<?php
// create_recipe.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once './header.php';
?>

<div class="main-content-area">
    <div class="recipe-wizard-container">
        <div class="wizard-header">
            <h1>Új Recept Létrehozása</h1>
            <p>Vezetünk a folyamaton, lépésről lépésre!</p>
        </div>

        <div class="wizard-progress">
            <div class="progress-bar-container">
                <div class="progress-bar" id="progressBar"></div>
            </div>
            <span class="step-counter" id="stepCounter">1 / 4</span>
        </div>

        <form action="save_recipe.php" method="POST" enctype="multipart/form-data" id="recipeWizardForm">
            <div class="wizard-step active">
                <div class="form-group">
                    <label for="recipe-title">Mi a recepted neve?</label>
                    <input type="text" id="recipe-title" name="title" class="form-control" placeholder="Pl. Nagymama isteni almás pitéje" required>
                </div>
                <div class="form-group">
                    <label for="cover-image">Tölts fel egy fotót, ami bemutatja az ételt</label>
                    <input type="file" id="cover-image" name="cover_image" class="form-control">
                </div>
                <div class="wizard-navigation justify-end">
                    <button type="button" class="btn wizard-next-btn">Tovább <i class="fas fa-chevron-right"></i></button>
                </div>
            </div>

            <div class="wizard-step">
                <div class="form-row-centered">
                    <div class="form-group">
                        <label for="prep-time">Elkészítési idő</label>
                        <div class="input-with-unit">
                            <input type="number" id="prep-time" name="prep_time" class="form-control" value="30">
                            <span>perc</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="servings">Adagok száma</label>
                        <input type="number" id="servings" name="servings" class="form-control" placeholder="Pl. 4" value="4">
                    </div>
                </div>
                <div class="wizard-navigation">
                    <button type="button" class="btn wizard-prev-btn"><i class="fas fa-chevron-left"></i> Vissza</button>
                    <button type="button" class="btn wizard-next-btn">Tovább <i class="fas fa-chevron-right"></i></button>
                </div>
            </div>

            <div class="wizard-step">
                <div class="form-group">
                    <label>Hozzávalók</label>
                    <div class="ingredients-list-container" id="ingredientsContainer">
                    </div>
                    <button type="button" class="btn add-ingredient-btn" id="addIngredientBtn">
                        <i class="fas fa-plus"></i> Új hozzávaló hozzáadása
                    </button>
                </div>
                <div class="wizard-navigation">
                    <button type="button" class="btn wizard-prev-btn"><i class="fas fa-chevron-left"></i> Vissza</button>
                    <button type="button" class="btn wizard-next-btn">Tovább <i class="fas fa-chevron-right"></i></button>
                </div>
            </div>

            <div class="wizard-step">
                <div class="form-group">
                    <label>Elkészítés Lépései</label>
                    <div class="instructions-list-container" id="instructionsContainer">
                    </div>
                    <button type="button" class="btn add-ingredient-btn" id="addInstructionBtn">
                        <i class="fas fa-plus"></i> Új lépés hozzáadása
                    </button>
                </div>
                <div class="wizard-navigation">
                    <button type="button" class="btn wizard-prev-btn"><i class="fas fa-chevron-left"></i> Vissza</button>
                    <button type="submit" class="btn filter-btn">Recept közzététele <i class="fas fa-check"></i></button>
                </div>
            </div>
    </div>
