<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die("Nincs jogosultságod.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    
    // A felhasználó nem törölheti saját magát
    if ($user_id == $_SESSION['user_id']) {
        header("Location: user_management.php");
        exit();
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
    } catch (PDOException $e) {
        // Hibaüzenet kezelése
    }
}

header("Location: user_management.php");
exit();
?>