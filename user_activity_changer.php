$query = $db->simple_select("users", "uid, regdate");
while ($u = $db->fetch_array($query)) {
    $lastactive = $u['regdate'] + rand(86400, 86400 * 180);

    $db->update_query("users", [
        "lastvisit" => $lastactive - rand(3600, 86400),
        "lastactive" => $lastactive
    ], "uid={$u['uid']}");
}
