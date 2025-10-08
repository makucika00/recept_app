<?php
// login.php
require_once 'db_config.php';
session_start();

$message = '';
$login_user = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_user = trim($_POST['login_user']);
    $password = $_POST['login_password'];

    if (empty($login_user) || empty($password)) {
        $message = "Kérjük, töltse ki mindkét mezőt.";
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
                    } catch (PDOException $e) {
                        
                    }

                    header("Location: index.php");
                    exit();
                } else {
                    $message = "Érvénytelen jelszó.";
                }
            } else {
                $message = "A felhasználó nem található.";
            }
        } catch (PDOException $e) {
            $message = "Hiba a bejelentkezés során: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
    <head>
        <meta charset="UTF-8">
        <title>Bejelentkezés</title>
        <style>
            body {
                font-family: sans-serif;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                height: 100vh;
                background-color: #f4f4f4;
            }
            .form-container {
                background: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }
            .message {
                color: <?php echo (strpos($message, 'Sikeres') !== false) ? 'green' : 'red'; ?>;
                margin-bottom: 15px;
                font-weight: bold;
            }
            input[type="text"], input[type="password"] {
                width: 100%;
                padding: 10px;
                margin: 8px 0;
                border: 1px solid #ccc;
                border-radius: 4px;
                box-sizing: border-box;
            }
            button {
                background-color: #008CBA;
                color: white;
                padding: 14px 20px;
                margin: 8px 0;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                width: 100%;
            }
        </style>
    </head>
    <body>

        <div class="form-container">
            <h2>Bejelentkezés</h2>
            <?php
            if ($message) {
                echo '<p class="message">' . htmlspecialchars($message) . '</p>';
            }
            ?>

            <form action="login.php" method="POST">

                <label for="login_user">Felhasználónév vagy E-mail:</label>
                <input type="text" id="login_user" name="login_user" required value="<?php echo isset($login_user) ? htmlspecialchars($login_user) : ''; ?>">

                <label for="login_password">Jelszó:</label>
                <input type="password" id="login_password" name="login_password" required>

                <button type="submit">Bejelentkezés</button>
                <p>Még nincs fiókja? <a href="register.php">Regisztráljon itt.</a></p>
            </form>
        </div>

    </body>
</html>