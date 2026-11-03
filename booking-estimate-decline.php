<?php
declare(strict_types=1);

require_once __DIR__ . '/database-config.php';
require_once __DIR__ . '/booking-pricing.php';
require_once __DIR__ . '/submission-email.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, max-age=0');

function declined_estimate_response(int $status, array $response): void
{
    http_response_code($status);
    echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    declined_estimate_response(405, ['error' => 'Method not allowed.']);
}

$previewRequested = isset($_POST['pricing_preview']) && $_POST['pricing_preview'] === '1';
if (!booking_pricing_is_available($previewRequested)) {
    declined_estimate_response(404, ['error' => 'The pricing estimator is not available.']);
}

$readText = static function (string $key): string {
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
};

$name = $readText('name');
$email = $readText('email');
$phone = $readText('phone');
$company = $readText('company');
$service = $readText('service');
$scope = $readText('scope');
$description = $readText('description');
$consent = $readText('follow_up_consent');
$quantityDetails = booking_pricing_detect_quantity($service, $description);
if ($quantityDetails['quantity'] > 100) {
    declined_estimate_response(422, ['error' => 'Estimates can include up to 100 separately priced items. Contact us for larger quantities.']);
}
$estimate = booking_pricing_estimate($service, $scope, $quantityDetails['quantity']);
if ($name === '' || strlen($name) > 150 || $email === '' || strlen($email) > 254 ||
    filter_var($email, FILTER_VALIDATE_EMAIL) === false || $phone === '' || strlen($phone) > 40 ||
    strlen($company) > 180 || $description === '' || strlen($description) > 5000 ||
    $consent !== 'yes' || $estimate === null) {
    declined_estimate_response(422, ['error' => 'Add your name, a valid email address, phone number, project description, and consent to save this request.']);
}
$estimate['quantity_label'] = booking_pricing_quantity_label($estimate, $quantityDetails);

$host = getenv('DB_HOST') ?: '';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_NAME') ?: '';
$username = getenv('DB_USER') ?: '';
$password = getenv('DB_PASS');
if ($host === '' || $database === '' || $username === '' || $password === false) {
    error_log('Declined estimate follow-up is unavailable: database settings are missing.');
    declined_estimate_response(503, ['error' => 'We could not save your request right now.']);
}

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false]
    );
    $statement = $pdo->prepare(
        'INSERT INTO declined_booking_estimates (full_name, email, phone, company, service, project_scope, description, estimate_minimum, estimate_maximum, currency, quantity, follow_up_consent)
         VALUES (:full_name, :email, :phone, :company, :service, :project_scope, :description, :estimate_minimum, :estimate_maximum, :currency, :quantity, 1)'
    );
    $statement->execute([
        ':full_name' => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':company' => $company !== '' ? $company : null,
        ':service' => $service,
        ':project_scope' => $scope,
        ':description' => $description,
        ':estimate_minimum' => $estimate['minimum'],
        ':estimate_maximum' => $estimate['maximum'],
        ':currency' => $estimate['currency'],
        ':quantity' => $estimate['quantity'],
    ]);
} catch (PDOException $exception) {
    error_log('Declined estimate follow-up database error: ' . $exception->getMessage());
    declined_estimate_response(500, ['error' => 'We could not save your request right now.']);
}

$receiptToken = bin2hex(random_bytes(24));
$receipt = [
    'form' => 'declined_estimate',
    'created_at' => date(DATE_ATOM),
    'fields' => [
        'Full name' => $name,
        'Email' => $email,
        'Phone' => $phone,
        'Company' => $company,
        'Service requested' => $service,
        'Project description' => $description,
        'Project scope' => $estimate['scope_label'],
        'Estimated quantity' => $estimate['quantity_label'],
        'Estimated price range' => $estimate['currency'] . ' ' .
            booking_pricing_format_amount($estimate['minimum']) . ' - ' .
            $estimate['currency'] . ' ' . booking_pricing_format_amount($estimate['maximum']) .
            ' (' . $estimate['unit'] . ')',
        'Follow-up consent' => 'Yes',
    ],
];

try {
    queue_submission_receipt_email($receipt, $receiptToken, $email, false);
    declined_estimate_response(200, [
        'saved' => true,
        'email_status' => 'queued',
        'message' => 'Your follow-up request was saved with your consent. A confirmation email is being sent to you.',
    ]);
} catch (Throwable $exception) {
    error_log('Declined estimate confirmation email queueing failed: ' . $exception->getMessage());
    declined_estimate_response(200, [
        'saved' => true,
        'email_status' => 'failed',
        'message' => 'Your follow-up request was saved with your consent, but we could not queue the confirmation email. Please contact Teznet if you do not hear from us.',
    ]);
}
