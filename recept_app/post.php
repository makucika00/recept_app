<?php
// post.php (Modernizált)
session_start();
require_once 'db_config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$post_id = $_GET['id'];
$post = null;

try {
    // Lekérdezzük a bejegyzést a szerző nevével együtt
    $sql = "SELECT p.*, u.username 
            FROM posts p 
            LEFT JOIN users u ON p.author_id = u.id 
            WHERE p.id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
    $stmt->execute();
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    
}

if (!$post) {
    require_once './header.php';
    echo '<div class="main-content-area"><h1>Hiba</h1><p>A keresett bejegyzés nem található.</p></div>';
    require_once './footer.php';
    exit();
}

// Jogosultság ellenőrzése
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
$is_author = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['author_id'];
$can_edit = $is_admin || $is_author;

// Logó útvonalának lekérése az alapértelmezett képhez
$logo_image_path = 'images/default_logo.png';
try {
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'logo_image'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result && !empty($result['setting_value'])) {
        $logo_image_path = htmlspecialchars($result['setting_value']);
    }
} catch (PDOException $e) {
    
}

require_once './header.php';
?>

<div class="main-content-area">
    <article class="single-post <?php if ($post['is_featured']) echo 'kiemelt-post'; ?>">

        <header class="single-post-header">
            <h1><?php echo htmlspecialchars($post['title']); ?></h1>
            <div class="post-meta">
                <span class="author-info">
                    <i class="fas fa-user"></i>
                    <a href="profile.php?id=<?php echo $post['author_id']; ?>">
<?php echo htmlspecialchars($post['username']); ?>
                    </a>
                </span>
                <span class="date-info">
                    <i class="fas fa-calendar-alt"></i> <?php echo date('Y. F j.', strtotime($post['created_at'])); ?>
                </span>
            </div>
                <?php if ($can_edit): ?>
                <div class="admin-actions-single">
    <?php if ($is_admin): ?>
                        <button class="action-btn feature-toggle-btn <?php if ($post['is_featured']) echo 'featured'; ?>" data-id="<?php echo $post['id']; ?>" title="Kiemelés/Visszavonás"><i class="fas fa-star"></i></button>
                <?php endif; ?>
                    <button id="editPostBtn" class="action-btn" title="Bejegyzés szerkesztése"><i class="fas fa-pencil-alt"></i></button>
                    <button class="action-btn delete-btn" data-id="<?php echo $post['id']; ?>" title="Bejegyzés törlése"><i class="fas fa-times"></i></button>
                </div>
            <?php endif; ?>
        </header>

        <div id="post-view">
<?php
$cover_path = !empty($post['cover_image']) ? htmlspecialchars($post['cover_image']) : null;
if ($cover_path):
    ?>
                <div class="single-post-feature-image">
                    <img src="<?php echo $cover_path; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                </div>
<?php endif; ?>

            <div class="post-content">
        <?php echo html_entity_decode($post['content']); ?>
            </div>
        </div>

<?php if ($can_edit): ?>
            <div id="post-edit" style="display:none;">
                <h2>Bejegyzés szerkesztése</h2>
                <form action="edit_post.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <div class="form-group"><label for="edit-title">Cím:</label><input type="text" name="title" id="edit-title" value="<?php echo htmlspecialchars($post['title']); ?>"></div>
                    <div class="form-group"><label for="edit-cover-image">Borítókép cseréje:</label><input type="file" name="cover_image" id="edit-cover-image"></div>
                    <div class="form-group"><label for="edit-content-textarea">Tartalom:</label><textarea name="content" id="edit-content-textarea"><?php echo htmlspecialchars($post['content']); ?></textarea></div>
                    <button type="submit" class="filter-btn">Mentés</button>
                    <button type="button" id="cancelEditBtn" class="cancel-btn">Mégse</button>
                </form>
            </div>
<?php endif; ?>
    </article>
</div>

<?php
require_once './footer.php';
?>