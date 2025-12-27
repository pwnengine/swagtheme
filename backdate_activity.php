<?php
// backdate_activity.php
// Run with: php backdate_activity.php
// Place in your MyBB root directory

define('IN_MYBB', 1);
require_once __DIR__ . "/global.php";

// Config: How far back to simulate activity (in months)
$max_months_back = 24;      // Max: up to 2 years ago
$min_months_back = 1;       // Min: at least 1 month ago
$activity_spread = 60;      // % of users get recent activity (last 60 days)

// Current time
$now = TIME_NOW;

echo "Starting user activity backdating...\n";
echo "Current time: " . date('Y-m-d H:i:s', $now) . "\n\n";

// 1. Get all users except admin
$query = $db->simple_select("users", "uid, username, regdate, lastvisit, lastpost", "uid > 1");
$updated = 0;

while ($user = $db->fetch_array($query)) {
    // Random days back for last visit (between min/max months)
    $months_back = rand($min_months_back, $max_months_back);
    $days_back = rand(0, 30); // extra random days
    $random_time = $now - ($months_back * 30 * 86400) - ($days_back * 86400);

    // Make some users look more active (recent)
    if (mt_rand(0, 100) <= $activity_spread) {
        // Recent activity: last 60 days
        $random_time = $now - rand(0, 60 * 86400);
    }

    // Ensure last visit is after registration
    $random_time = max($random_time, $user['regdate']);

    // Last login: same as last visit (MyBB only has one field for this)
    $lastvisit = $random_time;

    // Update main fields
    $update = [
        'lastvisit' => $lastvisit,
    ];

    // 2. If user has posts, backdate their lastpost timestamp
    if ($user['lastpost'] > 0) {
        // Try to get the actual latest post time
        $post_query = $db->simple_select("posts", "dateline", "uid = '{$user['uid']}'", [
            'order_by' => 'dateline',
            'order_dir' => 'DESC',
            'limit' => 1
        ]);
        if ($db->num_rows($post_query) > 0) {
            $post = $db->fetch_array($post_query);
            $update['lastpost'] = $post['dateline'];
        } else {
            // No posts? Set to something random before now
            $update['lastpost'] = $random_time - rand(0, 30 * 86400);
        }
    }

    // Apply update
    $db->update_query("users", $update, "uid = '{$user['uid']}'");

    echo "Updated: {$user['username']} (UID: {$user['uid']}) → Last visit: " . date('Y-m-d H:i:s', $lastvisit) . "\n";
    $updated++;
}

echo "\nBackdating finished.\n";
echo "Updated $updated users.\n";

// Optional: Update forum stats (last post, etc.)
require_once MYBB_ROOT . "inc/functions.php";
update_stats();
echo "Forum stats updated.\n";
