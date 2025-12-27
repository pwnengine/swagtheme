
<?php
define('IN_MYBB', 1);
define('NO_ONLINE', 1);

require_once __DIR__ . "/global.php";
require_once MYBB_ROOT . "inc/datahandlers/user.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

$data = json_decode(file_get_contents("users.json"), true);

$userhandler = new UserDataHandler("insert");

foreach ($data as $u) {
    $birth_time = strtotime($u["birth_date"]);
    $join_time = strtotime($u["join_date"]);

    $user = [
        "username" => $u["username"],
        "password" => $u["password"],
        "password2" => $u["password"],
        "email" => $u["email"],
        "email2" => $u["email"],
        "usergroup" => 2, // Registered
        "referrer" => "",
        "timezone" => "0",
        "language" => "",
        "regdate" => $join_time,
        "birthday" => [
            "day" => date("j", $birth_time),
            "month" => date("n", $birth_time),
            "year" => date("Y", $birth_time)
        ],
        "profile_fields" => [],
        "options" => [
            "allownotices" => 1,
            "hideemail" => 0,
            "subscriptionmethod" => 0,
            "invisible" => 0,
            "receivepms" => 1,
            "pmnotice" => 1,
            "pmnotify" => 0,
            "threadmode" => "",
            "showsigs" => 1,
            "showavatars" => 1,
            "showquickreply" => 1,
            "showredirect" => 1,
            "tpp" => 0,
            "ppp" => 0,
            "daysprune" => 0,
            "dateformat" => "",
            "timeformat" => "",
            "dst" => 0,
            "dstcorrection" => 0,
            "receivefrombuddy" => 0,
            "pmrequirepassword" => 0
        ]
    ];

    $userhandler->set_data($user);

    if ($userhandler->validate_user()) {
        $user_info = $userhandler->insert_user();
        $uid = $user_info['uid'];

        // Set signature as bio
        $db->update_query("users", [
            "signature" => $db->escape_string($u["bio"])
        ], "uid={$uid}");

        echo "Created: {$u['username']}\n";
    } else {
        $errors = $userhandler->get_friendly_errors();
        echo "Failed: {$u['username']} - Errors: " . implode(", ", $errors) . "\n";
    }
}
