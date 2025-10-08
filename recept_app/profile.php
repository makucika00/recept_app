<?php
// profile.php (Végleges, Javított Verzió)
session_start();
require_once 'db_config.php';

// === 1. LÉPÉS: LÁTOGATÓ AZONOSÍTÁSA ÉS JOGOSULTSÁG ELLENŐRZÉS ===
if (!isset($_SESSION['user_id'])) {
    require_once './header.php';
    echo '<div class="main-content-area"><div class="access-denied-box"><h1>Hozzáférés Megtagadva</h1><p>A profilok megtekintése csak regisztrált és bejelentkezett felhasználók számára lehetséges.</p><div class="access-denied-actions"><a href="login.php" class="filter-btn">Bejelentkezés</a><a href="register.php" class="filter-btn" style="background-color: #6c757d;">Regisztráció</a></div></div></div>';
    require_once './footer.php';
    exit();
}

// --- 2. PROFIL ADATOK MEGHATÁROZÁSA ---
$logged_in_user_id = $_SESSION['user_id'];
$profile_user_id = $logged_in_user_id;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $profile_user_id = $_GET['id'];
}

$is_own_profile = ($logged_in_user_id == $profile_user_id);
$is_admin = (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1);

// --- 3. ADATOK LEKÉRÉSE AZ ADATBÁZISBÓL ---
$user = null;
$posts = [];
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $profile_user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $post_stmt = $conn->prepare("SELECT * FROM posts WHERE author_id = :author_id ORDER BY created_at DESC");
        $post_stmt->bindParam(':author_id', $profile_user_id, PDO::PARAM_INT);
        $post_stmt->execute();
        $posts = $post_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Hiba az adatok lekérése során: " . $e->getMessage());
}

if (!$user) {
    require_once './header.php';
    echo '<div class="main-content-area"><h1>Hiba</h1><p>A keresett felhasználó nem található.</p></div>';
    require_once './footer.php';
    exit();
}

// --- 4. KÖVETÉSI ÁLLAPOT ELLENŐRZÉSE ---
$is_following = false;
if (!$is_own_profile) {
    $follow_stmt = $conn->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = :follower_id AND following_id = :following_id");
    $follow_stmt->execute([':follower_id' => $logged_in_user_id, ':following_id' => $profile_user_id]);
    if ($follow_stmt->fetchColumn() > 0) {
        $is_following = true;
    }
}

require_once './header.php';
?>

