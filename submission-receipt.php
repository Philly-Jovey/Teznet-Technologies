<?php
declare(strict_types=1);

require_once __DIR__ . '/submission-receipt-support.php';
require_once __DIR__ . '/submission-receipt-pdf.php';

$token = isset($_GET['token']) && is_string($_GET['token']) ? $_GET['token'] : '';
$receipt = get_submission_receipt($token);
if ($receipt === null) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: no-store, max-age=0');
    echo 'This receipt is unavailable. Return to your submission confirmation and download it again.';
    exit;
}

try {
    $pdf = build_submission_receipt_pdf($receipt, $token);
} catch (Throwable $exception) {
    error_log('Submission receipt PDF generation failed: ' . $exception->getMessage());
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: no-store, max-age=0');
    echo 'We could not prepare your PDF receipt. Please return to the confirmation page and try again.';
    exit;
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $pdf['ascii_filename'] . '"; filename*=UTF-8\'\'' . rawurlencode($pdf['filename']));
header('Content-Length: ' . strlen($pdf['content']));
header('Cache-Control: no-store, max-age=0');
header('X-Content-Type-Options: nosniff');
echo $pdf['content'];
