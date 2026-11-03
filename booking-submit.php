<?php
declare(strict_types=1);

require_once __DIR__ . '/database-config.php';
require_once __DIR__ . '/booking-pricing.php';
require_once __DIR__ . '/submission-receipt-support.php';
require_once __DIR__ . '/submission-email.php';

function respond(int $status, string $service = '', string $receiptToken = '', ?string $receiptEmailStatus = null): void
{
    $result = $status >= 200 && $status < 300 ? 'success' : 'error';
    $location = 'submission-status.php?form=booking&result=' . $result;
    if ($result === 'success' && $service !== '') {
        $location .= '&field=' . rawurlencode($service);
    }
    if ($result === 'success' && $receiptToken !== '') {
        $location .= '&receipt=' . rawurlencode($receiptToken);
        if ($receiptEmailStatus !== null) {
            $location .= '&receipt_email=' . rawurlencode($receiptEmailStatus);
        }
    }
    header('Location: ' . $location, true, 303);
    exit;
}

function posted_text(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405);
}

$name = posted_text('name');
$phone = posted_text('phone');
$email = posted_text('email');
$company = posted_text('company');
$service = posted_text('available-service');
$description = posted_text('description');
$requestDate = posted_text('request_date');
$requestTime = posted_text('request_time');
$requestTimezone = posted_text('request_timezone');
$estimateScope = posted_text('estimate_scope');
$estimateService = posted_text('estimate_service');
$estimateQuantityValue = posted_text('estimate_quantity');
$pricingPreviewValue = posted_text('pricing_preview');
$pricingPreview = $pricingPreviewValue === '1';
$estimate = null;
if ($estimateScope !== '') {
    if ($estimateService !== $service || !booking_pricing_is_available($pricingPreview)) {
        respond(422);
    }
    $quantityDetails = booking_pricing_detect_quantity($service, $description);
    if ($quantityDetails['quantity'] > 100 ||
        $estimateQuantityValue !== (string)$quantityDetails['quantity']) {
        respond(422);
    }
    $estimate = booking_pricing_estimate($service, $estimateScope, $quantityDetails['quantity']);
    if ($estimate === null) {
        respond(422);
    }
    $estimate['quantity_label'] = booking_pricing_quantity_label($estimate, $quantityDetails);
} elseif ($estimateService !== '' || $estimateQuantityValue !== '' || $pricingPreviewValue !== '') {
    respond(422);
}
if ($pricingPreviewValue !== '' && $pricingPreviewValue !== '1') {
    respond(422);
}
$dateParts = [];
$validDate = preg_match('/^(\d{4})-(\d{2})-(\d{2})$/D', $requestDate, $dateParts) === 1 &&
    checkdate((int)$dateParts[2], (int)$dateParts[3], (int)$dateParts[1]);
$validTime = preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/D', $requestTime) === 1;
$validBusinessHours = $validTime &&
    preg_match('/^(?:(?:09|1[0-5]):(?:00|30)|16:00):00$/D', $requestTime) === 1;
$validTimezone = $requestTimezone !== '' && strlen($requestTimezone) <= 64 &&
    preg_match('/^[A-Za-z0-9_+\/-]+$/D', $requestTimezone) === 1;
$validAppointment = false;
if ($validTimezone) {
    try {
        $timezone = new DateTimeZone($requestTimezone);
        if ($validDate && $validTime) {
            $requestedAtValue = $requestDate . ' ' . $requestTime;
            $requestedAt = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $requestedAtValue, $timezone);
            $requestedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $requestDate, $timezone);
            $validAppointment = $requestedAt instanceof DateTimeImmutable &&
                $requestedAt->format('Y-m-d H:i:s') === $requestedAtValue &&
                $requestedDate instanceof DateTimeImmutable &&
                $requestedDate > new DateTimeImmutable('today', $timezone) &&
                $requestedAt >= new DateTimeImmutable('now', $timezone);
        }
    } catch (Throwable $exception) {
        $validTimezone = false;
    }
}
$allowedServices = [
    'Network design & installation',
    'Fiber optic & connectivity',
    'Wireless networks & Wi-Fi',
    'Network monitoring & IT support',
    'Network Security',
    'Web Development',
    'Software Development',
    'Android Development',
    'iOS Development',
    'Mobile App Maintenance',
    'App Store Optimization',
    'SEO Optimization',
    'Media Production',
    'Event Livestream',
    'Graphic Design & Creative Content',
    'Event photography',
    'Event videography',
    'Drone Aerial Footage',
    'Event Recording',
    'Posters & flyers',
    'Branding & Logo Design',
    'Others',
];

