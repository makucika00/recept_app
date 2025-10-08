<?php
// create_post.php
session_start();
require_once 'db_config.php';

// Régi: if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1)
// ÚJ: Ellenőrizzük, hogy be van-e lépve a felhasználó
if (!isset($_SESSION['user_id'])) {
    die("A bejegyzés létrehozásához be kell jelentkezned.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $author_id = $_SESSION['user_id'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    if (!empty($title) && !empty($content)) {
        try {
            $stmt = $conn->prepare("INSERT INTO posts (author_id, title, content, is_featured) VALUES (:author_id, :title, :content, :is_featured)");
            $stmt->bindParam(':author_id', $author_id);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':is_featured', $is_featured);
            $stmt->execute();
        } catch (PDOException $e) {
            die("Hiba a mentés során: " . $e->getMessage());
        }
    }
}

// Átirányítás vissza a főoldalra
header("Location: index.php");
exit();
?>