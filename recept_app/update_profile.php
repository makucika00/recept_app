<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    die("Nincs jogosultságod.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    
    $full_name = trim($_POST['full_name']);
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $birth_date = !empty($_POST['birth_date']) ? $_POST['birth_date'] : null;
    $bio = trim($_POST['bio']);
    $header_color = $_POST['profile_header_color'];

    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Jelszó ellenőrzése
    if (!empty($new_password) && $new_password !== $confirm_password) {
        $_SESSION['profile_message'] = ['type' => 'error', 'text' => 'A megadott új jelszavak nem egyeznek.'];
        header("Location: profile.php");
        exit();
    }

    try {
        // Alap UPDATE parancs felépítése
        $sql = "UPDATE users SET full_name = :full_name, email = :email, birth_date = :birth_date, bio = :bio, profile_header_color = :color";
        $params = [
            ':full_name' => $full_name,
            ':email' => $email,
            ':birth_date' => $birth_date,
            ':bio' => $bio,
            ':color' => $header_color,
            ':id' => $user_id
        ];

        // Jelszó hozzáadása a parancshoz, ha szükséges
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql .= ", password_hash = :password";
            $params[':password'] = $hashed_password;
        }

        $sql .= " WHERE id = :id";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        $_SESSION['profile_message'] = ['type' => 'success', 'text' => 'Az adataid sikeresen frissültek!'];
        
    } catch (PDOException $e) {
        // Ellenőrizzük, hogy a hiba a foglalt e-mail cím miatt van-e
        if ($e->errorInfo[1] == 1062) { // 1062 = Duplicate entry
            $_SESSION['profile_message'] = ['type' => 'error', 'text' => 'Ez az e-mail cím már foglalt. Kérlek, adj meg egy másikat.'];
        } else {
            $_SESSION['profile_message'] = ['type' => 'error', 'text' => 'Adatbázis hiba történt a mentés során.'];
        }
    }
}

header("Location: profile.php");
exit();
?>