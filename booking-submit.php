<?php
declare(strict_types=1);

function respond(int $status): void
{
    $result = $status >= 200 && $status < 300 ? 'success' : 'error';
    header('Location: submission-status.php?form=booking&result=' . $result, true, 303);
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
$dateParts = [];
$validDate = preg_match('/^(\d{4})-(\d{2})-(\d{2})$/D', $requestDate, $dateParts) === 1 &&
    checkdate((int)$dateParts[2], (int)$dateParts[3], (int)$dateParts[1]);
$validTime = preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/D', $requestTime) === 1;
$validTimezone = $requestTimezone !== '' && strlen($requestTimezone) <= 64 &&
    preg_match('/^[A-Za-z0-9_+\/-]+$/D', $requestTimezone) === 1;
if ($validTimezone) {
    try {
        new DateTimeZone($requestTimezone);
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
    strlen($description) > 5000 || !$validDate || !$validTime || !$validTimezone
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
        'INSERT INTO booking_requests (full_name, phone, email, company, service, description, request_date, request_time, request_timezone)
         VALUES (:full_name, :phone, :email, :company, :service, :description, :request_date, :request_time, :request_timezone)'
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
    ]);
} catch (PDOException $exception) {
    error_log('Booking request database error: ' . $exception->getMessage());
    respond(500);
}

respond(200);
