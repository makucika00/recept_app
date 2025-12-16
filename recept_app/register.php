<?php
// register.php
require_once 'db_config.php';
session_start();

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $display_name = trim($_POST['display_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($display_name) || empty($username) || empty($email) || empty($password)) {
        $error_message = 'Minden mező kitöltése kötelező!';
    } elseif ($password !== $confirm_password) {
        $error_message = 'A két jelszó nem egyezik!';
    } else {
        try {
            // Ellenőrizzük, hogy a felhasználónév vagy email foglalt-e
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
            $stmt->execute([':username' => $username, ':email' => $email]);
            if ($stmt->fetch()) {
                $error_message = 'A felhasználónév vagy az email cím már foglalt!';
            } else {
                // Jelszó hashelése és felhasználó beszúrása
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // JAVÍTÁS ITT: 'password' helyett 'password_hash'
                $stmt = $conn->prepare("INSERT INTO users (display_name, username, email, password_hash) VALUES (:display_name, :username, :email, :password_hash)");
                $stmt->execute([
                    ':display_name' => $display_name,
                    ':username' => $username,
                    ':email' => $email,
                    ':password_hash' => $hashed_password // JAVÍTÁS ITT is
                ]);
                $success_message = 'Sikeres regisztráció! Most már bejelentkezhetsz.';
            }
        } catch (PDOException $e) {
            $error_message = 'Adatbázis hiba: ' . $e->getMessage();
        }
    }
}
require_once 'header.php';
?>

<div class="main-content-area" style="max-width: 500px;">
    <div class="single-post">
        <h1 style="text-align: center; margin-bottom: 20px;">Regisztráció</h1>

        <?php if ($error_message): ?>
            <p style="color: red; text-align: center;"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <p style="color: green; text-align: center;"><?php echo $success_message; ?></p>
            <div style="text-align: center; margin-top: 20px;">
                <a href="login.php" class="filter-btn">Bejelentkezés</a>
            </div>
        <?php else: ?>
            <form action="register.php" method="POST">
                <div class="form-group">
                    <label for="display_name">Név:</label>
                    <input type="text" id="display_name" name="display_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="username">Felhasználónév:</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="email">Email cím:</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Jelszó:</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Jelszó megerősítése:</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="filter-btn" style="width: 100%;">Regisztráció</button>
            </form>
            <p style="text-align:center; margin-top: 15px;">Már van fiókod? <a href="login.php">Jelentkezz be!</a></p>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>