<?php
// live_search.php
require_once 'db_config.php';

// A válasz típusa JSON lesz
header('Content-Type: application/json');

$results = [];
$query = $_GET['query'] ?? '';

// Csak akkor keresünk, ha a kifejezés legalább 2 karakter hosszú
if (strlen($query) > 1) {
    $search_term = '%' . $query . '%';
    
    try {
        // A LIMIT 5 biztosítja, hogy ne terheljük túl a rendszert és a felületet
        $stmt = $conn->prepare("SELECT id, title FROM posts WHERE title LIKE :query LIMIT 5");
        $stmt->bindParam(':query', $search_term);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Hiba esetén üres tömböt küldünk
    }
}

// A PHP tömböt JSON formátumú szöveggé alakítjuk és kiírjuk
echo json_encode($results);
?>