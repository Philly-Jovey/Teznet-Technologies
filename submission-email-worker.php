<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/submission-email.php';

$jobId = $argv[1] ?? '';
if (!is_string($jobId) || preg_match('/^[a-f0-9]{48}$/D', $jobId) !== 1) {
    fwrite(STDERR, "Invalid receipt email job ID.\n");
    exit(2);
}

$queueDirectory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR . 'teznet-mail-queue';
$jobPath = $queueDirectory . DIRECTORY_SEPARATOR . $jobId . '.json';
$jobContent = @file_get_contents($jobPath);
if ($jobContent === false) {
    fwrite(STDERR, "Receipt email job is unavailable.\n");
    exit(2);
}
if (!@unlink($jobPath)) {
    error_log('Unable to remove a processed receipt email queue job.');
}

try {
    $job = json_decode($jobContent, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($job) || !is_array($job['receipt'] ?? null) ||
        !is_string($job['token'] ?? null) || preg_match('/^[a-f0-9]{48}$/D', $job['token']) !== 1 ||
        !is_string($job['applicant_email'] ?? null) ||
        (isset($job['copy_company']) && !is_bool($job['copy_company']))) {
        throw new RuntimeException('Receipt email queue job data is invalid.');
    }

    send_submission_receipt_email($job['receipt'], $job['token'], $job['applicant_email'], $job['copy_company'] ?? true);
} catch (Throwable $exception) {
    error_log('Background receipt email delivery failed: ' . $exception->getMessage());
    exit(1);
}
