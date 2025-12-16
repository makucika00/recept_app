<?php
// edit_post.php
session_start();
require_once 'db_config.php';

// 1. Ellenőrzések: Bejelentkezés és Recept ID
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$recipe_id = $_GET['id'] ?? null;
if (!$recipe_id) {
    header('Location: index.php');
    exit;
}

// 2. Adatok lekérdezése az adatbázisból
try {
    $sql = "SELECT * FROM recipes WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $recipe_id]);
    $recipe = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$recipe) {
        header('Location: index.php'); // Ha nincs ilyen recept
        exit;
    }

    // 3. Jogosultság ellenőrzés (csak a szerző vagy admin szerkeszthet)
    if ($recipe['author_id'] != $_SESSION['user_id'] && (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1)) {
        header('Location: post.php?id=' . $recipe_id); // Visszairányítás, ha nincs joga szerkeszteni
        exit;
    }

    // Hozzávalók és lépések lekérése
    $sql_ingredients = "SELECT * FROM ingredients WHERE recipe_id = :id ORDER BY id ASC";
    $stmt_ingredients = $conn->prepare($sql_ingredients);
    $stmt_ingredients->execute([':id' => $recipe_id]);
    $ingredients = $stmt_ingredients->fetchAll(PDO::FETCH_ASSOC);

    $sql_instructions = "SELECT * FROM instructions WHERE recipe_id = :id ORDER BY step_number ASC";
    $stmt_instructions = $conn->prepare($sql_instructions);
    $stmt_instructions->execute([':id' => $recipe_id]);
    $instructions = $stmt_instructions->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Adatbázis hiba: " . $e->getMessage());
}

require_once './header.php';
?>

