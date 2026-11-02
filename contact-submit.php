<?php
declare(strict_types=1);

require_once __DIR__ . '/database-config.php';
require_once __DIR__ . '/submission-receipt-support.php';

/**
 * Saves a contact form submission to MySQL.
 * Configure DB_HOST, DB_PORT, DB_NAME, DB_USER, and DB_PASS in the hosting environment.
 */

function respond(int $status, string $title, string $message, string $service = '', string $receiptToken = ''): void
{
    $result = $status >= 200 && $status < 300 ? 'success' : 'error';
    $location = 'submission-status.php?form=contact&result=' . $result;
    if ($result === 'success' && $service !== '') {
        $location .= '&field=' . rawurlencode($service);
    }
    if ($result === 'success' && $receiptToken !== '') {
        $location .= '&receipt=' . rawurlencode($receiptToken);
    }
    header('Location: ' . $location, true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, 'Method not allowed', 'Please submit the contact form to send us a message.');
}

function posted_text(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

$firstName = posted_text('first-name');
$lastName = posted_text('last-name');
$email = posted_text('email');
$phone = posted_text('phone');
$service = posted_text('service');
$message = posted_text('message');

$allowedServices = [
    'network-design-installation',
    'fiber-connectivity',
    'wireless-wifi',
    'network-security-operations',
    'monitoring-it-support',
    'website-app-development',
    'school-management-system',
    'mobile-ecommerce',
    'cloud-devops',
    'digital-marketing-seo',
    'photography-video',
    'live-production-streaming',
    'branding-design',
    'technology-training',
    'internships-career-development',
    'general-enquiry',
];

if (
    $firstName === '' || strlen($firstName) > 100 ||
    $lastName === '' || strlen($lastName) > 100 ||
    $email === '' || strlen($email) > 254 || filter_var($email, FILTER_VALIDATE_EMAIL) === false ||
    strlen($phone) > 40 ||
    !in_array($service, $allowedServices, true) ||
    $message === '' || strlen($message) > 5000
) {
    respond(422, 'Check your details', 'Please complete the required fields with valid details, then submit the form again.');
}

$host = getenv('DB_HOST') ?: '';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_NAME') ?: '';
$username = getenv('DB_USER') ?: '';
$password = getenv('DB_PASS');

if ($host === '' || $database === '' || $username === '' || $password === false) {
    respond(500, 'Unable to send your message', 'The contact form is not connected to the database yet. Please try again later or contact us by email.');
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
        'INSERT INTO contact_submissions (first_name, last_name, work_email, telephone_number, service, message)
         VALUES (:first_name, :last_name, :work_email, :telephone_number, :service, :message)'
    );
    $statement->execute([
        ':first_name' => $firstName,
        ':last_name' => $lastName,
        ':work_email' => $email,
        ':telephone_number' => $phone !== '' ? $phone : null,
        ':service' => $service,
        ':message' => $message,
    ]);
} catch (PDOException $exception) {
    error_log('Contact form database error: ' . $exception->getMessage());
    respond(500, 'Unable to send your message', 'We could not save your message right now. Please try again later or contact us by email.');
}

$receiptToken = save_submission_receipt('contact', [
    'First name' => $firstName,
    'Last name' => $lastName,
    'Email' => $email,
    'Phone' => $phone,
    'Service of interest' => [
        'network-design-installation' => 'Network design & installation',
        'fiber-connectivity' => 'Fiber optic & connectivity',
        'wireless-wifi' => 'Wireless networks & Wi-Fi',
        'network-security-operations' => 'Network security & managed operations',
        'monitoring-it-support' => 'Network monitoring & IT support',
        'website-app-development' => 'Website & application development',
        'school-management-system' => 'School management system',
        'mobile-ecommerce' => 'Mobile apps & e-commerce',
        'cloud-devops' => 'Cloud & DevOps',
        'digital-marketing-seo' => 'Digital marketing & SEO',
        'photography-video' => 'Photography & videography',
        'live-production-streaming' => 'Live production & streaming',
        'branding-design' => 'Branding & graphic design',
        'technology-training' => 'Technology training & mentorship',
        'internships-career-development' => 'Internships & career development',
        'general-enquiry' => 'General enquiry / not sure',
    ][$service],
    'Message' => $message,
]);
respond(200, 'Message received', 'Thank you for contacting Teznet Technologies. Our team will get back to you.', $service, $receiptToken);
