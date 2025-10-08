<?php
// save_recipe.php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Adatok fogadása
    $title = trim($_POST['title'] ?? '');
    $prep_time = !empty($_POST['prep_time']) ? (int)$_POST['prep_time'] : null;
    $servings = !empty($_POST['servings']) ? (int)$_POST['servings'] : null;
    $author_id = $_SESSION['user_id'];

    $quantities = $_POST['quantity'] ?? [];
    $units = $_POST['unit'] ?? [];
    $ingredient_names = $_POST['ingredient_name'] ?? [];
    $instructions_array = $_POST['instructions'] ?? [];

    // Képfeltöltés (a logika változatlan)
    $cover_image_path = null;
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
        $target_dir = "uploads/";
        $file_extension = pathinfo($_FILES["cover_image"]["name"], PATHINFO_EXTENSION);
        $unique_filename = "post_img_" . uniqid() . "." . $file_extension;
        $target_file = $target_dir . $unique_filename;
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($file_extension), $allowed_types) && $_FILES["cover_image"]["size"] < 5000000) {
            if (move_uploaded_file($_FILES["cover_image"]["tmp_name"], $target_file)) {
                $cover_image_path = $target_file;
            }
        }
    }

    // Adatbázis tranzakció indítása
    $conn->beginTransaction();

    try {
        // 1. Alap recept adatainak beszúrása a 'recipes' táblába
        $sqlRecipe = "INSERT INTO recipes (title, author_id, cover_image, prep_time, servings) VALUES (:title, :author_id, :cover_image, :prep_time, :servings)";
        $stmtRecipe = $conn->prepare($sqlRecipe);
        $stmtRecipe->execute([
            ':title' => $title,
            ':author_id' => $author_id,
            ':cover_image' => $cover_image_path,
            ':prep_time' => $prep_time,
            ':servings' => $servings
        ]);
        // Az újonnan létrehozott recept ID-jának lekérdezése
        $recipe_id = $conn->lastInsertId();

        // 2. Hozzávalók beszúrása az 'ingredients' táblába
        $sqlIngredient = "INSERT INTO ingredients (recipe_id, quantity, unit, name) VALUES (:recipe_id, :quantity, :unit, :name)";
        $stmtIngredient = $conn->prepare($sqlIngredient);
        for ($i = 0; $i < count($ingredient_names); $i++) {
            if (!empty($ingredient_names[$i])) {
                $stmtIngredient->execute([
                    ':recipe_id' => $recipe_id,
                    ':quantity' => $quantities[$i],
                    ':unit' => $units[$i],
                    ':name' => $ingredient_names[$i]
                ]);
            }
        }

        // 3. Elkészítési lépések beszúrása az 'instructions' táblába
        $sqlInstruction = "INSERT INTO instructions (recipe_id, step_number, description) VALUES (:recipe_id, :step_number, :description)";
        $stmtInstruction = $conn->prepare($sqlInstruction);
        $step_number = 1;
        foreach ($instructions_array as $instruction) {
            if (!empty(trim($instruction))) {
                $stmtInstruction->execute([
                    ':recipe_id' => $recipe_id,
                    ':step_number' => $step_number,
                    ':description' => trim($instruction)
                ]);
                $step_number++;
            }
        }

        // Ha minden sikeres, a tranzakció véglegesítése
        $conn->commit();
        $_SESSION['upload_success'] = 'A recepted sikeresen közzétéve!';
        header('Location: index.php');
        exit;

    } catch (PDOException $e) {
        // Hiba esetén a tranzakció visszavonása, semmi sem mentődik
        $conn->rollBack();
        $_SESSION['upload_error'] = 'Adatbázis hiba történt a mentés során: ' . $e->getMessage();
        header('Location: create_recipe.php');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
?>