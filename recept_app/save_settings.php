<?php
// save_settings.php (JAVÍTOTT VERZIÓ)
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die("Nincs jogosultságod a művelethez.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value) ON DUPLICATE KEY UPDATE setting_value = :value");

    // --- MENÜ MENTÉSE ---
    // Csak akkor fut le, ha a menü szerkesztő űrlapról érkezik adat.
    if (isset($_POST['menu_text']) && isset($_POST['menu_href'])) {
        $menu_items = [];
        for ($i = 0; $i < count($_POST['menu_text']); $i++) {
            if (!empty($_POST['menu_text'][$i])) {
                $menu_items[] = [
                    'text' => $_POST['menu_text'][$i],
                    'href' => $_POST['menu_href'][$i]
                ];
            }
        }
        $key = 'navbar_menu';
        $value = json_encode($menu_items);
        $stmt->bindParam(':key', $key);
        $stmt->bindParam(':value', $value);
        $stmt->execute();
    }
    
    // --- FOOTER MENTÉSE ---
    // Csak akkor fut le, ha a footer szerkesztő űrlapról érkezik adat.
    if (isset($_POST['footer_address'])) {
        $footer_keys = ['footer_address', 'footer_phone', 'footer_email'];
        foreach ($footer_keys as $key) {
            if (isset($_POST[$key])) {
                $value = $_POST[$key];
                $stmt->bindParam(':key', $key);
                $stmt->bindParam(':value', $value);
                $stmt->execute();
            }
        }
    }

    // --- SZÍNEK MENTÉSE ---
    // Csak akkor fut le, ha a színválasztó űrlapról érkezik adat.
    if (isset($_POST['theme_color_primary'])) {
        $color_keys = ['theme_color_primary', 'theme_color_accent'];
         foreach ($color_keys as $key) {
            if (isset($_POST[$key])) {
                $value = $_POST[$key];
                $stmt->bindParam(':key', $key);
                $stmt->bindParam(':value', $value);
                $stmt->execute();
            }
        }
    }
}

// Visszairányítás arra az oldalra, ahonnan a kérés jött
$redirect_url = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header("Location: " . $redirect_url);
exit();
?>