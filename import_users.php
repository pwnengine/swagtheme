foreach ($data as $u) {
    // Check if username or email already exists
    $query = $db->simple_select("users", "uid", "username = '".$db->escape_string($u['username'])."' OR email = '".$db->escape_string($u['email'])."'");
    
    if ($db->num_rows($query) > 0) {
        echo "Skipped (already exists): {$u['username']}\n";
        continue;
    }

    // Your existing user array setup...
    $birth_time = strtotime($u["birth_date"]);
    $join_time = strtotime($u["join_date"]);

    $user = [
        "username" => $u["username"],
        "password" => $u["password"],
        "password2" => $u["password"],
        "email" => $u["email"],
        "email2" => $u["email"],
        "usergroup" => 2,
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
        "options" => [ /* ... your options ... */ ]
    ];

    $userhandler->set_data($user);

    if ($userhandler->validate_user()) {
        $user_info = $userhandler->insert_user();
        $uid = $user_info['uid'];

        $db->update_query("users", [
            "signature" => $db->escape_string($u["bio"])
        ], "uid={$uid}");

        echo "Created: {$u['username']}\n";
    } else {
        $errors = $userhandler->get_friendly_errors();
        echo "Failed: {$u['username']} - Errors: " . implode(", ", $errors) . "\n";
    }
}
