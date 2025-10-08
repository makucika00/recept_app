 <?php
// dynamic_styles.php
header("Content-type: text/css"); // Kritikus: Megmondja a böngészőnek, hogy ez egy CSS fájl
require_once 'db_config.php';

$settings = [];
try {
    $stmt = $conn->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'theme_color_%'");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) { /* Hiba */ }

$primary_color = $settings['theme_color_primary'] ?? '#5d8c61';
$accent_color = $settings['theme_color_accent'] ?? '#4db057';
?>

/* Dinamikusan generált színek */
:root {
    --primary-color: <?php echo $primary_color; ?>;
    --accent-color: <?php echo $accent_color; ?>;
}

/* Itt felülírjuk azokat a stílusokat, amik ezeket a színeket használják */
.navbar {
    background-color: var(--primary-color);
}
.search-container.active .search-btn {
    color: var(--primary-color);
}
.read-more-link {
    color: var(--primary-color);
}
.kiemelt, button, .btn:hover, .add-btn, .filter-btn {
    background-color: var(--accent-color);
}
.btn {
    color: var(--accent-color);
}
.post-author i {
    color: var(--primary-color);
}