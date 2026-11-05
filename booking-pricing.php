<?php
declare(strict_types=1);

function booking_pricing_config(): array
{
    return [
        'published' => false,
        'currency' => 'UGX',
        'scope_factors' => [
            'small' => ['label' => 'Small / basic', 'minimum' => 0.7, 'maximum' => 0.85],
            'standard' => ['label' => 'Standard', 'minimum' => 1.0, 'maximum' => 1.0],
            'complex' => ['label' => 'Large / complex', 'minimum' => 1.5, 'maximum' => 2.0],
        ],
        'services' => [
            'Network design & installation' => ['minimum' => 500000, 'maximum' => 2500000, 'category' => 'Teznet Networks', 'unit' => 'per project'],
            'Fiber optic & connectivity' => ['minimum' => 800000, 'maximum' => 5000000, 'category' => 'Teznet Networks', 'unit' => 'per project'],
            'Wireless networks & Wi-Fi' => ['minimum' => 300000, 'maximum' => 1800000, 'category' => 'Teznet Networks', 'unit' => 'per project'],
            'Network monitoring & IT support' => ['minimum' => 300000, 'maximum' => 1500000, 'category' => 'Teznet Networks', 'unit' => 'per month'],
            'Network Security' => ['minimum' => 500000, 'maximum' => 3000000, 'category' => 'Teznet Networks', 'unit' => 'per project'],
            'Web Development' => ['minimum' => 500000, 'maximum' => 4500000, 'category' => 'Teznet Digital', 'unit' => 'per website'],
            'Software Development' => ['minimum' => 3000000, 'maximum' => 15000000, 'category' => 'Teznet Digital', 'unit' => 'per project'],
            'Android Development' => ['minimum' => 5000000, 'maximum' => 15000000, 'category' => 'Teznet Digital', 'unit' => 'per app'],
            'iOS Development' => ['minimum' => 7000000, 'maximum' => 20000000, 'category' => 'Teznet Digital', 'unit' => 'per app'],
            'Mobile App Maintenance' => ['minimum' => 250000, 'maximum' => 1000000, 'category' => 'Teznet Digital', 'unit' => 'per month'],
            'App Store Optimization' => ['minimum' => 200000, 'maximum' => 800000, 'category' => 'Teznet Digital', 'unit' => 'per app'],
            'SEO Optimization' => ['minimum' => 300000, 'maximum' => 1200000, 'category' => 'Teznet Digital', 'unit' => 'per month'],
            'Media Production' => ['minimum' => 500000, 'maximum' => 3000000, 'category' => 'Teznet Media', 'unit' => 'per project'],
            'Event Livestream' => ['minimum' => 700000, 'maximum' => 2500000, 'category' => 'Teznet Media', 'unit' => 'per event'],
            'Graphic Design & Creative Content' => ['minimum' => 100000, 'maximum' => 500000, 'category' => 'Teznet Media', 'unit' => 'per project'],
            'Event photography' => ['minimum' => 300000, 'maximum' => 1200000, 'category' => 'Teznet Media', 'unit' => 'per event'],
            'Event videography' => ['minimum' => 500000, 'maximum' => 2000000, 'category' => 'Teznet Media', 'unit' => 'per event'],
            'Drone Aerial Footage' => ['minimum' => 300000, 'maximum' => 1000000, 'category' => 'Teznet Media', 'unit' => 'per shoot'],
            'Event Recording' => ['minimum' => 300000, 'maximum' => 1500000, 'category' => 'Teznet Media', 'unit' => 'per event'],
            'Posters & flyers' => ['minimum' => 50000, 'maximum' => 250000, 'category' => 'Teznet Media', 'unit' => 'per design'],
            'Branding & Logo Design' => ['minimum' => 250000, 'maximum' => 1500000, 'category' => 'Teznet Media', 'unit' => 'per project'],
            'Others' => null,
        ],
    ];
}

function booking_pricing_is_local_request(): bool
{
    $remoteAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    return is_string($remoteAddress) && in_array($remoteAddress, ['127.0.0.1', '::1'], true);
}

function booking_pricing_is_available(bool $previewRequested = false): bool
{
    $config = booking_pricing_config();
    return $config['published'] || ($previewRequested && booking_pricing_is_local_request());
}