<div class="main-content-area">
    <div class="profile-header" style="background-color: <?php echo htmlspecialchars($user['profile_header_color'] ?? '#5d8c61'); ?>;">
        <div class="profile-picture-container <?php if ($is_own_profile) echo 'editable'; ?>">
            <img src="<?php echo htmlspecialchars($user['profile_image'] ?? 'images/default_profile.png'); ?>" alt="Profilkép" <?php if ($is_own_profile) echo 'id="profile-image-trigger"'; ?> title="Profilkép cseréje">
            <?php if ($is_own_profile): ?>
                <div class="profile-picture-overlay"><i class="fas fa-camera"></i></div>
            <?php endif; ?>
        </div>
        <div class="profile-info">
            <h1><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></h1>
            <p>@<?php echo htmlspecialchars($user['username']); ?></p>
        </div>
        
        <div class="profile-actions">
            <?php if (!$is_own_profile): ?>
                <button class="action-btn send-email-btn" data-email="<?php echo htmlspecialchars($user['email']); ?>"><i class="fas fa-envelope"></i> Email</button>
                <button id="follow-toggle-btn" class="action-btn <?php echo $is_following ? 'following' : ''; ?>" data-id="<?php echo $user['id']; ?>">
                    <span class="follow-text"><?php echo $is_following ? 'Követed' : 'Követés'; ?></span>
                    <span class="unfollow-text">Követés megszüntetése</span>
                </button>
                <button class="action-btn report-user-btn" data-id="<?php echo $user['id']; ?>"><i class="fas fa-flag"></i> Jelentés</button>
            <?php endif; ?>
            <?php if ($is_admin && !$is_own_profile): ?>
                 <button class="action-btn delete-user-btn danger-btn" data-id="<?php echo $user['id']; ?>" data-username="<?php echo htmlspecialchars($user['username']); ?>"><i class="fas fa-trash"></i> Törlés</button>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($is_admin): ?>
        <div class="admin-stats-panel">
            <h3>Adminisztrátori Információk</h3>
            <ul>
                <li><strong>Regisztráció:</strong> <?php echo date('Y. m. d. H:i', strtotime($user['created_at'])); ?></li>
                <li><strong>Utolsó bejelentkezés:</strong> <?php echo $user['last_login'] ? date('Y. m. d. H:i', strtotime($user['last_login'])) : 'Soha'; ?></li>
                <li><strong>Bejegyzések száma:</strong> <?php echo count($posts); ?></li>
            </ul>
        </div>
    <?php endif; ?>

    <div class="profile-body">
        <div class="profile-posts-section">
            <h2><?php echo $is_own_profile ? 'Bejegyzéseim' : htmlspecialchars($user['username']) . ' bejegyzései'; ?></h2>
            <div class="posts-container">
                <?php if (empty($posts)): ?>
                    <p><?php echo $is_own_profile ? 'Még nincsenek bejegyzéseid.' : 'Ennek a felhasználónak még nincsenek bejegyzései.'; ?></p>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="post-entry <?php if($post['is_featured']) echo 'kiemelt'; ?>">
                             <div class="admin-actions">
                                <?php if ($is_admin): ?>
                                    <button class="action-btn feature-toggle-btn <?php if($post['is_featured']) echo 'featured'; ?>" data-id="<?php echo $post['id']; ?>" title="Kiemelés/Visszavonás"><i class="fas fa-star"></i></button>
                                <?php endif; ?>
                                <?php if ($is_own_profile || $is_admin): ?>
                                    <button class="action-btn edit-btn" data-id="<?php echo $post['id']; ?>" data-title="<?php echo htmlspecialchars($post['title']); ?>" data-content="<?php echo htmlspecialchars($post['content']); ?>" title="Szerkesztés"><i class="fas fa-pencil-alt"></i></button>
                                    <button class="action-btn delete-btn" data-id="<?php echo $post['id']; ?>" title="Törlés"><i class="fas fa-times"></i></button>
                                <?php endif; ?>
                            </div>
                            <h3><a href="post.php?id=<?php echo $post['id']; ?>" class="post-title-link"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                            <small>Publikálva: <?php echo date('Y. m. d.', strtotime($post['created_at'])); ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($is_own_profile): ?>
            <div class="profile-edit-section">
                <h2>Adataim szerkesztése</h2>
                <form action="update_profile.php" method="POST">
                    <div class="form-group"><label for="full_name">Teljes név:</label><input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>"></div>
                    <div class="form-group"><label for="email">Email cím:</label><input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required></div>
                    <div class="form-group"><label for="birth_date">Születési dátum:</label><input type="date" id="birth_date" name="birth_date" value="<?php echo htmlspecialchars($user['birth_date'] ?? ''); ?>"></div>
                    <div class="form-group"><label for="bio">Rólam (Bio):</label><textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea></div>
                    <div class="form-group"><label for="profile_header_color">Fejléc színe:</label><input type="color" id="profile_header_color" name="profile_header_color" value="<?php echo htmlspecialchars($user['profile_header_color'] ?? '#5d8c61'); ?>"></div>
                    <hr>
                    <h4>Jelszó módosítása (hagyd üresen, ha nem akarod megváltoztatni)</h4>
                    <div class="form-group"><label for="new_password">Új jelszó:</label><input type="password" id="new_password" name="new_password"></div>
                    <div class="form-group"><label for="confirm_password">Új jelszó megerősítése:</label><input type="password" id="confirm_password" name="confirm_password"></div>
                    <button type="submit" class="filter-btn">Adatok mentése</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once './footer.php';
?>