<div class="main-content-area">
    <div class="recipe-wizard-container">
        <div class="wizard-header">
            <h1>Recept Szerkesztése</h1>
            <p>"<?php echo htmlspecialchars($recipe['title']); ?>"</p>
        </div>
        
        <form action="update_recipe.php" method="POST" enctype="multipart/form-data" id="editRecipeForm">
            <input type="hidden" name="recipe_id" value="<?php echo $recipe['id']; ?>">

            <div class="wizard-step active" style="display:block;">
                <div class="form-group">
                    <label for="recipe-title">Recept Neve</label>
                    <input type="text" id="recipe-title" name="title" class="form-control" value="<?php echo htmlspecialchars($recipe['title']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="cover-image">Borítókép cseréje (hagyd üresen, ha nem változtatod)</label>
                    <input type="file" id="cover-image" name="cover_image" class="form-control">
                    <?php if(!empty($recipe['cover_image'])): ?>
                        <small>Jelenlegi kép: <a href="<?php echo htmlspecialchars($recipe['cover_image']); ?>" target="_blank">megtekintés</a></small>
                    <?php endif; ?>
                </div>
                <hr>
                <div class="form-row-centered">
                    <div class="form-group">
                        <label for="prep-time">Elkészítési idő</label>
                        <div class="input-with-unit">
                            <input type="number" id="prep-time" name="prep_time" class="form-control" value="<?php echo htmlspecialchars($recipe['prep_time']); ?>">
                            <span>perc</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="servings">Adagok száma</label>
                        <input type="number" id="servings" name="servings" class="form-control" value="<?php echo htmlspecialchars($recipe['servings']); ?>">
                    </div>
                </div>
                <hr>
                <div class="form-group">
                    <label>Hozzávalók</label>
                    <div class="ingredients-list-container" id="ingredientsContainer">
                        <?php if (empty($ingredients)): ?>
                             <div class="ingredient-row">
                                <input type="text" name="quantity[]" class="form-control" placeholder="Menny.">
                                <select name="unit[]" class="form-control">
                                    <?php $units = ['db', 'g', 'dkg', 'kg', 'ml', 'cl', 'dl', 'l', 'késhegynyi', 'csipet', 'ek', 'tk']; ?>
                                    <?php foreach($units as $unit): ?><option value="<?php echo $unit; ?>"><?php echo $unit; ?></option><?php endforeach; ?>
                                </select>
                                <input type="text" name="ingredient_name[]" class="form-control ingredient-name-input" placeholder="Hozzávaló neve">
                                <button type="button" class="btn remove-ingredient-btn"><i class="fas fa-times"></i></button>
                            </div>
                        <?php else: ?>
                            <?php foreach($ingredients as $ingredient): ?>
                                <div class="ingredient-row">
                                    <input type="text" name="quantity[]" class="form-control" placeholder="Menny." value="<?php echo htmlspecialchars($ingredient['quantity']); ?>">
                                    <select name="unit[]" class="form-control">
                                        <?php $units = ['db', 'g', 'dkg', 'kg', 'ml', 'cl', 'dl', 'l', 'késhegynyi', 'csipet', 'ek', 'tk']; ?>
                                        <?php foreach($units as $unit): ?>
                                            <option value="<?php echo $unit; ?>" <?php if($ingredient['unit'] == $unit) echo 'selected'; ?>><?php echo $unit; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="text" name="ingredient_name[]" class="form-control ingredient-name-input" placeholder="Hozzávaló neve" value="<?php echo htmlspecialchars($ingredient['name']); ?>">
                                    <button type="button" class="btn remove-ingredient-btn"><i class="fas fa-times"></i></button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn add-ingredient-btn" id="addIngredientBtn"><i class="fas fa-plus"></i> Új hozzávaló</button>
                </div>
                <hr>
                <div class="form-group">
                    <label>Elkészítés Lépései</label>
                    <div class="instructions-list-container" id="instructionsContainer">
                         <?php if (empty($instructions)): ?>
                            <div class="instruction-step-row">
                                <span class="step-number">1</span>
                                <textarea name="instructions[]" class="form-control" placeholder="Írd le a lépést..."></textarea>
                                <button type="button" class="btn remove-ingredient-btn"><i class="fas fa-times"></i></button>
                            </div>
                         <?php else: ?>
                            <?php foreach($instructions as $instruction): ?>
                                <div class="instruction-step-row">
                                    <span class="step-number"><?php echo $instruction['step_number']; ?></span>
                                    <textarea name="instructions[]" class="form-control" placeholder="Írd le a lépést..."><?php echo htmlspecialchars($instruction['description']); ?></textarea>
                                    <button type="button" class="btn remove-ingredient-btn"><i class="fas fa-times"></i></button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn add-ingredient-btn" id="addInstructionBtn"><i class="fas fa-plus"></i> Új lépés</button>
                </div>

                <div class="wizard-navigation">
                    <a href="post.php?id=<?php echo $recipe_id; ?>" class="btn wizard-prev-btn">Mégse</a>
                    <button type="submit" class="btn filter-btn">Változtatások mentése</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editForm = document.getElementById('editRecipeForm');

    // Hozzávalók Szekció
    const ingredientsContainer = document.getElementById('ingredientsContainer');
    const addIngredientBtn = document.getElementById('addIngredientBtn');

    function createIngredientRow() {
        const row = document.createElement('div');
        row.className = 'ingredient-row';
        row.innerHTML = `
            <input type="text" name="quantity[]" class="form-control" placeholder="Menny.">
            <select name="unit[]" class="form-control">
                <option value="db">db</option><option value="g">g</option><option value="dkg">dkg</option><option value="kg">kg</option><option value="ml">ml</option><option value="cl">cl</option><option value="dl">dl</option><option value="l">l</option><option value="késhegynyi">késhegynyi</option><option value="csipet">csipet</option><option value="ek">evőkanál</option><option value="tk">teáskanál</option>
            </select>
            <input type="text" name="ingredient_name[]" class="form-control ingredient-name-input" placeholder="Hozzávaló neve">
            <button type="button" class="btn remove-ingredient-btn"><i class="fas fa-times"></i></button>`;
        return row;
    }
    
    function handleIngredientRemove(event) {
        const row = event.target.closest('.ingredient-row');
        if (ingredientsContainer.querySelectorAll('.ingredient-row').length > 1) {
            row.remove();
        } else {
            row.classList.add('shake-animation');
            setTimeout(() => row.classList.remove('shake-animation'), 500);
        }
    }

    addIngredientBtn.addEventListener('click', () => {
        const newRow = createIngredientRow();
        ingredientsContainer.appendChild(newRow);
    });
    
    ingredientsContainer.addEventListener('click', function(e) {
        if (e.target.closest('.remove-ingredient-btn')) {
            handleIngredientRemove(e);
        }
    });

    // Elkészítési Lépések Szekció
    const instructionsContainer = document.getElementById('instructionsContainer');
    const addInstructionBtn = document.getElementById('addInstructionBtn');

    function updateStepNumbers() {
        const allRows = instructionsContainer.querySelectorAll('.instruction-step-row');
        allRows.forEach((row, index) => {
            row.querySelector('.step-number').textContent = index + 1;
        });
    }

    function createInstructionRow() {
        const row = document.createElement('div');
        row.className = 'instruction-step-row';
        row.innerHTML = `
            <span class="step-number">1</span>
            <textarea name="instructions[]" class="form-control" placeholder="Írd le a lépést..."></textarea>
            <button type="button" class="btn remove-ingredient-btn"><i class="fas fa-times"></i></button>`;
        return row;
    }

    function handleInstructionRemove(event) {
        const row = event.target.closest('.instruction-step-row');
        if (instructionsContainer.querySelectorAll('.instruction-step-row').length > 1) {
            row.remove();
            updateStepNumbers();
        } else {
            row.classList.add('shake-animation');
            setTimeout(() => row.classList.remove('shake-animation'), 500);
        }
    }

    addInstructionBtn.addEventListener('click', () => {
        const newRow = createInstructionRow();
        instructionsContainer.appendChild(newRow);
        updateStepNumbers();
    });
    
    instructionsContainer.addEventListener('click', function(e) {
        if (e.target.closest('.remove-ingredient-btn')) {
            handleInstructionRemove(e);
        }
    });
    
    updateStepNumbers();

    // Mentés előtti validáció
    editForm.addEventListener('submit', function(event) {
        let isValid = true;

        // Cím validálása
        const titleInput = document.getElementById('recipe-title');
        titleInput.classList.remove('invalid-field');
        if (titleInput.value.trim() === '') {
            titleInput.classList.add('invalid-field');
            isValid = false;
        }

        // Hozzávalók validálása
        const ingredientNameInputs = ingredientsContainer.querySelectorAll('input[name="ingredient_name[]"]');
        let isOneIngredient = false;
        ingredientNameInputs.forEach(input => input.classList.remove('invalid-field'));
        for(const input of ingredientNameInputs) {
            if (input.value.trim() !== '') {
                isOneIngredient = true;
                break;
            }
        }
        if (!isOneIngredient) {
            if(ingredientNameInputs[0]) ingredientNameInputs[0].classList.add('invalid-field');
            isValid = false;
        }

        // Lépések validálása
        const instructionTextareas = instructionsContainer.querySelectorAll('textarea[name="instructions[]"]');
        let isOneInstruction = false;
        instructionTextareas.forEach(input => input.classList.remove('invalid-field'));
        for(const textarea of instructionTextareas) {
            if (textarea.value.trim() !== '') {
                isOneInstruction = true;
                break;
            }
        }
        if (!isOneInstruction) {
            if(instructionTextareas[0]) instructionTextareas[0].classList.add('invalid-field');
            isValid = false;
        }

        if (!isValid) {
            event.preventDefault(); // Megakadályozza a küldést
            alert('Kérjük, töltsd ki a pirossal jelölt kötelező mezőket!');
            // Opcionális: a hibás mezőhöz görgetés
            const firstInvalidField = document.querySelector('.invalid-field');
            if(firstInvalidField) {
                firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
});
</script>

<?php
require_once './footer.php';
?>