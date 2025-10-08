<?php
// profile.php
session_start();
require_once 'db_config.php';

// Jogosultság ellenőrzés
if (!isset($_SESSION['user_id'])) {
    require_once './header.php';
    echo '<div class="main-content-area"><div class="access-denied-box"><h1>Hozzáférés Megtagadva</h1><p>A profilok megtekintése csak bejelentkezett felhasználóknak lehetséges.</p><div class="access-denied-actions"><a href="login.php" class="filter-btn">Bejelentkezés</a><a href="register.php" class="filter-btn" style="background-color: #6c757d;">Regisztráció</a></div></div></div>';
    require_once './footer.php';
    exit();
}

// Profil adatok meghatározása
$logged_in_user_id = $_SESSION['user_id'];
$profile_user_id = $_GET['id'] ?? $logged_in_user_id;
$is_own_profile = ($logged_in_user_id == $profile_user_id);
$is_admin = (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1);

// Adatok lekérése
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute([':id' => $profile_user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $post_stmt = $conn->prepare("SELECT * FROM recipes WHERE author_id = :author_id ORDER BY created_at DESC");
        $post_stmt->execute([':author_id' => $profile_user_id]);
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

require_once './header.php';
?>

<div class="main-content-area">
    <div class="profile-header" style="background-color: <?php echo htmlspecialchars($user['profile_header_color'] ?? '#5d8c61'); ?>;">
        <div class="profile-picture-container <?php if ($is_own_profile) echo 'editable'; ?>">
            <img src="<?php echo htmlspecialchars($user['profile_image'] ?? 'images/default_profile.png'); ?>" alt="Profilkép" <?php if ($is_own_profile) echo 'id="profile-image-trigger"'; ?>>
            <?php if ($is_own_profile): ?>
                <div class="profile-picture-overlay"><i class="fas fa-camera"></i></div>
            <?php endif; ?>
        </div>
        <div class="profile-info">
            <h1><?php echo htmlspecialchars($user['display_name'] ?? $user['username']); ?></h1>
            <p>@<?php echo htmlspecialchars($user['username']); ?></p>
        </div>
    </div>

    <div class="profile-body">
        <div class="profile-posts-section">
            <h2><?php echo $is_own_profile ? 'Receptjeim' : htmlspecialchars($user['display_name']) . ' receptjei'; ?></h2>
            <?php if (empty($posts)): ?>
                <p><?php echo $is_own_profile ? 'Még nincsenek receptjeid.' : 'Ennek a felhasználónak még nincsenek receptjei.'; ?></p>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post-entry">
                        <h3><a href="post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                        <small>Publikálva: <?php echo date('Y. m. d.', strtotime($post['created_at'])); ?></small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <h2 style="margin-top: 30px;">Elkészített Receptek</h2>
             <?php
            $sql_cooked = "SELECT r.* FROM recipes r JOIN user_cooked_recipes ucr ON r.id = ucr.recipe_id WHERE ucr.user_id = :user_id ORDER BY ucr.cooked_at DESC";
            $stmt_cooked = $conn->prepare($sql_cooked);
            $stmt_cooked->execute([':user_id' => $profile_user_id]);
            $cooked_recipes = $stmt_cooked->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <?php if (empty($cooked_recipes)): ?>
                <p>Még nem jelölt meg egy receptet sem elkészítettként.</p>
            <?php else: ?>
                <?php foreach ($cooked_recipes as $cooked_recipe): ?>
                    <div class="post-entry">
                        <h3><a href="post.php?id=<?php echo $cooked_recipe['id']; ?>"><?php echo htmlspecialchars($cooked_recipe['title']); ?></a></h3>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($is_own_profile): ?>
            <div class="profile-edit-section">
                <h2>Adataim szerkesztése</h2>
                <form action="update_profile.php" method="POST">
                    <div class="form-group">
                        <label for="display_name">Név:</label>
                        <input type="text" id="display_name" name="display_name" class="form-control" value="<?php echo htmlspecialchars($user['display_name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="username">Felhasználónév:</label>
                        <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email cím:</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="bio">Rólam (Bio):</label>
                        <textarea id="bio" name="bio" class="form-control" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="profile_header_color">Fejléc színe:</label>
                        <input type="color" id="profile_header_color" name="profile_header_color" class="form-control" value="<?php echo htmlspecialchars($user['profile_header_color'] ?? '#5d8c61'); ?>">
                    </div>
                    <hr>
                    <h4>Jelszó módosítása (hagyd üresen, ha nem akarod megváltoztatni)</h4>
                    <div class="form-group">
                        <label for="new_password">Új jelszó:</label>
                        <input type="password" id="new_password" name="new_password" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Új jelszó megerősítése:</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                    </div>
                    <button type="submit" class="filter-btn">Adatok mentése</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once './footer.php'; ?>