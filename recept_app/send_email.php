<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die("Nincs jogosultságod.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $to = filter_var($_POST['email_to'], FILTER_SANITIZE_EMAIL);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);
    
    // Fontos: Az admin email címe a feladó
    $admin_email = 'admin@sajatoldalad.hu'; // Cseréld le a saját email címedre!
    $headers = "From: " . $admin_email . "\r\n" .
               "Reply-To: " . $admin_email . "\r\n" .
               "Content-Type: text/plain; charset=UTF-8\r\n" .
               'X-Mailer: PHP/' . phpversion();

    if (mail($to, $subject, $message, $headers)) {
        // Sikeres küldés, ide tehetsz session üzenetet, ha szeretnél
    } else {
        // Sikertelen küldés, ide tehetsz hibaüzenetet
    }
}

header("Location: user_management.php");
exit();
?>