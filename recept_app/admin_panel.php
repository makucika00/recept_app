<?php
// admin_panel.php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

$settings = [];
try {
    $stmt = $conn->query("SELECT setting_key, setting_value FROM settings");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {}

require_once 'header.php';
?>

<div class="main-content-area">
    <h1>Beállítások</h1>
    <p>Itt tudod módosítani az oldal globális beállításait, és kezelni a felhasználókat.</p>

    <hr style="margin: 30px 0;">

    <h2>Felhasználók Kezelése</h2>
    <a href="user_management.php" class="filter-btn" style="display: inline-block; margin-top: 10px;">Felhasználók listájának megnyitása</a>

    <hr style="margin: 30px 0;">

    <form action="save_settings.php" method="POST" class="settings-form">
        <h4>Színek Szerkesztése</h4>
        <div class="form-group-inline">
            <label for="theme_color_primary">Fő szín (navbar, linkek):</label>
            <input type="color" id="theme_color_primary" name="theme_color_primary" value="<?php echo htmlspecialchars($settings['theme_color_primary'] ?? '#5d8c61'); ?>">
        </div>
        <div class="form-group-inline">
            <label for="theme_color_accent">Kiemelt szín (gombok):</label>
            <input type="color" id="theme_color_accent" name="theme_color_accent" value="<?php echo htmlspecialchars($settings['theme_color_accent'] ?? '#4db057'); ?>">
        </div>
        
        <button type="submit" class="filter-btn" style="margin-top:20px;">Színek Mentése</button>
    </form>
</div>

<?php
require_once 'footer.php';
?>