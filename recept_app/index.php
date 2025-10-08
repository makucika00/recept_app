<?php
// index.php
session_start();

// Flash üzenetek kezelése
$upload_message = null;
$upload_message_type = null;
if (isset($_SESSION['upload_error'])) {
    $upload_message = $_SESSION['upload_error'];
    $upload_message_type = 'error';
    unset($_SESSION['upload_error']);
} elseif (isset($_SESSION['upload_success'])) {
    $upload_message = $_SESSION['upload_success'];
    $upload_message_type = 'success';
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

// Bejegyzések lekérése a szerző nevével együtt
try {
    $sql = "SELECT posts.*, users.username 
            FROM posts 
            LEFT JOIN users ON posts.author_id = users.id 
            ORDER BY posts.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $posts = [];
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
        <h2>Friss bejegyzések</h2>
<?php if (isset($_SESSION['user_id'])): ?>
            <button id="openNewPostModalBtn" class="action-btn add-btn" title="Új bejegyzés létrehozása">
                <i class="fas fa-plus"></i>
            </button>
        <?php endif; ?>
    </div>

    <div class="posts-container posts-grid-layout">
        <?php if (empty($posts)): ?>
            <p style="grid-column: 1 / -1;">Jelenleg nincsenek bejegyzések.</p>
        <?php else: ?>
            <?php
            $random_sizes = [2, 1, 3, 1, 2, 1];
            $i = 0;
            foreach ($posts as $post):
                $cover_path = !empty($post['cover_image']) ? htmlspecialchars($post['cover_image']) : $logo_image_path;
                $post_class = $post['is_featured'] ? 'post-card kiemelt' : 'post-card';
                $random_size = $random_sizes[$i % count($random_sizes)];
                $i++;
                ?>
                <div class="<?php echo $post_class; ?> card-size-<?php echo $random_size; ?>">

                    <div class="card-image-wrapper">
                        <a href="post.php?id=<?php echo $post['id']; ?>" class="card-image-link">
                            <div class="card-image" style="background-image: url('<?php echo $cover_path; ?>');"></div>
                        </a>
                    </div>

                    <div class="card-info">
                        <div class="card-admin-actions">
                            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                                <button class="action-btn feature-toggle-btn <?php if ($post['is_featured']) echo 'featured'; ?>" 
                                        data-id="<?php echo $post['id']; ?>" title="Kiemelés/Visszavonás">
                                    <i class="fas fa-star"></i>
                                </button>
        <?php endif; ?>
        <?php if (isset($_SESSION['user_id']) && (($_SESSION['is_admin'] ?? 0) == 1 || $_SESSION['user_id'] == $post['author_id'])): ?>
                                <button class="action-btn edit-btn" 
                                        data-id="<?php echo $post['id']; ?>" 
                                        data-title="<?php echo htmlspecialchars($post['title']); ?>"
                                        data-content="<?php echo htmlspecialchars($post['content']); ?>"
                                        title="Szerkesztés">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <button class="action-btn delete-btn" data-id="<?php echo $post['id']; ?>" title="Törlés">
                                    <i class="fas fa-times"></i>
                                </button>
                                <?php endif; ?>
                        </div>

                        <?php if (!empty($post['username'])): ?>
                            <div class="post-author">
                                <i class="fas fa-user"></i>
                                <a href="profile.php?id=<?php echo $post['author_id']; ?>">
                            <?php echo htmlspecialchars($post['username']); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <h3><a href="post.php?id=<?php echo $post['id']; ?>" class="post-title-link"><?php echo htmlspecialchars($post['title']); ?></a></h3>

        <?php
        $decoded_content = html_entity_decode($post['content']);
        $plain_content = strip_tags($decoded_content);
        $excerpt_length = 120;
        $excerpt = (mb_strlen($plain_content) > $excerpt_length) ? mb_substr($plain_content, 0, $excerpt_length) . '...' : $plain_content;
        ?>
                        <p><?php echo htmlspecialchars($excerpt); ?></p>

                        <a href="post.php?id=<?php echo $post['id']; ?>" class="read-more-link">Tovább olvasom →</a>

                        <small>Publikálva: <?php echo date('Y. m. d.', strtotime($post['created_at'])); ?></small>
                    </div>
                </div>
    <?php endforeach; ?>
<?php endif; ?>
    </div>
</div>

<?php
require_once './footer.php';
?>