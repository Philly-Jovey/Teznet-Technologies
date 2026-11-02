<?php
declare(strict_types=1);

require_once __DIR__ . '/submission-receipt-support.php';

$token = isset($_GET['token']) && is_string($_GET['token']) ? $_GET['token'] : '';
$receipt = get_submission_receipt($token);
$formNames = [
    'contact' => 'Contact message',
    'booking' => 'Consultation request',
    'application' => 'Teznet Hub application',
];

if ($receipt === null || !isset($formNames[$receipt['form']])) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: no-store, max-age=0');
    echo 'This receipt is unavailable. Return to your submission confirmation and download it again.';
    exit;
}

function receipt_pdf_text(string $text): string
{
    $converted = iconv('UTF-8', 'Windows-1252//TRANSLIT', $text);
    if ($converted === false) {
        throw new RuntimeException('Unable to encode receipt text for PDF output.');
    }

    $converted = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', ' ', $converted);
    if ($converted === null) {
        throw new RuntimeException('Unable to prepare receipt text for PDF output.');
    }

    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $converted);
}

function receipt_pdf_add_text(array &$commands, string $text, string $font, int $size, int $x, int $y, string $color): void
{
    $commands[] = sprintf(
        'BT /%s %d Tf %s rg %d %d Td (%s) Tj ET',
        $font,
        $size,
        $color,
        $x,
        $y,
        receipt_pdf_text($text)
    );
}

try {
    $lines = [];
    foreach ($receipt['fields'] as $label => $value) {
        if (!is_string($label) || !is_string($value) || trim($value) === '') continue;

        $lines[] = ['text' => $label . ':', 'font' => 'F2'];
        $normalizedValue = str_replace(["\r\n", "\r"], "\n", trim($value));
        foreach (explode("\n", $normalizedValue) as $paragraph) {
            $wrapped = wordwrap($paragraph, 88, "\n", true);
            foreach (explode("\n", $wrapped) as $wrappedLine) {
                $lines[] = ['text' => $wrappedLine, 'font' => 'F1'];
            }
        }
        $lines[] = ['text' => '', 'font' => 'F1'];
    }

    $pages = array_chunk($lines, 40);
    if ($pages === []) $pages = [[]];

    $objects = [
        1 => '<< /Type /Catalog /Pages 2 0 R >>',
        2 => '',
        3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
        4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
    ];
    $pageReferences = [];
    $pageCount = count($pages);

    foreach ($pages as $pageIndex => $pageLines) {
        $pageObjectId = 5 + $pageIndex * 2;
        $contentObjectId = $pageObjectId + 1;
        $pageReferences[] = $pageObjectId . ' 0 R';

        $commands = [];
        receipt_pdf_add_text($commands, 'TEZNET TECHNOLOGIES', 'F2', 18, 54, 748, '0.04 0.12 0.22');
        receipt_pdf_add_text($commands, 'SUBMISSION RECEIPT', 'F2', 11, 54, 727, '0.08 0.45 0.25');
        receipt_pdf_add_text($commands, 'Submission: ' . $formNames[$receipt['form']], 'F1', 10, 54, 703, '0.12 0.16 0.22');
        receipt_pdf_add_text($commands, 'Reference: ' . strtoupper(substr($token, 0, 12)), 'F1', 10, 54, 688, '0.12 0.16 0.22');
        receipt_pdf_add_text($commands, 'Submitted: ' . date('F j, Y \a\t g:i A T', strtotime($receipt['created_at'])), 'F1', 10, 54, 673, '0.12 0.16 0.22');
        $commands[] = 'q 0.08 0.45 0.25 RG 2 w 54 658 m 558 658 l S Q';
        receipt_pdf_add_text($commands, 'SUBMITTED INFORMATION', 'F2', 11, 54, 638, '0.04 0.12 0.22');

        $y = 616;
        foreach ($pageLines as $line) {
            if ($line['text'] !== '') {
                receipt_pdf_add_text(
                    $commands,
                    $line['text'],
                    $line['font'],
                    $line['font'] === 'F2' ? 10 : 9,
                    54,
                    $y,
                    $line['font'] === 'F2' ? '0.04 0.12 0.22' : '0.22 0.25 0.30'
                );
            }
            $y -= 14;
        }

        $commands[] = 'q 0.82 0.85 0.89 RG 0.6 w 54 48 m 558 48 l S Q';
        receipt_pdf_add_text($commands, 'This receipt confirms your information was submitted to Teznet Technologies.', 'F1', 8, 54, 33, '0.35 0.39 0.44');
        receipt_pdf_add_text($commands, 'Page ' . ($pageIndex + 1) . ' of ' . $pageCount, 'F1', 8, 505, 33, '0.35 0.39 0.44');

        $stream = implode("\n", $commands) . "\n";
        $objects[$pageObjectId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents ' . $contentObjectId . ' 0 R >>';
        $objects[$contentObjectId] = '<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . 'endstream';
    }

    $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $pageReferences) . '] /Count ' . $pageCount . ' >>';
    ksort($objects);

    $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
    $offsets = [0];
    foreach ($objects as $objectId => $objectBody) {
        $offsets[$objectId] = strlen($pdf);
        $pdf .= $objectId . " 0 obj\n" . $objectBody . "\nendobj\n";
    }

    $crossReferenceOffset = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
    for ($objectId = 1; $objectId <= count($objects); $objectId++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$objectId]);
    }
    $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n" . $crossReferenceOffset . "\n%%EOF\n";
} catch (Throwable $exception) {
    error_log('Submission receipt PDF generation failed: ' . $exception->getMessage());
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: no-store, max-age=0');
    echo 'We could not prepare your PDF receipt. Please return to the confirmation page and try again.';
    exit;
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="teznet-' . $receipt['form'] . '-receipt.pdf"');
header('Content-Length: ' . strlen($pdf));
header('Cache-Control: no-store, max-age=0');
header('X-Content-Type-Options: nosniff');
echo $pdf;
