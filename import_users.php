<?php
define('IN_MYBB', 1);
define('NO_ONLINE', 1);
define('NO_DEBUG', 1);

require_once __DIR__ . "/global.php";
require_once MYBB_ROOT . "inc/datahandlers/user.php";

$data = json_decode(file_get_contents("dummy_users.json"), true);

$userhandler = new UserDataHandler("insert");

foreach ($data as $u) {

    $user = [
        "username" => $u["username"],
        "password" => $u["password"],
        "password2" => $u["password"],
        "email" => $u["email"],
        "email2" => $u["email"],
        "usergroup" => 2, // Registered
        "additionalgroups" => "",
        "birthday" => date("d-m-Y", strtotime($u["birth_date"])),
        "regdate" => strtotime($u["join_date"]),
        "profile_fields" => [],
        "options" => [
            "showsigs" => 1,
            "showavatars" => 1,
            "showquickreply" => 1
        ]
    ];

    $userhandler->set_data($user);

    if ($userhandler->validate_user()) {
        $uid = $userhandler->insert_user();

        // Set bio
        $db->update_query("users", [
            "bio" => $db->escape_string($u["bio"])
        ], "uid={$uid}");

        echo "Created: {$u['username']}\n";
    } else {
        echo "Failed: {$u['username']}\n";
    }
}
