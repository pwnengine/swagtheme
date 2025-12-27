<?php
// create_dummy_threads.php
// Run with: php create_dummy_threads.php
// Place in MyBB root directory

define('IN_MYBB', 1);
require_once __DIR__ . "/global.php";
require_once MYBB_ROOT . "inc/functions_post.php";

// Forum IDs — CHANGE THESE TO MATCH YOUR ACTUAL FORUM IDs
$looksmaxxing_fid = 3;   // Your Looksmaxxing forum ID
$peds_fid          = 4;  // Your Steroids/PEDs forum ID

// Looksmaxxing-themed titles and posts
$looks_titles = [
    "Frame check pls — 5'8 gang or nah?",
    "Mewing + thumbpulling — results after 6 months?",
    "Jaw filler experience — worth the pain?",
    "Hairline army unite — finasteride or embrace?",
    "Subhuman to chad pipeline — my glow up progress",
    "Heightmaxxing cope — LL surgery fund started",
    "5'9 cope — shoes + insoles stack",
    "Aestheticmaxxing — 10% BF goal check",
    "Mewing since 2019 — palate expansion update",
    "Jaw surgery or fillers? Need advice"
];

$looks_posts = [
    "Been mewing religiously for 2 years. Cheekbones popping more but still recessed. Anyone got before/after pics?",
    "Just got jaw filler (2ml Voluma). Swelling down, looks sharper. Cost $900 but worth it.",
    "5'8 here — starting LL fund. Anyone done it in Turkey? Costs?",
    "Hair thinning bad — started fin 1mg + minox. Shedding phase sucks.",
    "Frame mogged by everyone. Gym + posturemaxxing helping?",
    "Mewing + facepulls — palate seems wider but no height gain.",
    "Subhuman cope: lighting + angles + gym. Real progress or delusion?",
    "Aesthetic goals: lean + wide clavicles. Current 15% BF — tips?"
];

// PEDs/Steroids-themed titles and posts
$peds_titles = [
    "First cycle advice? 500mg Test E newbie",
    "Tren sides — night sweats and paranoia real?",
    "Bloodwork before blast — HDL crashed",
    "MK-677 sleep insane but appetite crazy",
    "Anyone running RETA + CAG stack?",
    "Gyno lump after week 3 — Nolva dosage?",
    "Source review — bunk Var from last order",
    "Cycle log: Week 4 on Test + Primo",
    "RAD-140 strength gains — suppression yet?",
    "BPC-157 + TB-500 for shoulder injury"
];

$peds_posts = [
    "Starting 500mg Test E weekly. Bloods good pre-cycle. When to start AI?",
    "Tren A 400mg/wk — aggression through the roof. Normal or drop?",
    "Bloods: Test 3200, E2 50, HDL 25. Cardarine needed?",
    "MK-677 25mg/night — sleeping like a king but eating 5k cal.",
    "RETA + CAG combo — heard it's the new god stack. Logs?",
    "Small lump under nip — Nolva 20mg/day? How long?",
    "Source sent underdosed Var — no pumps at 50mg/day.",
    "Week 4: 300mg Test + 200mg Primo. Veins popping, no sides.",
    "RAD-140 20mg — strength up 25%. Liver ok so far.",
    "BPC-157 500mcg + TB-500 — elbow pain gone in 2 weeks."
];

// Get all users except admin
$users = [];
$query = $db->simple_select("users", "uid, username", "uid > 1");
while ($user = $db->fetch_array($query)) {
    $users[] = $user;
}

if (empty($users)) {
    die("No users found in database (except admin).\n");
}

echo "Starting thread creation...\n";
echo "Found " . count($users) . " users.\n";
echo "Creating 30 random threads...\n\n";

for ($i = 0; $i < 30; $i++) {
    // Pick random user
    $user = $users[array_rand($users)];
    $uid = $user['uid'];
    $username = $user['username'];

    // Random time: now minus 1-120 days
    $time = TIME_NOW - rand(86400, 86400 * 120);

    // Decide if looksmaxxing or PEDs thread (50/50 chance)
    $is_looksmaxxing = (mt_rand(0, 1) === 0);

    if ($is_looksmaxxing) {
        $fid = $looksmaxxing_fid;
        $subject = $looks_titles[array_rand($looks_titles)];
        $message = $looks_posts[array_rand($looks_posts)];
        $category = "Looksmaxxing";
    } else {
        $fid = $peds_fid;
        $subject = $peds_titles[array_rand($peds_titles)];
        $message = $peds_posts[array_rand($peds_posts)];
        $category = "PEDs";
    }

    // Build thread array
    $thread = [
        "fid"           => $fid,
        "subject"       => $subject,
        "uid"           => $uid,
        "username"      => $username,
        "dateline"      => $time,
        "lastpost"      => $time,
        "lastposter"    => $username,
        "lastposteruid" => $uid,
        "message"       => $message,
        "ipaddress"     => "127.0.0.1",
        "replyto"       => 0,
        "visible"       => 1,
        "closed"        => 0,
        "posthash"      => md5(uniqid(rand(), true)),
        "poll"          => 0,
        "numreplies"    => 0,
        "numviews"      => rand(10, 500),
        "icon"          => 0
    ];

    // Insert
    $tid = insert_thread($thread);

    if ($tid > 0) {
        echo "Created $category thread: {$subject} by {$username} (TID: {$tid})\n";
    } else {
        echo "Failed to create thread: {$subject}\n";
    }
}

echo "\nDone! 30 threads created (split between Looksmaxxing and PEDs forums).\n";

// Update forum counters
require_once MYBB_ROOT . "inc/functions.php";
update_forum_counters($looksmaxxing_fid);
update_forum_counters($peds_fid);
update_stats();
echo "Forum counters and stats updated.\n";