if (
    $name === '' || strlen($name) > 150 ||
    $phone === '' || strlen($phone) > 40 ||
    $email === '' || strlen($email) > 254 || filter_var($email, FILTER_VALIDATE_EMAIL) === false ||
    strlen($company) > 180 ||
    !in_array($service, $allowedServices, true) ||
    strlen($description) > 5000 || !$validDate || !$validBusinessHours || !$validTimezone || !$validAppointment
) {
    respond(422);
}

$host = getenv('DB_HOST') ?: '';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_NAME') ?: '';
$username = getenv('DB_USER') ?: '';
$password = getenv('DB_PASS');
if ($host === '' || $database === '' || $username === '' || $password === false) {
    error_log('Booking request form is not configured: database settings are missing.');
    respond(500);
}

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    $statement = $pdo->prepare(
        'INSERT INTO booking_requests (full_name, phone, email, company, service, description, request_date, request_time, request_timezone, estimate_minimum, estimate_maximum, estimate_currency, estimate_scope, estimate_quantity)
         VALUES (:full_name, :phone, :email, :company, :service, :description, :request_date, :request_time, :request_timezone, :estimate_minimum, :estimate_maximum, :estimate_currency, :estimate_scope, :estimate_quantity)'
    );
    $statement->execute([
        ':full_name' => $name,
        ':phone' => $phone,
        ':email' => $email,
        ':company' => $company !== '' ? $company : null,
        ':service' => $service,
        ':description' => $description !== '' ? $description : null,
        ':request_date' => $requestDate,
        ':request_time' => $requestTime,
        ':request_timezone' => $requestTimezone,
        ':estimate_minimum' => $estimate['minimum'] ?? null,
        ':estimate_maximum' => $estimate['maximum'] ?? null,
        ':estimate_currency' => $estimate['currency'] ?? null,
        ':estimate_scope' => $estimate['scope'] ?? null,
        ':estimate_quantity' => $estimate['quantity'] ?? null,
    ]);
} catch (PDOException $exception) {
    error_log('Booking request database error: ' . $exception->getMessage());
    respond(500);
}

$receiptFields = [
    'Full name' => $name,
    'Phone' => $phone,
    'Email' => $email,
    'Company' => $company,
    'Service requested' => $service,
    'Project description' => $description,
    'Requested date' => $requestDate,
    'Requested time' => $requestTime,
    'Time zone' => $requestTimezone,
];
if ($estimate !== null) {
    $receiptFields['Estimated price range'] = $estimate['currency'] . ' ' .
        booking_pricing_format_amount($estimate['minimum']) . ' - ' .
        $estimate['currency'] . ' ' . booking_pricing_format_amount($estimate['maximum']) .
        ' (' . $estimate['unit'] . ', ' . $estimate['scope_label'] . ')';
    $receiptFields['Estimated quantity'] = $estimate['quantity_label'];
    $receiptFields['Estimate notice'] = ($pricingPreview ? 'DRAFT estimate for owner review; not an approved price or quotation. ' : '') .
        'Indicative estimate only. Final pricing will be confirmed after consultation and review of the project requirements.';
}
$receiptToken = save_submission_receipt('booking', $receiptFields);
$receiptEmailStatus = 'failed';
try {
    $receipt = get_submission_receipt($receiptToken);
    if ($receipt === null) {
        throw new RuntimeException('Unable to load the booking receipt for email delivery.');
    }
    queue_submission_receipt_email($receipt, $receiptToken, $email);
    $receiptEmailStatus = 'queued';
} catch (Throwable $exception) {
    error_log('Booking receipt email queueing failed: ' . $exception->getMessage());
}
respond(200, $service, $receiptToken, $receiptEmailStatus);
