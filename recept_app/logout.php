<?php
// logout.php
session_start();

// 1. Töröljük az összes session változót
$_SESSION = array();

// 2. Töröljük a session cookie-t (ha van)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Végül szüntessük meg a sessiont
session_destroy();

// 4. Átirányítás a főoldalra
header("Location: index.php");
exit;
?>