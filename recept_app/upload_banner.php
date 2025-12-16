<?php
session_start();
require_once 'db_config.php';

// Ellenőrizzük, hogy a felhasználó admin-e
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. BANNER CÍMÉNEK FRISSÍTÉSE
    if (isset($_POST['banner_title'])) {
        $new_title = trim($_POST['banner_title']);
        if (!empty($new_title)) {
            try {
                // Az ON DUPLICATE KEY UPDATE itt is működik, ha az a kulcs már létezik
                $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('banner_title', :value) ON DUPLICATE KEY UPDATE setting_value = :value");
                $stmt->bindParam(':value', $new_title);
                $stmt->execute();
            } catch (PDOException $e) {
                $_SESSION['upload_error'] = "Hiba a banner címének frissítése során.";
                header("Location: index.php");
                exit();
            }
        }
    }

    // 2. BANNER KÉP FRISSÍTÉSE (csak ha töltöttek fel új fájlt)
    if (isset($_FILES["banner_image"]) && $_FILES["banner_image"]["error"] != UPLOAD_ERR_NO_FILE) {
        
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) { @mkdir($target_dir, 0755, true); }
        
        if ($_FILES["banner_image"]["size"] > 2000000) {
            $_SESSION['upload_error'] = "Hiba: A fájl mérete túl nagy (maximum 2MB).";
            header("Location: index.php");
            exit();
        }

        $imageFileType = strtolower(pathinfo(basename($_FILES["banner_image"]["name"]), PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowed_types)) {
            $_SESSION['upload_error'] = "Hiba: Csak JPG, JPEG, PNG és GIF fájlok engedélyezettek.";
            header("Location: index.php");
            exit();
        }

        if (getimagesize($_FILES["banner_image"]["tmp_name"]) === false) {
            $_SESSION['upload_error'] = "Hiba: A feltöltött fájl nem kép.";
            header("Location: index.php");
            exit();
        }

        $new_filename = uniqid('banner_') . '.' . $imageFileType;
        $final_target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES["banner_image"]["tmp_name"], $final_target_file)) {
            try {
                $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('banner_image', :path) ON DUPLICATE KEY UPDATE setting_value = :path");
                $stmt->bindParam(':path', $final_target_file);
                $stmt->execute();
            } catch (PDOException $e) {
                $_SESSION['upload_error'] = "Hiba az adatbázis frissítése során.";
                header("Location: index.php");
                exit();
            }
        } else {
            $_SESSION['upload_error'] = "Kritikus hiba: A fájl mozgatása sikertelen. Ellenőrizd az 'uploads' mappa írási jogosultságait!";
            header("Location: index.php");
            exit();
        }
    }

    if (!isset($_SESSION['upload_error'])) {
        $_SESSION['upload_success'] = "Banner sikeresen frissítve!";
    }
}

header("Location: index.php");
exit();
?>