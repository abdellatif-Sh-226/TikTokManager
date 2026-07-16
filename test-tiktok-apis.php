<?php
// ======================================================
// TikTok API Tester (v2 — POST fix)
// ======================================================

$accessToken = 'act.PSumeDGNdgUdDKna9KmgCG2ccSCvPiFcax7LHvXt4UHJLQOWhYWP0xeg4Y4G!6282.s1';
$openId = '-00040zlTfoTAeBGBva61LZpFJTbKT8ZY6CA';
$baseUrl = 'https://open.tiktokapis.com/';
$results = [];

function callGet($url, $headers) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => $headers,
    ]);
    $r = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    return ['http_code' => $code, 'response' => $r ? json_decode($r, true) : null, 'error' => $err ?: null];
}

function callPost($url, $headers, $body) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($body),
    ]);
    $r = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    return ['http_code' => $code, 'response' => $r ? json_decode($r, true) : null, 'error' => $err ?: null];
}

$authHeaders = [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json',
];

// ===================================================================
// API 1: v2/user/info/ — GET
// ===================================================================
echo "📡 (1/5) v2/user/info/ — GET ...\n";
$results['user_info'] = callGet($baseUrl . 'v2/user/info/?' . http_build_query([
    'fields' => 'open_id,union_id,avatar_url,display_name,username,follower_count,following_count,likes_count,video_count',
]), $authHeaders);

// ===================================================================
// API 2: v2/video/list/ — POST with JSON body
// ===================================================================
echo "📡 (2/5) v2/video/list/ — POST ...\n";
$results['video_list'] = callPost(
    $baseUrl . 'v2/video/list/?' . http_build_query([
        'fields' => 'id,title,cover_image_url,view_count,like_count,comment_count,share_count,create_time',
    ]),
    $authHeaders,
    ['max_count' => 20, 'cursor' => 0]
);

// ===================================================================
// API 3: v2/video/publish/status/ — POST (ken 3ndna posts)
// ===================================================================
echo "📡 (3/5) v2/video/publish/status/ — POST ...\n";
$results['publish_status'] = callPost(
    $baseUrl . 'v2/video/publish/status/',
    $authHeaders,
    ['open_id' => $openId]
);

// ===================================================================
// APIs 4 & 5: b video ID (ken mawjoud)
// ===================================================================
$videoId = null;
if ($results['video_list']['response'] && isset($results['video_list']['response']['data']['videos'][0]['id'])) {
    $videoId = $results['video_list']['response']['data']['videos'][0]['id'];
    echo "✅ Video ID found: $videoId\n";
} else {
    echo "⚠️ No videos returned — skipping video-specific APIs\n";
}

if ($videoId) {
    // API 4: v2/video/query/ — POST
    echo "📡 (4/5) v2/video/query/ — POST ...\n";
    $results['video_query'] = callPost(
        $baseUrl . 'v2/video/query/?' . http_build_query([
            'fields' => 'id,title,cover_image_url,view_count,like_count,comment_count,share_count,create_time',
        ]),
        $authHeaders,
        ['video_id' => $videoId]
    );

    // API 5: v2/video/comment/list/ — POST
    echo "📡 (5/5) v2/video/comment/list/ — POST ...\n";
    $results['video_comments'] = callPost(
        $baseUrl . 'v2/video/comment/list/?' . http_build_query([
            'fields' => 'id,text,create_time,like_count,reply_count,user',
        ]),
        $authHeaders,
        ['video_id' => $videoId, 'max_count' => 20, 'cursor' => 0]
    );
} else {
    $results['video_query'] = ['http_code' => 0, 'response' => null, 'error' => 'No video ID'];
    $results['video_comments'] = ['http_code' => 0, 'response' => null, 'error' => 'No video ID'];
}

// ===================================================================
// Save results
// ===================================================================
$outputFile = __DIR__ . '/tiktok-api-responses.json';
file_put_contents($outputFile, json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// ===================================================================
// Display summary
// ===================================================================
echo "\n" . str_repeat('=', 70) . "\n";
echo "  📊 RESULTS SUMMARY\n";
echo str_repeat('=', 70) . "\n\n";

foreach ($results as $name => $data) {
    echo "● $name\n";
    echo "  └─ HTTP " . $data['http_code'];
    if ($data['error']) {
        echo " ❌ " . $data['error'];
    } elseif ($data['response']) {
        echo " ✅\n";

        if (isset($data['response']['data']['user'])) {
            $u = $data['response']['data']['user'];
            echo "     ├─ display_name:   " . ($u['display_name'] ?? 'N/A') . "\n";
            echo "     ├─ username:        " . ($u['username'] ?? 'N/A') . "\n";
            echo "     ├─ follower_count:  " . ($u['follower_count'] ?? 'N/A') . "\n";
            echo "     ├─ following_count: " . ($u['following_count'] ?? 'N/A') . "\n";
            echo "     ├─ likes_count:     " . ($u['likes_count'] ?? 'N/A') . "\n";
            echo "     ├─ video_count:     " . ($u['video_count'] ?? 'N/A') . "\n";
            echo "     └─ avatar_url:      " . ($u['avatar_url'] ?? 'N/A') . "\n";
        }

        if (isset($data['response']['data']['videos'])) {
            $vids = $data['response']['data']['videos'];
            echo "     └─ Videos (" . count($vids) . "):\n";
            foreach ($vids as $i => $v) {
                echo "        " . ($i+1) . ". id=$v[id]\n";
                echo "           title:    " . ($v['title'] ?? 'N/A') . "\n";
                echo "           views:    " . ($v['view_count'] ?? 0) . "\n";
                echo "           likes:    " . ($v['like_count'] ?? 0) . "\n";
                echo "           comments: " . ($v['comment_count'] ?? 0) . "\n";
                echo "           shares:   " . ($v['share_count'] ?? 0) . "\n";
            }
        }

        if (isset($data['response']['data']['comments'])) {
            $cmts = $data['response']['data']['comments'];
            echo "     └─ Comments (" . count($cmts) . "):\n";
            foreach ($cmts as $i => $c) {
                echo "        " . ($i+1) . ". id=$c[id]\n";
                echo "           text:       " . ($c['text'] ?? 'N/A') . "\n";
                echo "           like_count: " . ($c['like_count'] ?? 0) . "\n";
                echo "           reply_count: " . ($c['reply_count'] ?? 0) . "\n";
            }
        }

        if (str_contains($name, 'status') && isset($data['response']['data'])) {
            echo "     └─ " . json_encode($data['response']['data'], JSON_UNESCAPED_SLASHES) . "\n";
        }
    } else {
        echo " ❌ No response\n";
    }
    echo "\n";
}

echo str_repeat('=', 70) . "\n";
echo "  ✅ Full JSON saved to: $outputFile\n";
echo str_repeat('=', 70) . "\n";
