<?php
session_start();
require_once 'db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'A funkcióhoz be kell jelentkezned.']);
    exit();
}

if (!isset($_POST['user_id']) || !is_numeric($_POST['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Érvénytelen felhasználói azonosító.']);
    exit();
}

$follower_id = $_SESSION['user_id'];
$following_id = $_POST['user_id'];

try {
    // Ellenőrizzük, hogy már követi-e
    $stmt = $conn->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = :follower_id AND following_id = :following_id");
    $stmt->execute([':follower_id' => $follower_id, ':following_id' => $following_id]);
    $is_following = $stmt->fetchColumn() > 0;

    if ($is_following) {
        // Ha igen, akkor "unfollow"
        $delete_stmt = $conn->prepare("DELETE FROM follows WHERE follower_id = :follower_id AND following_id = :following_id");
        $delete_stmt->execute([':follower_id' => $follower_id, ':following_id' => $following_id]);
        echo json_encode(['success' => true, 'is_following' => false]);
    } else {
        // Ha nem, akkor "follow"
        $insert_stmt = $conn->prepare("INSERT INTO follows (follower_id, following_id) VALUES (:follower_id, :following_id)");
        $insert_stmt->execute([':follower_id' => $follower_id, ':following_id' => $following_id]);
        echo json_encode(['success' => true, 'is_following' => true]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Adatbázis hiba.']);
}
?>