<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    die("Nincs jogosultságod.");
}

$_SESSION['source_modal'] = 'profile'; // A hibaüzenet forrása

if (isset($_FILES["profile_image"]) && $_FILES["profile_image"]["error"] == UPLOAD_ERR_OK) {
    
    // Fájlméret ellenőrzése (max 2MB)
    if ($_FILES["profile_image"]["size"] > 2000000) {
        $_SESSION['upload_error'] = "Hiba: A fájl mérete túl nagy (maximum 2MB).";
        header("Location: profile.php");
        exit();
    }

    $imageFileType = strtolower(pathinfo(basename($_FILES["profile_image"]["name"]), PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png'];
    if (!in_array($imageFileType, $allowed_types)) {
        $_SESSION['upload_error'] = "Hiba: Csak JPG, JPEG és PNG fájlok engedélyezettek.";
        header("Location: profile.php");
        exit();
    }
    
    $target_dir = "uploads/";
    $new_filename = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $imageFileType;
    $final_target_file = $target_dir . $new_filename;

    if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $final_target_file)) {
        try {
            $stmt = $conn->prepare("UPDATE users SET profile_image = :path WHERE id = :id");
            $stmt->bindParam(':path', $final_target_file);
            $stmt->bindParam(':id', $_SESSION['user_id']);
            $stmt->execute();
        } catch (PDOException $e) {
            $_SESSION['upload_error'] = "Adatbázis hiba a kép mentésekor.";
        }
    } else {
        $_SESSION['upload_error'] = "Hiba a fájl feltöltése során.";
    }
} else {
    $_SESSION['upload_error'] = "Nem választottál fájlt vagy feltöltési hiba történt.";
}

header("Location: profile.php");
exit();
?>