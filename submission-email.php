<?php
declare(strict_types=1);

require_once __DIR__ . '/submission-receipt-pdf.php';

function smtp_read_response($connection): array
{
    $response = '';
    while (($line = fgets($connection, 515)) !== false) {
        $response .= $line;
        if (preg_match('/^\d{3} /', $line) === 1) {
            return [(int)substr($line, 0, 3), trim($response)];
        }
    }

    throw new RuntimeException('The email server returned an incomplete response.');
}

function smtp_command($connection, string $command, array $expectedCodes): string
{
    if (fwrite($connection, $command . "\r\n") === false) {
        throw new RuntimeException('Unable to communicate with the email server.');
    }

    [$code, $response] = smtp_read_response($connection);
    if (!in_array($code, $expectedCodes, true)) {
        throw new RuntimeException('Email server rejected a message command (' . $code . ').');
    }
    return $response;
}

function smtp_write_message($connection, string $message): void
{
    $offset = 0;
    $length = strlen($message);
    while ($offset < $length) {
        $written = fwrite($connection, substr($message, $offset));
        if ($written === false || $written === 0) {
            throw new RuntimeException('Unable to send the receipt email.');
        }
        $offset += $written;
    }
}

function queue_submission_receipt_email(array $receipt, string $token, string $applicantEmail, bool $copyCompany = true): void
{
    $queueDirectory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR . 'teznet-mail-queue';
    if (!is_dir($queueDirectory) && !@mkdir($queueDirectory, 0700, true) && !is_dir($queueDirectory)) {
        throw new RuntimeException('Unable to create the private receipt email queue.');
    }

    $jobId = bin2hex(random_bytes(24));
    $jobPath = $queueDirectory . DIRECTORY_SEPARATOR . $jobId . '.json';
    $jobContent = json_encode([
        'receipt' => $receipt,
        'token' => $token,
        'applicant_email' => $applicantEmail,
        'copy_company' => $copyCompany,
    ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $jobFile = @fopen($jobPath, 'x');
    if ($jobFile === false) {
        throw new RuntimeException('Unable to create a receipt email queue job.');
    }

    try {
        if (fwrite($jobFile, $jobContent) !== strlen($jobContent) || !fflush($jobFile)) {
            throw new RuntimeException('Unable to write the receipt email queue job.');
        }
    } catch (Throwable $exception) {
        fclose($jobFile);
        @unlink($jobPath);
        throw $exception;
    }
    fclose($jobFile);

    $workerPath = __DIR__ . DIRECTORY_SEPARATOR . 'submission-email-worker.php';
    $phpExtensionDirectory = (string)ini_get('extension_dir');
    $phpCliPath = dirname($phpExtensionDirectory) . DIRECTORY_SEPARATOR . 'php.exe';
    if (!is_file($phpCliPath)) {
        @unlink($jobPath);
        throw new RuntimeException('The PHP command-line executable is unavailable.');
    }
    $command = 'start "" /B ' . escapeshellarg($phpCliPath) . ' ' . escapeshellarg($workerPath) .
        ' ' . escapeshellarg($jobId) . ' >NUL 2>&1';
    $process = @popen($command, 'r');
    if ($process === false) {
        @unlink($jobPath);
        throw new RuntimeException('Unable to start the receipt email worker.');
    }
    pclose($process);
}

function send_submission_receipt_email(array $receipt, string $token, string $applicantEmail, bool $copyCompany = true): void
{
    $configPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR . 'teznet-mail.php';
    if (!is_file($configPath)) {
        throw new RuntimeException('Private email settings are missing.');
    }

    $config = require $configPath;
    if (!is_array($config)) {
        throw new RuntimeException('Private email settings must return an array.');
    }

    $host = $config['SMTP_HOST'] ?? '';
    $port = $config['SMTP_PORT'] ?? 0;
    $username = $config['SMTP_USERNAME'] ?? '';
    $password = $config['SMTP_PASSWORD'] ?? '';
    $fromAddress = $config['MAIL_FROM_ADDRESS'] ?? '';
    $companyAddress = $config['MAIL_COMPANY_ADDRESS'] ?? '';
    if (!is_string($host) || preg_match('/^[A-Za-z0-9.-]+$/D', $host) !== 1 ||
        !is_int($port) || $port !== 465 ||
        !is_string($username) || filter_var($username, FILTER_VALIDATE_EMAIL) === false ||
        !is_string($password) || trim($password) === '' ||
        !is_string($fromAddress) || filter_var($fromAddress, FILTER_VALIDATE_EMAIL) === false ||
        !is_string($companyAddress) || filter_var($companyAddress, FILTER_VALIDATE_EMAIL) === false ||
        filter_var($applicantEmail, FILTER_VALIDATE_EMAIL) === false) {
        throw new RuntimeException('Private email settings are incomplete or invalid.');
    }

    $pdf = build_submission_receipt_pdf($receipt, $token);
    $formNames = [
        'contact' => 'contact message',
        'booking' => 'consultation request',
        'application' => 'Teznet Hub application',
        'declined_estimate' => 'estimate follow-up request',
    ];
    $formName = $formNames[$receipt['form']] ?? null;
    if ($formName === null) {
        throw new RuntimeException('The receipt type is invalid.');
    }

    $fields = $receipt['fields'] ?? [];
    $fullName = $receipt['form'] === 'contact'
        ? trim((string)($fields['First name'] ?? '') . ' ' . (string)($fields['Last name'] ?? ''))
        : trim((string)($fields['Full name'] ?? ''));
    $fullName = preg_replace('/[\r\n]+/', ' ', $fullName) ?? '';
    $subject = 'Your Teznet Technologies ' . $formName . ' receipt';
    $boundary = '=_teznet_' . bin2hex(random_bytes(18));
    $asciiFilename = str_replace(['"', '\\', "\r", "\n"], '-', $pdf['ascii_filename']);
    $companyIsApplicant = !$copyCompany || strcasecmp($companyAddress, $applicantEmail) === 0;
    $body = "Hello {$fullName},\r\n\r\n";
    $body .= $receipt['form'] === 'declined_estimate'
        ? "Thank you. We received your request for follow-up about this estimate. A confirmation is attached.\r\n"
        : "Thank you. We received your {$formName}. A copy of your receipt is attached.\r\n";
    $body .= "Receipt reference: " . strtoupper(substr($token, 0, 12)) . "\r\n\r\n";
    $body .= "Teznet Technologies\r\n";

    $headers = [
        'Date: ' . date(DATE_RFC2822),
        'From: Teznet Technologies <' . $fromAddress . '>',
        'To: ' . $applicantEmail,
        'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=',
        'MIME-Version: 1.0',
        'Content-Type: multipart/mixed; boundary="' . $boundary . '"',
    ];
    if (!$companyIsApplicant) {
        $headers[] = 'Cc: ' . $companyAddress;
    }
    $message = implode("\r\n", array_merge($headers, [
        '',
        '--' . $boundary,
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
        '',
        $body,
        '--' . $boundary,
        'Content-Type: application/pdf; name="' . $asciiFilename . '"',
        'Content-Transfer-Encoding: base64',
        'Content-Disposition: attachment; filename="' . $asciiFilename . '"',
        '',
        rtrim(chunk_split(base64_encode($pdf['content']), 76, "\r\n")),
        '--' . $boundary . '--',
        '',
    ]));

    $recipients = array_values(array_unique(
        $copyCompany ? [$applicantEmail, $companyAddress] : [$applicantEmail],
        SORT_STRING
    ));
    $normalizedRecipients = [];
    foreach ($recipients as $recipient) {
        $normalizedRecipients[strtolower($recipient)] = $recipient;
    }

    $connection = @stream_socket_client(
        'ssl://' . $host . ':' . $port,
        $errorCode,
        $errorMessage,
        15,
        STREAM_CLIENT_CONNECT,
        stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true]])
    );
    if ($connection === false) {
        throw new RuntimeException('Unable to connect to the configured email server (' . $errorCode . ').');
    }

    try {
        stream_set_timeout($connection, 15);
        [$greetingCode] = smtp_read_response($connection);
        if ($greetingCode !== 220) {
            throw new RuntimeException('The email server did not accept the connection.');
        }
        smtp_command($connection, 'EHLO localhost', [250]);
        smtp_command($connection, 'AUTH LOGIN', [334]);
        smtp_command($connection, base64_encode($username), [334]);
        smtp_command($connection, base64_encode(str_replace(' ', '', $password)), [235]);
        smtp_command($connection, 'MAIL FROM:<' . $fromAddress . '>', [250]);
        foreach ($normalizedRecipients as $recipient) {
            smtp_command($connection, 'RCPT TO:<' . $recipient . '>', [250, 251]);
        }
        smtp_command($connection, 'DATA', [354]);

        $message = preg_replace('/(?m)^\./', '..', $message);
        if ($message === null) {
            throw new RuntimeException('Unable to prepare the receipt email.');
        }
        smtp_write_message($connection, $message . ".\r\n");
        [$deliveryCode, $deliveryResponse] = smtp_read_response($connection);
        if ($deliveryCode !== 250) {
            throw new RuntimeException('The email server did not accept the receipt (' . $deliveryCode . ').');
        }
        fwrite($connection, "QUIT\r\n");
    } finally {
        fclose($connection);
    }
}
