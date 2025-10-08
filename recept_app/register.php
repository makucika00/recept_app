<?php
// register.php
require_once 'db_config.php'; // Adatbázis-kapcsolat betöltése

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // 1. Validáció
    if (empty($username) || empty($email) || empty($password) || empty($password_confirm)) {
        $message = "Minden mező kitöltése kötelező!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Érvénytelen e-mail cím formátum!";
    } elseif ($password !== $password_confirm) {
        $message = "A két jelszó nem egyezik!";
    } else {
        // 2. Jelszó Titkosítása (Hashelése)
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 3. Adatbázisba írás
        try {
            // Ellenőrizzük, hogy a felhasználónév vagy e-mail már foglalt-e
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $message = "A felhasználónév vagy e-mail cím már foglalt.";
            } else {
                // Beszúrás
                $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)");
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password_hash', $hashed_password);
                $stmt->execute();

                $message = "Sikeres regisztráció! Most már bejelentkezhet.";
                // Opcionális: Átirányítás a bejelentkezési oldalra
                header("Location: login.php");
                exit();
            }
        } catch (PDOException $e) {
            $message = "Hiba történt a regisztráció során: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
    <head>
        <meta charset="UTF-8">
        <title>Regisztráció</title>
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
            input[type="text"], input[type="email"], input[type="password"] {
                width: 100%;
                padding: 10px;
                margin: 8px 0;
                border: 1px solid #ccc;
                border-radius: 4px;
                box-sizing: border-box;
            }
            button {
                background-color: #4CAF50;
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
            <h2>Regisztráció</h2>
            <?php if ($message) {
                echo '<p class="message">' . htmlspecialchars($message) . '</p>';
            } ?>

            <form action="register.php" method="POST">

                <label for="username">Felhasználónév:</label>
                <input type="text" id="username" name="username" required value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">

                <label for="email">E-mail cím:</label>
                <input type="email" id="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">

                <label for="password">Jelszó:</label>
                <input type="password" id="password" name="password" required>

                <label for="password_confirm">Jelszó megerősítése:</label>
                <input type="password" id="password_confirm" name="password_confirm" required>

                <button type="submit">Regisztráció</button>
                <p>Már van fiókja? <a href="login.php">Jelentkezzen be itt.</a></p>
            </form>
        </div>

    </body>
</html>