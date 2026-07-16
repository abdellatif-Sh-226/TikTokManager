<?php
$db = new SQLite3('backend/database/database.sqlite');
$result = $db->query("SELECT id, name, tiktok_open_id, tiktok_access_token, tiktok_username FROM users WHERE tiktok_open_id IS NOT NULL LIMIT 1");
if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    echo json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} else {
    echo "No TikTok user found in database\n";
    // Show all users
    $r2 = $db->query("SELECT id, name, email, tiktok_open_id FROM users");
    while ($row2 = $r2->fetchArray(SQLITE3_ASSOC)) {
        echo json_encode($row2) . "\n";
    }
}
