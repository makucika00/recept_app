<?php
// user_management.php
session_start();
require_once 'db_config.php';

// Jogosultság ellenőrzése
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

// Összes felhasználó lekérése (az aktuális admin kivételével)
try {
    $stmt = $conn->prepare("SELECT id, username, email, created_at FROM users WHERE id != :admin_id ORDER BY username ASC");
    $stmt->bindParam(':admin_id', $_SESSION['user_id']);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
}

require_once './header.php';
?>

<div class="main-content-area">
    <h1>Felhasználók Kezelése</h1>
    <p>Az oldalon regisztrált felhasználók listája.</p>

    <div class="user-cards-container">
        <?php if (empty($users)): ?>
            <p>Nincsenek más regisztrált felhasználók.</p>
        <?php else: ?>
            <?php foreach ($users as $user): ?>
                <div class="user-card">
                    <div class="user-info">
                        <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><strong>Regisztrált:</strong> <?php echo date('Y. m. d.', strtotime($user['created_at'])); ?></p>
                    </div>
                    <div class="user-posts">
                        <h4>Általa létrehozott bejegyzések:</h4>
                        <?php
                        // Felhasználóhoz tartozó bejegyzések lekérése
                        $post_stmt = $conn->prepare("SELECT id, title FROM posts WHERE author_id = :author_id ORDER BY created_at DESC");
                        $post_stmt->bindParam(':author_id', $user['id']);
                        $post_stmt->execute();
                        $posts = $post_stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <?php if (empty($posts)): ?>
                            <p><em>Nincsenek bejegyzései.</em></p>
                        <?php else: ?>
                            <ul>
                                <?php foreach ($posts as $post): ?>
                                    <li>
                                        <a href="post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a>
                                        <button class="action-btn feature-toggle-btn small <?php if ($post['is_featured']) echo 'featured'; ?>" 
                                                data-id="<?php echo $post['id']; ?>" title="Kiemelés/Visszavonás">
                                            <i class="fas fa-star"></i>
                                        </button>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                    <div class="user-card-actions">
                        <button class="action-btn send-email-btn" data-email="<?php echo htmlspecialchars($user['email']); ?>" title="Email küldése"><i class="fas fa-envelope"></i></button>
                        <button class="action-btn delete-user-btn danger-btn" data-id="<?php echo $user['id']; ?>" data-username="<?php echo htmlspecialchars($user['username']); ?>" title="Felhasználó törlése"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
require_once './footer.php';
?>