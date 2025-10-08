<?php
// edit_post.php
session_start();
require_once 'db_config.php';

// Ellenőrizzük, hogy a felhasználó be van-e jelentkezve
if (!isset($_SESSION['user_id'])) {
    die("Nincs jogosultságod a művelethez.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $post_id = $_POST['post_id'];
    $title = trim($_POST['title']);
    $content = $_POST['content'];

    if (!empty($post_id) && !empty($title)) {
        try {
            // 1. LEKÉRDEZZÜK A BEJEGYZÉS SZERZŐJÉT
            $stmt = $conn->prepare("SELECT author_id FROM posts WHERE id = :id");
            $stmt->bindParam(':id', $post_id);
            $stmt->execute();
            $post = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2. ELLENŐRIZZÜK A JOGOSULTSÁGOT
            if ($post && (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1 || $_SESSION['user_id'] == $post['author_id'])) {
                
                // 3. HA VAN JOGOSULTSÁG, VÉGREHAJTJUK A MÓDOSÍTÁST
                $update_stmt = $conn->prepare("UPDATE posts SET title = :title, content = :content WHERE id = :id");
                $update_stmt->bindParam(':title', $title);
                $update_stmt->bindParam(':content', $content);
                $update_stmt->bindParam(':id', $post_id);
                $update_stmt->execute();

            } else {
                die("Nincs jogosultságod a bejegyzés szerkesztéséhez.");
            }
        } catch (PDOException $e) {
            // Itt a javított sor
            die("Hiba a frissítés során: " . $e->getMessage());
        }
    }
}

// Átirányítás vissza a bejegyzés oldalára
header("Location: post.php?id=" . $post_id);
exit();
?>