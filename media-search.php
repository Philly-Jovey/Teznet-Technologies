<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function youtube_http_get(string $url): array
{
    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $body = curl_exec($curl);
        $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        return ['body' => is_string($body) ? $body : null, 'status' => $status, 'error' => $error];
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10,
            'ignore_errors' => true,
            'follow_location' => 0,
            'max_redirects' => 0,
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ],
    ]);
    $warning = '';
    set_error_handler(static function (int $severity, string $message) use (&$warning): bool {
        $warning = $message;
        return true;
    });
    try {
        $body = file_get_contents($url, false, $context);
    } finally {
        restore_error_handler();
    }

    $status = 0;
    foreach ($http_response_header ?? [] as $header) {
        if (preg_match('/^HTTP\/\S+\s+(\d{3})\b/', $header, $matches)) {
            $status = (int)$matches[1];
            break;
        }
    }
    return ['body' => is_string($body) ? $body : null, 'status' => $status, 'error' => $warning];
}

function media_search_response(int $status, array $body): void
{
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function youtube_api_request(string $endpoint, array $parameters, string $apiKey): array
{
    $parameters['key'] = $apiKey;
    $url = 'https://www.googleapis.com/youtube/v3/' . $endpoint . '?' . http_build_query($parameters);
    $result = youtube_http_get($url);
    $response = $result['body'];
    $status = $result['status'];
    $error = $result['error'];

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

function youtube_video_id(array $item): string
{
    $videoId = $item['id']['videoId'] ?? $item['snippet']['resourceId']['videoId'] ?? '';
    return is_string($videoId) && preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId) ? $videoId : '';
}

function youtube_recent_uploads(): array
{
    $feedUrl = 'https://www.youtube.com/feeds/videos.xml?channel_id=UCgwlvLIdEZanHDlG_4nYBiA';
    $result = youtube_http_get($feedUrl);
    $response = $result['body'];
    $status = $result['status'];
    $error = $result['error'];

    if (!is_string($response) || $status < 200 || $status >= 300) {
        error_log('YouTube RSS request failed: HTTP ' . $status . ($error !== '' ? ' ' . $error : ''));
        throw new RuntimeException('The YouTube video feed is temporarily unavailable.');
    }
    if (!function_exists('simplexml_load_string')) {
        throw new RuntimeException('The server is missing the SimpleXML extension.');
    }

    $feed = simplexml_load_string($response, SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);
    if (!$feed instanceof SimpleXMLElement) {
        throw new RuntimeException('The YouTube video feed returned invalid data.');
    }

    $videos = [];
    foreach ($feed->entry ?? [] as $entry) {
        $youtube = $entry->children('http://www.youtube.com/xml/schemas/2015');
        $videoId = (string)($youtube->videoId ?? '');
        if (!preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId)) {
            continue;
        }

        $videos[] = [
            'id' => $videoId,
            'title' => (string)($entry->title ?? 'Teznet Media video'),
            'thumbnail' => 'https://i.ytimg.com/vi/' . $videoId . '/hqdefault.jpg',
            'url' => 'https://www.youtube.com/watch?v=' . $videoId,
            'upcoming' => false,
            'publishedAt' => (string)($entry->published ?? ''),
        ];
        if (count($videos) === 4) {
            break;
        }
    }

    return $videos;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    media_search_response(405, ['error' => 'Use the video search form to search the channel.']);
}

