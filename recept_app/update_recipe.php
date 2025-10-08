<?php
// update_recipe.php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Adatok fogadása
    $recipe_id = $_POST['recipe_id'];
    $title = trim($_POST['title'] ?? '');
    $prep_time = !empty($_POST['prep_time']) ? (int)$_POST['prep_time'] : null;
    $servings = !empty($_POST['servings']) ? (int)$_POST['servings'] : null;
    $user_id = $_SESSION['user_id'];

    $quantities = $_POST['quantity'] ?? [];
    $units = $_POST['unit'] ?? [];
    $ingredient_names = $_POST['ingredient_name'] ?? [];
    $instructions_array = $_POST['instructions'] ?? [];
    
    // --- ÚJ SZERVER OLDALI VALIDÁCIÓ ---
    $hasOneIngredient = false;
    foreach($ingredient_names as $name) {
        if (!empty(trim($name))) {
            $hasOneIngredient = true;
            break;
        }
    }
    
    $hasOneInstruction = false;
    foreach($instructions_array as $desc) {
        if (!empty(trim($desc))) {
            $hasOneInstruction = true;
            break;
        }
    }

    if (empty($title) || !$hasOneIngredient || !$hasOneInstruction) {
        header('Location: edit_post.php?id=' . $recipe_id . '&error=empty_fields');
        exit;
    }
    // --- VALIDÁCIÓ VÉGE ---

    // Adatbázis tranzakció indítása
    $conn->beginTransaction();

    try {
        // Jogosultság ellenőrzés (biztonsági okokból itt is)
        $sql_auth = "SELECT author_id FROM recipes WHERE id = :id";
        $stmt_auth = $conn->prepare($sql_auth);
        $stmt_auth->execute([':id' => $recipe_id]);
        $author = $stmt_auth->fetch();

        if ($author['author_id'] != $user_id && (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1)) {
            throw new Exception("Nincs jogosultságod a recept szerkesztéséhez.");
        }

        // Képkezelés
        $cover_image_sql_part = "";
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
            // ... (Itt jön a teljes képfeltöltő logika a save_recipe.php-ből) ...
            $target_dir = "uploads/";
            $file_extension = pathinfo($_FILES["cover_image"]["name"], PATHINFO_EXTENSION);
            $unique_filename = "post_img_" . uniqid() . "." . $file_extension;
            $target_file = $target_dir . $unique_filename;
            if (move_uploaded_file($_FILES["cover_image"]["tmp_name"], $target_file)) {
                $cover_image_sql_part = ", cover_image = :cover_image";
            }
        }

        // 1. Alap recept adatok frissítése
        $sql_recipe = "UPDATE recipes SET title = :title, prep_time = :prep_time, servings = :servings {$cover_image_sql_part} WHERE id = :id";
        $stmt_recipe = $conn->prepare($sql_recipe);
        $params = [
            ':title' => $title,
            ':prep_time' => $prep_time,
            ':servings' => $servings,
            ':id' => $recipe_id
        ];
        if (!empty($cover_image_sql_part)) {
            $params[':cover_image'] = $target_file;
        }
        $stmt_recipe->execute($params);

        // 2. Régi hozzávalók és lépések törlése
        $stmt_delete_ing = $conn->prepare("DELETE FROM ingredients WHERE recipe_id = :id");
        $stmt_delete_ing->execute([':id' => $recipe_id]);
        $stmt_delete_ins = $conn->prepare("DELETE FROM instructions WHERE recipe_id = :id");
        $stmt_delete_ins->execute([':id' => $recipe_id]);

        // 3. Új hozzávalók beszúrása
        $sql_ingredient = "INSERT INTO ingredients (recipe_id, quantity, unit, name) VALUES (:recipe_id, :quantity, :unit, :name)";
        $stmt_ingredient = $conn->prepare($sql_ingredient);
        for ($i = 0; $i < count($ingredient_names); $i++) {
            if (!empty($ingredient_names[$i])) {
                $stmt_ingredient->execute([
                    ':recipe_id' => $recipe_id,
                    ':quantity' => $quantities[$i],
                    ':unit' => $units[$i],
                    ':name' => $ingredient_names[$i]
                ]);
            }
        }

        // 4. Új elkészítési lépések beszúrása
        $sql_instruction = "INSERT INTO instructions (recipe_id, step_number, description) VALUES (:recipe_id, :step_number, :description)";
        $stmt_instruction = $conn->prepare($sql_instruction);
        $step_number = 1;
        foreach ($instructions_array as $instruction) {
            if (!empty(trim($instruction))) {
                $stmt_instruction->execute([
                    ':recipe_id' => $recipe_id,
                    ':step_number' => $step_number,
                    ':description' => trim($instruction)
                ]);
                $step_number++;
            }
        }

        $conn->commit();
        header('Location: post.php?id=' . $recipe_id . '&status=updated');
        exit;

    } catch (Exception $e) {
        $conn->rollBack();
        header('Location: edit_post.php?id=' . $recipe_id . '&error=' . urlencode($e->getMessage()));
        exit;
    }
} else {
    header('Location: index.php');
}
?>