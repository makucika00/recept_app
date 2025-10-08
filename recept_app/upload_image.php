<?php
// upload_image.php
session_start();
require_once 'db_config.php'; // EZ A SOR HIÁNYZOTT!

// Ellenőrizzük, hogy a felhasználó admin-e
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    http_response_code(403);
    echo json_encode(['error' => ['message' => 'Nincs jogosultságod a művelethez.']]);
    exit();
}

$accepted_origins = ["http://localhost", "http://127.0.0.1"];
if (isset($_SERVER['HTTP_ORIGIN'])) {
    if (in_array($_SERVER['HTTP_ORIGIN'], $accepted_origins)) {
        header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    } else {
        http_response_code(403);
        echo json_encode(['error' => ['message' => 'Origin Denied']]);
        return;
    }
}

$imageFolder = "uploads/";

if (!is_dir($imageFolder)) {
    @mkdir($imageFolder, 0755, true);
}

reset($_FILES);
$temp = current($_FILES);
if (is_uploaded_file($temp['tmp_name'])){
    if (preg_match("/([^\w\s\d\-_~,;:\[\]\(\).])|([\.]{2,})/", $temp['name'])) {
        header("HTTP/1.1 400 Invalid file name.");
        return;
    }

    if (!in_array(strtolower(pathinfo($temp['name'], PATHINFO_EXTENSION)), array("gif", "jpg", "png", "jpeg"))) {
        header("HTTP/1.1 400 Invalid extension.");
        return;
    }

    // Adjunk egyedi nevet a fájlnak
    $filename = uniqid('post_img_') . '.' . strtolower(pathinfo($temp['name'], PATHINFO_EXTENSION));
    $filetowrite = $imageFolder . $filename;

    if (move_uploaded_file($temp['tmp_name'], $filetowrite)) {
        // A TinyMCE ezt a JSON formátumot várja vissza
        $final_url = BASE_URL . '/' . $filetowrite;
        echo json_encode(array('location' => $final_url));
    } else {
        header("HTTP/1.1 500 Server Error");
    }
} else {
    // A hibaüzeneteket a session-be mentjük, hogy a frontend is lássa
    if (isset($_FILES['file']['error'])) {
        // A részletesebb hibakezelés, amit korábban írtunk
    } else {
         header("HTTP/1.1 400 No file uploaded.");
    }
}
?>