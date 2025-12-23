$avatars = glob(MYBB_ROOT . "uploads/avatars/seed/*.{jpg,png}", GLOB_BRACE);

$query = $db->simple_select("users", "uid", "uid > 1");
while ($u = $db->fetch_array($query)) {
    $avatar = $avatars[array_rand($avatars)];
    $filename = basename($avatar);

    copy($avatar, MYBB_ROOT . "uploads/avatars/$filename");

    $db->update_query("users", [
        "avatar" => "uploads/avatars/$filename",
        "avatardimensions" => "120|120",
        "avatartype" => "upload"
    ], "uid={$u['uid']}");
}
