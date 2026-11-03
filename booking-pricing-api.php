<?php
declare(strict_types=1);

require_once __DIR__ . '/booking-pricing.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, max-age=0');

function booking_pricing_json_response(int $status, array $response): void
{
    http_response_code($status);
    echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

$previewRequested = isset($_GET['preview']) && $_GET['preview'] === '1';
if (!booking_pricing_is_available($previewRequested)) {
    booking_pricing_json_response(200, ['available' => false, 'preview' => false]);
}
$config = booking_pricing_config();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $catalog = [];
    foreach (booking_pricing_config()['services'] as $label => $pricing) {
        $catalog[] = ['label' => $label, 'category' => is_array($pricing) ? $pricing['category'] : 'Custom quote'];
    }

    booking_pricing_json_response(200, [
        'available' => true,
        'preview' => !$config['published'],
        'currency' => $config['currency'],
        'services' => $catalog,
        'scopes' => array_map(
            static fn (array $scope): string => $scope['label'],
            $config['scope_factors']
        ),
    ]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: GET, POST');
    booking_pricing_json_response(405, ['error' => 'Method not allowed.']);
}

$service = isset($_POST['service']) && is_string($_POST['service']) ? trim($_POST['service']) : '';
$scope = isset($_POST['scope']) && is_string($_POST['scope']) ? trim($_POST['scope']) : '';
$description = isset($_POST['description']) && is_string($_POST['description']) ? trim($_POST['description']) : '';
$quantityDetails = booking_pricing_detect_quantity($service, $description);
if ($quantityDetails['quantity'] > 100) {
    booking_pricing_json_response(422, ['error' => 'Estimates can include up to 100 separately priced items. Contact us for larger quantities.']);
}
$estimate = booking_pricing_estimate($service, $scope, $quantityDetails['quantity']);
if ($estimate === null) {
    booking_pricing_json_response(422, ['error' => 'Choose a listed service and project size to see an estimate. Custom services require a consultation.']);
}

$estimate['quantity_detected'] = $quantityDetails['detected'];
$estimate['quantity_unit'] = $quantityDetails['unit'];
$estimate['quantity_label'] = booking_pricing_quantity_label($estimate, $quantityDetails);
booking_pricing_json_response(200, ['estimate' => $estimate, 'preview' => !booking_pricing_config()['published']]);