function booking_pricing_detect_quantity(string $service, string $description): array
{
    $countableUnits = [
        'Network design & installation' => ['project', 'site'],
        'Fiber optic & connectivity' => ['project', 'site', 'connection'],
        'Wireless networks & Wi-Fi' => ['project', 'site'],
        'Network monitoring & IT support' => ['month', 'site'],
        'Network Security' => ['project', 'site'],
        'Web Development' => ['website'],
        'Software Development' => ['project'],
        'Android Development' => ['app'],
        'iOS Development' => ['app'],
        'Mobile App Maintenance' => ['month'],
        'App Store Optimization' => ['app'],
        'SEO Optimization' => ['month'],
        'Media Production' => ['project'],
        'Event Livestream' => ['event'],
        'Graphic Design & Creative Content' => ['project', 'design'],
        'Event photography' => ['event'],
        'Event videography' => ['event'],
        'Drone Aerial Footage' => ['shoot'],
        'Event Recording' => ['event'],
        'Posters & flyers' => ['design', 'flyer', 'poster'],
        'Branding & Logo Design' => ['project', 'logo'],
    ];
    $units = $countableUnits[$service] ?? [];
    $numberWords = [
        'one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5,
        'six' => 6, 'seven' => 7, 'eight' => 8, 'nine' => 9, 'ten' => 10,
        'eleven' => 11, 'twelve' => 12, 'thirteen' => 13, 'fourteen' => 14,
        'fifteen' => 15, 'sixteen' => 16, 'seventeen' => 17, 'eighteen' => 18,
        'nineteen' => 19, 'twenty' => 20, 'pair of' => 2, 'a pair of' => 2,
    ];

    $quantity = 1;
    $matchedUnit = '';
    foreach ($units as $unit) {
        $unitPattern = preg_quote($unit, '/') . '(?:s)?';
        foreach ([
            '/(?<![\p{L}\p{N}])(?<count>\d{1,3}|a\s+pair\s+of|pair\s+of|' .
                implode('|', array_keys($numberWords)) . ')\s*(?:x|×)?\s*' .
                $unitPattern . '(?!\p{L})/iu',
            '/(?<![\p{L}\p{N}])' . $unitPattern .
                '\s*(?:x|×|:)\s*(?<count>\d{1,3}|a\s+pair\s+of|pair\s+of|' .
                implode('|', array_keys($numberWords)) . ')(?!\p{L})/iu',
        ] as $pattern) {
            if (preg_match_all($pattern, $description, $matches, PREG_SET_ORDER) === false) {
                throw new RuntimeException('Unable to parse the project quantity.');
            }
            foreach ($matches as $match) {
                $rawCount = strtolower(preg_replace('/\s+/', ' ', trim($match['count'])) ?? '');
                $matchedCount = ctype_digit($rawCount) ? (int)$rawCount : ($numberWords[$rawCount] ?? 1);
                if ($matchedCount > $quantity) {
                    $quantity = $matchedCount;
                    $matchedUnit = $unit;
                } elseif ($matchedCount === 1 && $matchedUnit === '') {
                    $matchedUnit = $unit;
                }
            }
        }
    }

    return [
        'quantity' => $quantity,
        'detected' => $matchedUnit !== '',
        'unit' => $matchedUnit,
    ];
}

function booking_pricing_estimate(string $service, string $scope, int $quantity = 1): ?array
{
    $config = booking_pricing_config();
    if (!isset($config['scope_factors'][$scope]) || !array_key_exists($service, $config['services']) ||
        $quantity < 1 || $quantity > 100) {
        return null;
    }

    $servicePricing = $config['services'][$service];
    if (!is_array($servicePricing)) {
        return null;
    }
    $scopeFactor = $config['scope_factors'][$scope];
    $minimum = (int)round($servicePricing['minimum'] * $scopeFactor['minimum'] * $quantity);
    $maximum = (int)round($servicePricing['maximum'] * $scopeFactor['maximum'] * $quantity);
    return [
        'service' => $service,
        'scope' => $scope,
        'scope_label' => $scopeFactor['label'],
        'minimum' => $minimum,
        'maximum' => max($minimum, $maximum),
        'currency' => $config['currency'],
        'unit' => $servicePricing['unit'],
        'quantity' => $quantity,
    ];
}

function booking_pricing_quantity_label(array $estimate, array $quantityDetails): string
{
    $unit = $quantityDetails['unit'] ?? '';
    if ($unit === '') {
        $unit = preg_replace('/^per\s+/', '', (string)($estimate['unit'] ?? 'unit')) ?? 'unit';
    }

    $quantity = (int)($estimate['quantity'] ?? 1);
    if ($quantity !== 1 && !str_ends_with($unit, 's')) {
        $unit .= 's';
    }

    return $quantity . ' ' . $unit;
}

function booking_pricing_format_amount(int $amount): string
{
    return number_format($amount, 0, '.', ',');
}