</form>
</div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const wizardForm = document.getElementById('recipeWizardForm');
        const wizardSteps = document.querySelectorAll('.wizard-step');
        const nextButtons = document.querySelectorAll('.wizard-next-btn');
        const prevButtons = document.querySelectorAll('.wizard-prev-btn');
        const progressBar = document.getElementById('progressBar');
        const stepCounter = document.getElementById('stepCounter');

        let currentStep = 0;
        const totalSteps = wizardSteps.length;

        function updateWizard() {
            wizardSteps.forEach((step, index) => {
                step.classList.toggle('active', index === currentStep);
            });
            const progressPercentage = ((currentStep + 1) / totalSteps) * 100;
            progressBar.style.width = progressPercentage + '%';
            stepCounter.textContent = `${currentStep + 1} / ${totalSteps}`;
        }

        // ===== JAVÍTOTT VALIDÁCIÓS FÜGGVÉNY =====
        function validateStep(stepIndex) {
            // 1. Lépés: Cím validálása
            if (stepIndex === 0) {
                const titleInput = document.getElementById('recipe-title');
                titleInput.classList.remove('invalid-field');
                if (titleInput.value.trim() === '') {
                    titleInput.classList.add('invalid-field');
                    titleInput.focus();
                    return false;
                }
            }

            // 3. Lépés: Hozzávalók validálása
            if (stepIndex === 2) {
                const ingredientNameInputs = ingredientsContainer.querySelectorAll('input[name="ingredient_name[]"]');
                let isAtLeastOneIngredientValid = false;
                ingredientNameInputs.forEach(input => input.classList.remove('invalid-field'));
                for (const input of ingredientNameInputs) {
                    if (input.value.trim() !== '') {
                        isAtLeastOneIngredientValid = true;
                        break;
                    }
                }
                if (!isAtLeastOneIngredientValid) {
                    const firstIngredientNameInput = ingredientNameInputs[0];
                    if (firstIngredientNameInput) {
                        firstIngredientNameInput.classList.add('invalid-field');
                        firstIngredientNameInput.focus();
                    }
                    return false;
                }
            }

            // 4. Lépés: Elkészítés validálása
            if (stepIndex === 3) {
                const instructionTextareas = instructionsContainer.querySelectorAll('textarea[name="instructions[]"]');
                let isAtLeastOneInstructionValid = false;
                instructionTextareas.forEach(input => input.classList.remove('invalid-field'));
                for (const textarea of instructionTextareas) {
                    if (textarea.value.trim() !== '') {
                        isAtLeastOneInstructionValid = true;
                        break;
                    }
                }
                if (!isAtLeastOneInstructionValid) {
                    const firstTextarea = instructionTextareas[0];
                    if (firstTextarea) {
                        firstTextarea.classList.add('invalid-field');
                        firstTextarea.focus();
                    }
                    return false;
                }
            }
            return true; // Ha semmi nem bukott el, a validáció sikeres
        }

        nextButtons.forEach(button => {
            button.addEventListener('click', () => {
                if (validateStep(currentStep) && currentStep < totalSteps - 1) {
                    currentStep++;
                    updateWizard();
                }
            });
        });

        prevButtons.forEach(button => {
            button.addEventListener('click', () => {
                if (currentStep > 0) {
                    currentStep--;
                    updateWizard();
                }
            });
        });

        wizardForm.addEventListener('submit', function (event) {
            for (let i = 0; i < totalSteps; i++) {
                if (!validateStep(i)) {
                    event.preventDefault();
                    currentStep = i;
                    updateWizard();
                    alert("Kérjük, töltsd ki a pirossal jelölt kötelező mezőt a továbblépéshez!");
                    break;
                }
            }
        });

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
            row.querySelector('.remove-ingredient-btn').addEventListener('click', function () {
                if (ingredientsContainer.querySelectorAll('.ingredient-row').length > 1) {
                    row.remove();
                } else {
                    row.classList.add('shake-animation');
                    setTimeout(() => row.classList.remove('shake-animation'), 500);
                }
            });
            ingredientsContainer.appendChild(row);
        }
        addIngredientBtn.addEventListener('click', createIngredientRow);
        createIngredientRow(); // Kezdeti egy sor

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
            row.querySelector('.remove-ingredient-btn').addEventListener('click', function () {
                if (instructionsContainer.querySelectorAll('.instruction-step-row').length > 1) {
                    row.remove();
                    updateStepNumbers();
                } else {
                    row.classList.add('shake-animation');
                    setTimeout(() => row.classList.remove('shake-animation'), 500);
                }
            });
            instructionsContainer.appendChild(row);
            updateStepNumbers();
        }
        addInstructionBtn.addEventListener('click', createInstructionRow);
        createInstructionRow(); // Kezdeti egy sor

        updateWizard();
    });
</script>

<?php
require_once './footer.php';
?>