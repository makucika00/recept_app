<?php
// update_profile.php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // Adatok fogadása
    $display_name = trim($_POST['display_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $bio = trim($_POST['bio']);
    $profile_header_color = $_POST['profile_header_color'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($display_name) || empty($username) || empty($email)) {
        $_SESSION['profile_update_error'] = 'A Név, Felhasználónév és Email mezők kitöltése kötelező!';
        header('Location: profile.php');
        exit;
    }

    try {
        $sql = "UPDATE users SET display_name = :display_name, username = :username, email = :email, bio = :bio, profile_header_color = :color";
        $params = [
            ':display_name' => $display_name,
            ':username' => $username,
            ':email' => $email,
            ':bio' => $bio,
            ':color' => $profile_header_color,
        ];

        if (!empty($new_password)) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql .= ", password = :password";
                $params[':password'] = $hashed_password;
            } else {
                $_SESSION['profile_update_error'] = 'A megadott új jelszavak nem egyeznek!';
                header('Location: profile.php');
                exit;
            }
        }

        $sql .= " WHERE id = :id";
        $params[':id'] = $user_id;

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        $_SESSION['profile_update_success'] = 'Profil sikeresen frissítve!';
        
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
             $_SESSION['profile_update_error'] = 'A megadott felhasználónév vagy email cím már foglalt!';
        } else {
             $_SESSION['profile_update_error'] = 'Adatbázis hiba: ' . $e->getMessage();
        }
    }

    header('Location: profile.php');
    exit;
}
?>