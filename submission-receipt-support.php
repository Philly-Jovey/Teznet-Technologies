<?php
declare(strict_types=1);

function save_submission_receipt(string $form, array $fields): string
{
    if (session_status() === PHP_SESSION_NONE && !session_start()) {
        error_log('Unable to start a session for a submission receipt.');
        return '';
    }

    try {
        $receipts = $_SESSION['teznet_submission_receipts'] ?? [];
        if (!is_array($receipts)) $receipts = [];

        foreach ($receipts as $token => $receipt) {
            $createdAt = is_array($receipt) ? strtotime((string)($receipt['created_at'] ?? '')) : false;
            if ($createdAt === false || $createdAt < time() - 7 * 24 * 60 * 60) unset($receipts[$token]);
        }

        $token = bin2hex(random_bytes(24));
        $receipts[$token] = [
            'form' => $form,
            'created_at' => date(DATE_ATOM),
            'fields' => $fields,
        ];
        $_SESSION['teznet_submission_receipts'] = $receipts;
        return $token;
    } catch (Throwable $exception) {
        error_log('Unable to create a submission receipt: ' . $exception->getMessage());
        return '';
    }
}

function get_submission_receipt(string $token): ?array
{
    if (preg_match('/^[a-f0-9]{48}$/D', $token) !== 1) return null;
    if (session_status() === PHP_SESSION_NONE && !session_start()) {
        error_log('Unable to start a session to retrieve a submission receipt.');
        return null;
    }

    $receipt = $_SESSION['teznet_submission_receipts'][$token] ?? null;
    if (!is_array($receipt) || !isset($receipt['form'], $receipt['created_at'], $receipt['fields']) ||
        !is_string($receipt['form']) || !is_string($receipt['created_at']) || !is_array($receipt['fields'])) {
        return null;
    }

    $createdAt = strtotime($receipt['created_at']);
    if ($createdAt === false || $createdAt < time() - 7 * 24 * 60 * 60) return null;
    return $receipt;
}
