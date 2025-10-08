<?php
// upload_logo.php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

// Beállítjuk, hogy a hiba melyik modálhoz tartozik
$_SESSION['source_modal'] = 'logo';

$target_dir = "uploads/";
if (!is_dir($target_dir)) { @mkdir($target_dir, 0755, true); }

if (isset($_FILES["logo_image"]) && $_FILES["logo_image"]["error"] == UPLOAD_ERR_OK) {
    
    if ($_FILES["logo_image"]["size"] > 1000000) { // Max 1MB
        $_SESSION['upload_error'] = "Hiba: A fájl mérete túl nagy (maximum 1MB).";
        header("Location: index.php");
        exit();
    }

    $imageFileType = strtolower(pathinfo(basename($_FILES["logo_image"]["name"]), PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'svg'];
    if (!in_array($imageFileType, $allowed_types)) {
        $_SESSION['upload_error'] = "Hiba: Csak JPG, PNG és SVG fájlok engedélyezettek.";
        header("Location: index.php");
        exit();
    }

    $new_filename = 'logo_' . uniqid() . '.' . $imageFileType;
    $final_target_file = $target_dir . $new_filename;

    if (move_uploaded_file($_FILES["logo_image"]["tmp_name"], $final_target_file)) {
        try {
            $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('logo_image', :path) ON DUPLICATE KEY UPDATE setting_value = :path");
            $stmt->bindParam(':path', $final_target_file);
            $stmt->execute();
            $_SESSION['upload_success'] = "A logó sikeresen frissítve!";
            unset($_SESSION['source_modal']); // Siker esetén töröljük a forrást
        } catch (PDOException $e) {
            $_SESSION['upload_error'] = "Hiba az adatbázis frissítése során.";
        }
    } else {
        $_SESSION['upload_error'] = "Kritikus hiba: A fájl mozgatása sikertelen. Ellenőrizd az 'uploads' mappa jogosultságait!";
    }
} else {
    $_SESSION['upload_error'] = "Hiba: Nem választottál fájlt, vagy a feltöltés sikertelen volt.";
}

header("Location: index.php");
exit();
?>