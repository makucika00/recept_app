<?php
// index.php
session_start();

// Flash üzenetek kezelése
if (isset($_SESSION['upload_error'])) {
    // Itt kezelhetnéd a hibaüzenet megjelenítését
    unset($_SESSION['upload_error']);
} elseif (isset($_SESSION['upload_success'])) {
    // Itt kezelhetnéd a sikerüzenet megjelenítését
    unset($_SESSION['upload_success']);
}

require_once 'db_config.php';

// Banner kép, cím és logó lekérése
$banner_image_path = 'images/banner.jpg';
$banner_title = 'Üdvözöljük!';
$logo_image_path = 'images/default_logo.png';

try {
    $stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('banner_image', 'banner_title', 'logo_image')");
    $stmt->execute();
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    if (!empty($settings['banner_image'])) {
        $banner_image_path = htmlspecialchars($settings['banner_image']);
    }
    if (!empty($settings['banner_title'])) {
        $banner_title = htmlspecialchars($settings['banner_title']);
    }
    if (!empty($settings['logo_image'])) {
        $logo_image_path = htmlspecialchars($settings['logo_image']);
    }
} catch (PDOException $e) {
    error_log("Hiba a beállítások lekérésekor: " . $e->getMessage());
}

// Legfrissebb receptek lekérése
$user_id = $_SESSION['user_id'] ?? null;
try {
    $sql = "SELECT r.*, u.username, 
            (SELECT COUNT(*) FROM user_cooked_recipes ucr WHERE ucr.recipe_id = r.id AND ucr.user_id = :user_id) as is_cooked
            FROM recipes r
            LEFT JOIN users u ON r.author_id = u.id 
            ORDER BY r.created_at DESC
            LIMIT 6";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recipes = [];
}

require_once './header.php';
?>

<header class="banner" style="background-image: url('<?php echo $banner_image_path; ?>');">
    <div class="overlay"></div>
    <h1><?php echo $banner_title; ?></h1>
    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
        <button id="editBannerBtn" class="action-btn banner-edit-btn" 
                title="Banner szerkesztése" 
                data-title="<?php echo htmlspecialchars($banner_title); ?>">
            <i class="fas fa-pencil-alt"></i>
        </button>
    <?php endif; ?>
</header>

<div class="index-container">
    <div class="posts-header">
        <h2>Legújabb Receptek</h2>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="create_recipe.php" class="action-btn add-btn" title="Új recept létrehozása" style="text-decoration: none; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-plus"></i>
            </a>
        <?php endif; ?>
    </div>

    <div class="posts-container posts-grid-layout">
        <?php if (empty($recipes)): ?>
            <p style="grid-column: 1 / -1;">Jelenleg nincsenek receptek.</p>
        <?php else: ?>
            <?php
            $random_sizes = [2, 1, 3, 1, 2, 1];
            $i = 0;
            foreach ($recipes as $recipe):
                $cover_path = !empty($recipe['cover_image']) ? htmlspecialchars($recipe['cover_image']) : $logo_image_path;
                $post_class = $recipe['is_featured'] ? 'post-card kiemelt' : 'post-card';
                $random_size = $random_sizes[$i % count($random_sizes)];
                $i++;
                ?>
                <a href="post.php?id=<?php echo $recipe['id']; ?>" class="<?php echo $post_class; ?> card-size-<?php echo $random_size; ?> card-link">
                    <?php if ($recipe['is_cooked']): ?>
                        <div class="cooked-overlay">
                            <div class="cooked-icon-circle"><i class="fas fa-check"></i></div>
                        </div>
                    <?php endif; ?>
                    <div class="card-image-wrapper">
                        <div class="card-image" style="background-image: url('<?php echo $cover_path; ?>');"></div>
                    </div>
                    <div class="card-info">
                        <h3><?php echo htmlspecialchars($recipe['title']); ?></h3>
                        <div class="card-meta">
                            <div class="card-prep-time">
                                <i class="fas fa-clock"></i>
                                <span><?php echo htmlspecialchars($recipe['prep_time'] ?? 'N/A'); ?> perc</span>
                            </div>
                            <div class="card-rating">
                                <?php
                                $avg_rating = round($recipe['avg_rating']);
                                for ($i = 1; $i <= 5; $i++):
                                    ?>
                                    <i class="<?php echo ($i <= $avg_rating) ? 'fas fa-star' : 'far fa-star'; ?>"></i>
        <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
<?php endif; ?>
    </div>
</div>

<?php
require_once './footer.php';
?>