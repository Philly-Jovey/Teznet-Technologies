<?php
declare(strict_types=1);

const MAX_DOCUMENT_BYTES = 3 * 1024 * 1024;

function respond(int $status, string $title, string $message): void
{
    http_response_code($status);
    header('Content-Type: text/html; charset=UTF-8');
    ?>
    <!doctype html>
    <html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> | Teznet Technologies</title>
    <style>body{margin:0;padding:2rem;background:#f8f9ff;color:#0b1f3a;font:16px/1.6 Arial,sans-serif}main{max-width:38rem;margin:10vh auto;padding:2rem;background:#fff;border:1px solid #dce3ed;border-radius:1rem}a{color:#a4001d}</style></head>
    <body><main><h1><?= htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h1><p><?= htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p><a href="opportunities.html#application">Return to the application</a></main></body></html>
    <?php
    exit;
}

function posted_text(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

function uploaded_file(string $key): ?array
{
    if (!isset($_FILES[$key]) || !is_array($_FILES[$key])) return null;
    $file = $_FILES[$key];
    if (!isset($file['error']) || is_array($file['error'])) throw new RuntimeException('Invalid upload.');
    if ($file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('A document could not be uploaded. Check that each file is 3 MB or smaller.');
    if (!isset($file['tmp_name'], $file['name'], $file['size']) || !is_uploaded_file($file['tmp_name'])) throw new RuntimeException('Invalid upload.');
    if ((int)$file['size'] < 1 || (int)$file['size'] > MAX_DOCUMENT_BYTES) throw new RuntimeException('Each document must be 3 MB or smaller.');
    return $file;
}

function validate_document(array $file): string
{
    $originalName = (string)$file['name'];
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowed = [
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword', 'application/x-ole-storage', 'application/CDFV2'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/x-zip'],
        'jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'], 'png' => ['image/png'],
    ];
    if (!isset($allowed[$extension])) throw new RuntimeException('Only PDF, DOC, DOCX, JPG and PNG documents are accepted.');
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!is_string($mime) || !in_array($mime, $allowed[$extension], true)) {
        // Some finfo builds report legacy Word files with the generic OLE MIME type.
        if (!($extension === 'doc' && in_array($mime, ['application/x-ole-storage', 'application/CDFV2', 'application/vnd.ms-office'], true))) {
            throw new RuntimeException('The document content does not match its file type.');
        }
    }
    $bytes = file_get_contents($file['tmp_name'], false, null, 0, 16);
    if (!is_string($bytes)) throw new RuntimeException('The uploaded document could not be read.');
    if ($extension === 'pdf' && !str_starts_with($bytes, '%PDF-')) throw new RuntimeException('The uploaded PDF is invalid.');
    if (in_array($extension, ['jpg', 'jpeg'], true) && !str_starts_with($bytes, "\xFF\xD8\xFF")) throw new RuntimeException('The uploaded JPEG is invalid.');
    if ($extension === 'png' && !str_starts_with($bytes, "\x89PNG\r\n\x1A\n")) throw new RuntimeException('The uploaded PNG is invalid.');
    if ($extension === 'doc' && !str_starts_with($bytes, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1")) throw new RuntimeException('The uploaded Word document is invalid.');
    if ($extension === 'docx') {
        if (!class_exists(ZipArchive::class)) throw new RuntimeException('DOCX validation is unavailable on this server.');
        $zip = new ZipArchive();
        if ($zip->open($file['tmp_name']) !== true) throw new RuntimeException('The uploaded DOCX is invalid.');
        if ($zip->locateName('[Content_Types].xml') === false || $zip->locateName('word/document.xml') === false) {
            $zip->close();
            throw new RuntimeException('The uploaded DOCX is invalid.');
        }
        $zip->close();
    }
    return $extension === 'jpeg' ? 'jpg' : $extension;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, 'Method not allowed', 'Please use the application form to submit your details.');
}

$opportunity = posted_text('opportunity_type');
$fullName = posted_text('full_name');
$email = posted_text('email');
$phone = posted_text('phone');
$education = posted_text('education_level');
$institution = posted_text('institution');
$department = posted_text('department');
$duration = posted_text('duration');
$availability = posted_text('availability');
$motivation = posted_text('motivation');
$educationOptions = ['Secondary', 'Certificate', 'Diploma', "Bachelor's degree", 'Postgraduate', 'Other'];
$departmentOptions = [
    'network-design-installation' => 'Teznet Networks — Network design & installation',
    'fiber-connectivity' => 'Teznet Networks — Fiber optic & connectivity',
    'wireless-wifi' => 'Teznet Networks — Wireless networks & Wi-Fi',
    'network-security-operations' => 'Teznet Networks — Network security & managed operations',
    'monitoring-it-support' => 'Teznet Networks — Network monitoring & IT support',
    'website-app-development' => 'Teznet Digital — Website & application development',
    'school-management-system' => 'Teznet Digital — School management system',
    'mobile-ecommerce' => 'Teznet Digital — Mobile apps & e-commerce',
    'cloud-devops' => 'Teznet Digital — Cloud & DevOps',
    'digital-marketing-seo' => 'Teznet Digital — Digital marketing & SEO',
    'photography-video' => 'Teznet Media — Photography & videography',
    'live-production-streaming' => 'Teznet Media — Live production & streaming',
    'branding-design' => 'Teznet Media — Branding & graphic design',
];
$durationOptions = ['', '1 month', '3 months', '6 months', 'More than 6 months'];
$availabilityOptions = ['', 'Full-time', 'Part-time', 'Weekends', 'Flexible'];
if (!in_array($opportunity, ['internship', 'volunteering'], true) || $fullName === '' || strlen($fullName) > 150 ||
    strlen($email) > 254 || filter_var($email, FILTER_VALIDATE_EMAIL) === false || $phone === '' || strlen($phone) > 40 ||
    !in_array($education, $educationOptions, true) || strlen($institution) > 180 || !array_key_exists($department, $departmentOptions) ||
    !in_array($duration, $durationOptions, true) || !in_array($availability, $availabilityOptions, true) ||
    $motivation === '' || strlen($motivation) > 5000 || posted_text('consent') !== 'yes') {
    respond(422, 'Check your details', 'Please complete the required fields with valid details, then submit the form again.');
}

$storageSetting = getenv('TEZNET_APPLICATION_UPLOAD_DIR') ?: '';
$host = getenv('DB_HOST') ?: '';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_NAME') ?: '';
$username = getenv('DB_USER') ?: '';
$password = getenv('DB_PASS');
if ($storageSetting === '' || $host === '' || $database === '' || $username === '' || $password === false) {
    error_log('Volunteer application is not configured: private storage or database settings are missing.');
    respond(503, 'Applications are temporarily unavailable', 'The application service is being configured. Please try again later or contact Teznet Technologies by email.');
}

$storagePath = rtrim($storageSetting, DIRECTORY_SEPARATOR);
$documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
if (!is_dir($storagePath) && !@mkdir($storagePath, 0700, true) && !is_dir($storagePath)) {
    error_log('Unable to create private volunteer document storage directory.');
    respond(503, 'Applications are temporarily unavailable', 'The application service is being configured. Please try again later.');
}
$storagePath = realpath($storagePath) ?: '';
$directoryReady = $storagePath !== '' && @chmod($storagePath, 0700) && is_writable($storagePath);
$normalizedStorage = strtolower(str_replace('\\', '/', $storagePath));
$normalizedRoot = is_string($documentRoot) ? strtolower(rtrim(str_replace('\\', '/', $documentRoot), '/')) : '';
if (!$directoryReady || $normalizedRoot === '' || $normalizedStorage === $normalizedRoot || str_starts_with($normalizedStorage, $normalizedRoot . '/')) {
    error_log('Volunteer document storage must be configured outside the public document root.');
    respond(503, 'Applications are temporarily unavailable', 'The application service is being configured. Please try again later.');
}

$fileFields = ['cv' => 'CV / Résumé', 'academic_documents' => 'Academic documents', 'introduction_letter' => 'Institution introduction letter', 'portfolio' => 'Portfolio / work samples'];
$storedPaths = [];
$documents = [];
try {
    foreach ($fileFields as $field => $label) {
        $file = uploaded_file($field);
        if ($file === null) {
            if (in_array($field, ['cv', 'academic_documents', 'introduction_letter'], true)) {
                throw new RuntimeException('Please upload your CV, academic documents, and institution introduction letter.');
            }
            continue;
        }
        $extension = validate_document($file);
        $safeName = bin2hex(random_bytes(24)) . '.' . $extension;
        $destination = $storagePath . DIRECTORY_SEPARATOR . $safeName;
        if (!move_uploaded_file($file['tmp_name'], $destination)) throw new RuntimeException('A document could not be saved.');
        @chmod($destination, 0600);
        $storedPaths[] = $destination;
        $documents[$field] = ['label' => $label, 'stored_name' => $safeName, 'original_name' => basename((string)$file['name']), 'size' => (int)$file['size']];
    }

    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $statement = $pdo->prepare('INSERT INTO volunteer_applications (opportunity_type, full_name, email, phone, education_level, institution, department, preferred_duration, availability, motivation, documents_json, consent_given) VALUES (:opportunity, :full_name, :email, :phone, :education, :institution, :department, :duration, :availability, :motivation, :documents, 1)');
    $statement->execute([
        ':opportunity' => $opportunity, ':full_name' => $fullName, ':email' => $email, ':phone' => $phone,
        ':education' => $education, ':institution' => $institution !== '' ? $institution : null,
        ':department' => $departmentOptions[$department], ':duration' => $duration !== '' ? $duration : null,
        ':availability' => $availability !== '' ? $availability : null, ':motivation' => $motivation,
        ':documents' => json_encode($documents, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
    ]);
} catch (RuntimeException $exception) {
    foreach ($storedPaths as $path) @unlink($path);
    respond(422, 'Unable to submit application', $exception->getMessage());
} catch (Throwable $exception) {
    foreach ($storedPaths as $path) @unlink($path);
    error_log('Volunteer application processing error: ' . $exception->getMessage());
    respond(500, 'Unable to submit application', 'We could not save your application right now. Please try again later or contact Teznet Technologies by email.');
}

respond(200, 'Application received', 'Thank you for applying to TEZNET TECHNOLOGIES. Our team will review your application and contact you if there is a suitable next step.');