$streamsRequest = ($_GET['streams'] ?? '') === '1';
if ($streamsRequest) {
    $cachePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'teznet-media-streams-' . hash('sha256', __DIR__) . '.json';
    if (is_file($cachePath) && time() - (int)filemtime($cachePath) < 1800) {
        $cachedResponse = json_decode((string)file_get_contents($cachePath), true);
        if (is_array($cachedResponse) && isset($cachedResponse['videos']) && is_array($cachedResponse['videos'])) {
            media_search_response(200, $cachedResponse);
        }
    }

    $apiKey = getenv('YOUTUBE_API_KEY') ?: '';
    if ($apiKey === '') {
        try {
            $streamsResponse = ['title' => 'Latest videos', 'videos' => youtube_recent_uploads()];
            $cacheJson = json_encode($streamsResponse, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if (!is_string($cacheJson) || file_put_contents($cachePath, $cacheJson, LOCK_EX) === false) {
                error_log('Unable to cache the Teznet Media YouTube stream list.');
            }
            media_search_response(200, $streamsResponse);
        } catch (RuntimeException $exception) {
            media_search_response(502, ['error' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            error_log('Media YouTube RSS error: ' . $exception->getMessage());
            media_search_response(500, ['error' => 'The YouTube video feed is temporarily unavailable. Please try again later.']);
        }
    }

    try {
        $channelData = youtube_api_request('channels', [
            'part' => 'id,contentDetails',
            'forHandle' => 'teznetmedia',
        ], $apiKey);
        $channel = $channelData['items'][0] ?? [];
        $channelId = $channel['id'] ?? '';
        $uploadsPlaylistId = $channel['contentDetails']['relatedPlaylists']['uploads'] ?? '';
        if (!is_string($channelId) || $channelId === '' || !is_string($uploadsPlaylistId) || $uploadsPlaylistId === '') {
            media_search_response(502, ['error' => 'The Teznet Media YouTube channel could not be found.']);
        }

        $upcomingData = youtube_api_request('search', [
            'part' => 'snippet',
            'channelId' => $channelId,
            'eventType' => 'upcoming',
            'type' => 'video',
            'videoEmbeddable' => 'true',
            'maxResults' => 4,
            'order' => 'date',
        ], $apiKey);

        $videos = [];
        foreach ($upcomingData['items'] ?? [] as $item) {
            if (!is_array($item) || !is_array($item['snippet'] ?? null)) {
                continue;
            }
            $videoId = youtube_video_id($item);
            if ($videoId === '') {
                continue;
            }
            $snippet = $item['snippet'];
            $videos[] = [
                'id' => $videoId,
                'title' => (string)($snippet['title'] ?? 'Upcoming Teznet Media stream'),
                'thumbnail' => 'https://i.ytimg.com/vi/' . $videoId . '/hqdefault.jpg',
                'url' => 'https://www.youtube.com/watch?v=' . $videoId,
                'upcoming' => true,
                'publishedAt' => (string)($snippet['publishedAt'] ?? ''),
            ];
        }

        $sectionTitle = 'Upcoming live streams';
        if ($videos === []) {
            $uploadsData = youtube_api_request('playlistItems', [
                'part' => 'snippet',
                'playlistId' => $uploadsPlaylistId,
                'maxResults' => 4,
            ], $apiKey);

            foreach ($uploadsData['items'] ?? [] as $item) {
                if (!is_array($item) || !is_array($item['snippet'] ?? null)) {
                    continue;
                }
                $videoId = youtube_video_id($item);
                if ($videoId === '') {
                    continue;
                }
                $snippet = $item['snippet'];
                $videos[] = [
                    'id' => $videoId,
                    'title' => (string)($snippet['title'] ?? 'Teznet Media video'),
                    'thumbnail' => 'https://i.ytimg.com/vi/' . $videoId . '/hqdefault.jpg',
                    'url' => 'https://www.youtube.com/watch?v=' . $videoId,
                    'upcoming' => false,
                    'publishedAt' => (string)($snippet['publishedAt'] ?? ''),
                ];
            }
            $sectionTitle = 'Latest videos';
        }

        $streamsResponse = ['title' => $sectionTitle, 'videos' => $videos];
        $cacheJson = json_encode($streamsResponse, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (!is_string($cacheJson) || file_put_contents($cachePath, $cacheJson, LOCK_EX) === false) {
            error_log('Unable to cache the Teznet Media YouTube stream list.');
        }
        media_search_response(200, $streamsResponse);
    } catch (RuntimeException $exception) {
        media_search_response(502, ['error' => $exception->getMessage()]);
    } catch (Throwable $exception) {
        error_log('Media YouTube streams error: ' . $exception->getMessage());
        media_search_response(500, ['error' => 'The YouTube stream list is temporarily unavailable. Please try again later.']);
    }
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
        $videoId = youtube_video_id($item);
        $snippet = $item['snippet'] ?? [];
        if ($videoId === '' || !is_array($snippet)) {
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
