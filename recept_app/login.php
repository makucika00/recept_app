<?php
// login.php
require_once 'db_config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_user = trim($_POST['login_user']);
    $password = $_POST['login_password'];

    if (empty($login_user) || empty($password)) {
        header("Location: index.php?login_error=empty");
        exit();
    } else {
        try {
            $stmt = $conn->prepare("SELECT id, username, password_hash, is_admin FROM users WHERE username = :login_user OR email = :login_user");
            $stmt->bindParam(':login_user', $login_user);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                if (password_verify($password, $user['password_hash'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['is_admin'] = $user['is_admin'];

                    try {
                        $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
                        $update_stmt->bindParam(':id', $user['id']);
                        $update_stmt->execute();
                    } catch (PDOException $e) {}

                    // Sikeres bejelentkezés után irány a főoldal
                    header("Location: index.php");
                    exit();
                } else {
                    // Rossz jelszó
                    header("Location: index.php?login_error=invalid");
                    exit();
                }
            } else {
                // Nincs ilyen felhasználó
                header("Location: index.php?login_error=not_found");
                exit();
            }
        } catch (PDOException $e) {
            header("Location: index.php?login_error=system");
            exit();
        }
    }
} else {
    // Ha valaki közvetlenül nyitja meg a login.php-t, irányítsuk át a főoldalra és nyissuk meg a modált
    header("Location: index.php?login_error=open");
    exit();
}
?>