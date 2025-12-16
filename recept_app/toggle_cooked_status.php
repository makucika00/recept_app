<?php
// toggle_cooked_status.php
session_start();
require_once 'db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'A funkcióhoz be kell jelentkezned!']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$recipe_id = $data['recipe_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$recipe_id) {
    echo json_encode(['success' => false, 'message' => 'Hiányzó recept azonosító!']);
    exit;
}

try {
    // Ellenőrizzük, hogy a recept már el van-e mentve
    $sql_check = "SELECT * FROM user_cooked_recipes WHERE user_id = :user_id AND recipe_id = :recipe_id";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute([':user_id' => $user_id, ':recipe_id' => $recipe_id]);
    
    if ($stmt_check->fetch()) {
        // Ha igen, töröljük (visszavonás)
        $sql_delete = "DELETE FROM user_cooked_recipes WHERE user_id = :user_id AND recipe_id = :recipe_id";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->execute([':user_id' => $user_id, ':recipe_id' => $recipe_id]);
        echo json_encode(['success' => true, 'status' => 'removed']);
    } else {
        // Ha nem, hozzáadjuk (mentés)
        $sql_insert = "INSERT INTO user_cooked_recipes (user_id, recipe_id) VALUES (:user_id, :recipe_id)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->execute([':user_id' => $user_id, ':recipe_id' => $recipe_id]);
        echo json_encode(['success' => true, 'status' => 'added']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Adatbázis hiba: ' . $e->getMessage()]);
}
?>