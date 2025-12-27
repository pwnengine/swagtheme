<?php

define('IN_MYBB', 1);
require_once __DIR__ . "/global.php";

echo "Starting avatar assignment...\n";

// 1. Get all avatar files from your seed folder
$seed_dir = MYBB_ROOT . "uploads/avatars/seed/";
$avatar_files = glob($seed_dir . "*.{jpg,png,gif}", GLOB_BRACE);

if (empty($avatar_files)) {
    die("Error: No avatar files found in $seed_dir\n");
}

echo "Found " . count($avatar_files) . " avatar files.\n";

// 2. Make sure the uploads/avatars folder is writable (chmod 777 or 755 depending on server)
$upload_dir = MYBB_ROOT . "uploads/avatars/";
if (!is_writable($upload_dir)) {
    die("Error: Uploads avatars folder is not writable: $upload_dir\n");
}

// 3. Get all users (except admin uid=1)
$query = $db->simple_select("users", "uid, username", "uid > 1");
$processed = 0;
$skipped = 0;

while ($user = $db->fetch_array($query)) {
    // Pick random avatar
    $source_path = $avatar_files[array_rand($avatar_files)];
    $filename = basename($source_path);

    // Destination path (unique filename to avoid overwrites)
    $dest_path = $upload_dir . $filename;

    // If file already exists, just use it (no need to copy again)
    if (!file_exists($dest_path)) {
        if (!copy($source_path, $dest_path)) {
            echo "Failed to copy avatar for UID {$user['uid']} ({$user['username']})\n";
            $skipped++;
            continue;
        }
    }

    // Get real dimensions
    $size = getimagesize($dest_path);
    if ($size === false) {
        echo "Invalid image for UID {$user['uid']} ({$user['username']})\n";
        $skipped++;
        continue;
    }

    $dimensions = $size[0] . "|" . $size[1];

    // Update user record
    $db->update_query("users", [
        "avatar"          => "uploads/avatars/" . $filename,
        "avatardimensions" => $dimensions,
        "avatartype"      => "upload"
    ], "uid = '{$user['uid']}'");

    echo "Assigned avatar to {$user['username']} (UID: {$user['uid']}) - $filename ({$dimensions})\n";
    $processed++;
}

echo "\nAvatar assignment finished.\n";
echo "Processed: $processed users\n";
echo "Skipped/Failed: $skipped\n";
