<?php

// db_config.php
$servername = "localhost";
$dbusername = "root";       // Cseréld le a valós adatbázis felhasználónevedre!
$dbpassword = "";           // Cseréld le a valós jelszavadra!
$dbname = "user_auth_db";

// Kapcsolat létrehozása PDO-val (biztonságosabb)
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $dbusername, $dbpassword);
    // PDO hiba mód beállítása kivételre (Exceptions)
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Kapcsolódás sikeres"; // Ezt éles környezetben töröld!
} catch (PDOException $e) {
    die("Kapcsolódási hiba: " . $e->getMessage());
}

// A weboldal gyökerének URL-je. MÓDOSÍTSD, HA SZÜKSÉGES!
define('BASE_URL', '/recept_app');
?>