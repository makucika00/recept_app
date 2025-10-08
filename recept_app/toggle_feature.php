<?php
// toggle_feature.php
session_start();
require_once 'db_config.php';

// A válasz JSON formátumú lesz
header('Content-Type: application/json');

// 1. Jogosultság ellenőrzése
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Nincs jogosultságod.']);
    exit();
}

// 2. Bemeneti adatok ellenőrzése
if (!isset($_POST['post_id']) || !is_numeric($_POST['post_id'])) {
    echo json_encode(['success' => false, 'message' => 'Érvénytelen bejegyzés azonosító.']);
    exit();
}

$post_id = $_POST['post_id'];
$new_status = 0;

try {
    // 3. Jelenlegi állapot lekérdezése
    $stmt = $conn->prepare("SELECT is_featured FROM recipes WHERE id = :id");
    $stmt->bindParam(':id', $post_id);
    $stmt->execute();
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($post) {
        // 4. Az állapot megfordítása (ha 1 volt, 0 lesz, ha 0 volt, 1 lesz)
        $new_status = $post['is_featured'] == 1 ? 0 : 1;

        // 5. Adatbázis frissítése az új állapottal
        $update_stmt = $conn->prepare("UPDATE recipes SET is_featured = :status WHERE id = :id");
        $update_stmt->bindParam(':status', $new_status, PDO::PARAM_INT);
        $update_stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
        $update_stmt->execute();

        // 6. Sikeres válasz küldése a JavaScriptnek
        echo json_encode(['success' => true, 'new_status' => $new_status]);
    } else {
        echo json_encode(['success' => false, 'message' => 'A bejegyzés nem található.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Adatbázis hiba.']);
}
?>