<?php
// import_users.php
// Run with: php import_users.php
// Place this file in your MyBB root directory (same as global.php)

define('IN_MYBB', 1);
define('NO_ONLINE', 1);

require_once __DIR__ . "/global.php";
require_once MYBB_ROOT . "inc/datahandlers/user.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load the JSON data
$json_file = __DIR__ . "/users.json";
if (!file_exists($json_file)) {
    die("Error: users.json not found in " . __DIR__ . "\n");
}

$data = json_decode(file_get_contents($json_file), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("Error: Invalid JSON in users.json - " . json_last_error_msg() . "\n");
}

$userhandler = new UserDataHandler("insert");

echo "Starting user import...\n";
echo "Total users in JSON: " . count($data) . "\n\n";

foreach ($data as $u) {
    // Skip if username or email already exists
    $query = $db->simple_select("users", "uid", 
        "username = '" . $db->escape_string($u['username']) . "' 
        OR email = '" . $db->escape_string($u['email']) . "'"
    );

    if ($db->num_rows($query) > 0) {
        echo "Skipped (already exists): {$u['username']}\n";
        continue;
    }

    $birth_time = strtotime($u["birth_date"]);
    $join_time = strtotime($u["join_date"]);

    // MyBB requires birthday as an array with day/month/year
    $user = [
        "username"         => $u["username"],
        "password"         => $u["password"],
        "password2"        => $u["password"],
        "email"            => $u["email"],
        "email2"           => $u["email"],
        "usergroup"        => 2, // Registered
        "referrer"         => "",
        "timezone"         => "0",
        "language"         => "",
        "regdate"          => $join_time,
        "birthday"         => [
            "day"   => date("j", $birth_time),
            "month" => date("n", $birth_time),
            "year"  => date("Y", $birth_time)
        ],
        "profile_fields"   => [],
        "options"          => [
            "allownotices"         => 1,
            "hideemail"            => 0,
            "subscriptionmethod"   => 0,
            "invisible"            => 0,
            "receivepms"           => 1,
            "pmnotice"             => 1,
            "pmnotify"             => 0,
            "threadmode"           => "",
            "showsigs"             => 1,
            "showavatars"          => 1,
            "showquickreply"       => 1,
            "showredirect"         => 1,
            "tpp"                  => 0,
            "ppp"                  => 0,
            "daysprune"            => 0,
            "dateformat"           => "",
            "timeformat"           => "",
            "dst"                  => 0,
            "dstcorrection"        => 0,
            "receivefrombuddy"     => 0,
            "pmrequirepassword"    => 0
        ]
    ];

    $userhandler->set_data($user);

    if ($userhandler->validate_user()) {
        $user_info = $userhandler->insert_user();
        $uid = $user_info['uid'];

        // Set bio as signature (MyBB doesn't have a separate bio field)
        $db->update_query("users", [
            "signature" => $db->escape_string($u["bio"])
        ], "uid = '{$uid}'");

        echo "Created: {$u['username']} (UID: {$uid})\n";
    } else {
        $errors = $userhandler->get_friendly_errors();
        echo "Failed: {$u['username']} - Errors: " . implode(" | ", $errors) . "\n";
    }
}

echo "\nImport finished.\n";
