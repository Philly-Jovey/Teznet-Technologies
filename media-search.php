<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function media_search_response(int $status, array $body): void
{
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function youtube_api_request(string $endpoint, array $parameters, string $apiKey): array
{
    if (!function_exists('curl_init')) {
        throw new RuntimeException('The server is missing the cURL extension.');
    }

    $parameters['key'] = $apiKey;
    $url = 'https://www.googleapis.com/youtube/v3/' . $endpoint . '?' . http_build_query($parameters);
    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $response = curl_exec($curl);
    $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);

    if (!is_string($response) || $status < 200 || $status >= 300) {
        error_log('YouTube API request failed: HTTP ' . $status . ($error !== '' ? ' ' . $error : ''));
        throw new RuntimeException('The video search service is temporarily unavailable.');
    }

    $data = json_decode($response, true);
    if (!is_array($data)) {
        throw new RuntimeException('The video search service returned an invalid response.');
    }
    return $data;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    media_search_response(405, ['error' => 'Use the video search form to search the channel.']);
}

$query = $_GET['q'] ?? '';
if (!is_string($query)) {
    media_search_response(400, ['error' => 'Enter a search phrase.']);
}
$query = trim($query);
if ($query === '' || strlen($query) > 100) {
    media_search_response(400, ['error' => 'Enter a search phrase up to 100 characters long.']);
}

$apiKey = getenv('YOUTUBE_API_KEY') ?: '';
if ($apiKey === '') {
    media_search_response(503, ['error' => 'Video search is being set up. Please browse the Teznet Media YouTube channel in the meantime.']);
}

try {
    $channelData = youtube_api_request('channels', [
        'part' => 'id',
        'forHandle' => 'teznetmedia',
    ], $apiKey);
    $channelId = $channelData['items'][0]['id'] ?? '';
    if (!is_string($channelId) || $channelId === '') {
        media_search_response(502, ['error' => 'The Teznet Media YouTube channel could not be found.']);
    }

    $searchData = youtube_api_request('search', [
        'part' => 'snippet',
        'channelId' => $channelId,
        'q' => $query,
        'type' => 'video',
        'videoEmbeddable' => 'true',
        'maxResults' => 8,
        'order' => 'relevance',
    ], $apiKey);

    $videos = [];
    foreach ($searchData['items'] ?? [] as $item) {
        $videoId = $item['id']['videoId'] ?? '';
        $snippet = $item['snippet'] ?? [];
        if (!is_string($videoId) || !preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId) || !is_array($snippet)) {
            continue;
        }
        $videos[] = [
            'id' => $videoId,
            'title' => (string)($snippet['title'] ?? 'Teznet Media video'),
            'description' => (string)($snippet['description'] ?? ''),
            'thumbnail' => 'https://i.ytimg.com/vi/' . $videoId . '/hqdefault.jpg',
            'url' => 'https://www.youtube.com/watch?v=' . $videoId,
        ];
    }

    media_search_response(200, ['videos' => $videos]);
} catch (RuntimeException $exception) {
    media_search_response(502, ['error' => $exception->getMessage()]);
} catch (Throwable $exception) {
    error_log('Media YouTube search error: ' . $exception->getMessage());
    media_search_response(500, ['error' => 'Video search is temporarily unavailable. Please try again later.']);
}
