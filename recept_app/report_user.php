<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    die("A funkcióhoz be kell jelentkezned.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reporter_user_id = $_SESSION['user_id'];
    $reported_user_id = $_POST['reported_user_id'];
    $reason = trim($_POST['reason']);

    if (!empty($reported_user_id) && !empty($reason)) {
        try {
            $stmt = $conn->prepare("INSERT INTO reports (reporter_user_id, reported_user_id, reason) VALUES (:reporter, :reported, :reason)");
            $stmt->execute([':reporter' => $reporter_user_id, ':reported' => $reported_user_id, ':reason' => $reason]);
        } catch (PDOException $e) {
            // Hiba kezelése
        }
    }
}
header("Location: profile.php?id=" . $reported_user_id);
exit();
?>