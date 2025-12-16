<?php
// submit_rating.php
session_start();
require_once 'db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Az értékeléshez be kell jelentkezned!']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$recipe_id = $data['recipe_id'] ?? null;
$rating = $data['rating'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$recipe_id || !$rating || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Érvénytelen adatok!']);
    exit;
}

$conn->beginTransaction();

try {
    // 1. Felhasználó értékelésének beszúrása vagy frissítése
    $sql = "INSERT INTO recipe_ratings (recipe_id, user_id, rating) 
            VALUES (:recipe_id, :user_id, :rating)
            ON DUPLICATE KEY UPDATE rating = :rating";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':recipe_id' => $recipe_id,
        ':user_id' => $user_id,
        ':rating' => $rating
    ]);

    // 2. Új átlag és értékelésszám kiszámítása
    $sql_avg = "SELECT AVG(rating) as avg_rating, COUNT(rating) as rating_count 
                FROM recipe_ratings 
                WHERE recipe_id = :recipe_id";
    $stmt_avg = $conn->prepare($sql_avg);
    $stmt_avg->execute([':recipe_id' => $recipe_id]);
    $new_stats = $stmt_avg->fetch(PDO::FETCH_ASSOC);

    // 3. A 'recipes' tábla frissítése az új statisztikákkal
    $sql_update = "UPDATE recipes 
                   SET avg_rating = :avg_rating, rating_count = :rating_count 
                   WHERE id = :recipe_id";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->execute([
        ':avg_rating' => $new_stats['avg_rating'],
        ':rating_count' => $new_stats['rating_count'],
        ':recipe_id' => $recipe_id
    ]);

    $conn->commit();
    
    // Visszaküldjük a friss adatokat a kliensnek
    echo json_encode([
        'success' => true,
        'new_avg_rating' => round($new_stats['avg_rating'], 2),
        'new_rating_count' => $new_stats['rating_count']
    ]);

} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Adatbázis hiba: ' . $e->getMessage()]);
}
?>