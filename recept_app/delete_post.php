<?php
// delete_post.php
session_start();
require_once 'db_config.php';

// Ellenőrizzük, hogy a felhasználó be van-e jelentkezve
if (!isset($_SESSION['user_id'])) {
    die("Nincs jogosultságod a művelethez.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_id'])) {
    $post_id = $_POST['post_id'];

    if (!empty($post_id)) {
        try {
            // 1. LEKÉRDEZZÜK A BEJEGYZÉS SZERZŐJÉT
            $stmt = $conn->prepare("SELECT author_id FROM posts WHERE id = :id");
            $stmt->bindParam(':id', $post_id);
            $stmt->execute();
            $post = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2. ELLENŐRIZZÜK A JOGOSULTSÁGOT
            if ($post && ($_SESSION['is_admin'] == 1 || $_SESSION['user_id'] == $post['author_id'])) {
                
                // 3. HA VAN JOGOSULTSÁG, TÖRLÜNK
                $delete_stmt = $conn->prepare("DELETE FROM posts WHERE id = :id");
                $delete_stmt->bindParam(':id', $post_id);
                $delete_stmt->execute();

            } else {
                die("Nincs jogosultságod a bejegyzés törléséhez.");
            }
        } catch (PDOException $e) {
            die("Hiba a törlés során: " . $e->getMessage());
        }
    }
}

// Átirányítás vissza a főoldalra
header("Location: index.php");
exit();
?>