<?php
// post.php
session_start();
require_once 'db_config.php';

$post_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if (!$post_id) {
    header('Location: index.php');
    exit;
}

try {
    // Lekérdezés kiegészítve a 'display_name'-mel, 'is_cooked' és 'user_rating' állapottal
    $sql = "SELECT r.*, u.username, u.display_name,
            (SELECT COUNT(*) FROM user_cooked_recipes WHERE recipe_id = r.id AND user_id = :user_id) as is_cooked,
            (SELECT rating FROM recipe_ratings WHERE recipe_id = r.id AND user_id = :user_id) as user_rating
            FROM recipes r
            LEFT JOIN users u ON r.author_id = u.id 
            WHERE r.id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $post_id, ':user_id' => $user_id]);
    $recipe = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$recipe) {
        header('Location: index.php');
        exit;
    }

    $sql_ingredients = "SELECT * FROM ingredients WHERE recipe_id = :recipe_id ORDER BY id ASC";
    $stmt_ingredients = $conn->prepare($sql_ingredients);
    $stmt_ingredients->execute([':recipe_id' => $post_id]);
    $ingredients = $stmt_ingredients->fetchAll(PDO::FETCH_ASSOC);

    $sql_instructions = "SELECT * FROM instructions WHERE recipe_id = :recipe_id ORDER BY step_number ASC";
    $stmt_instructions = $conn->prepare($sql_instructions);
    $stmt_instructions->execute([':recipe_id' => $post_id]);
    $instructions = $stmt_instructions->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Adatbázis hiba: " . $e->getMessage());
}

require_once './header.php';
?>

<div class="main-content-area">
    <div class="single-post">
        <div class="single-post-header">
            <h1><?php echo htmlspecialchars($recipe['title']); ?></h1>

            <div class="recipe-author-line">
                <span class="by-text">by</span>
                <span class="author-name">
                    <a href="profile.php?id=<?php echo $recipe['author_id']; ?>">
                        <?php echo htmlspecialchars($recipe['display_name'] ?? $recipe['username']); // Ha nincs display_name, a username jelenik meg  ?>
                    </a>
                </span>
            </div>

            <div class="rating-display-wrapper" 
                 data-recipe-id="<?php echo $recipe['id']; ?>" 
                 data-user-rating="<?php echo $recipe['user_rating'] ?? 0; ?>">
                <div class="stars-display">
                    <?php
                    for ($i = 1; $i <= 5; $i++):
                        if ($i <= round($recipe['avg_rating'])) {
                            echo '<i class="fas fa-star filled"></i>';
                        } else {
                            echo '<i class="far fa-star"></i>';
                        }
                    endfor;
                    ?>
                </div>
                <span class="rating-count">(<?php echo $recipe['rating_count']; ?> értékelés)</span>
            </div>
        </div>

        <?php if (!empty($recipe['prep_time']) || !empty($recipe['servings'])): ?>
            <div class="recipe-stats">
                <?php if (!empty($recipe['prep_time'])): ?>
                    <div class="stat-item">
                        <span class="stat-value"><?php echo htmlspecialchars($recipe['prep_time']); ?></span>
                        <span class="stat-label">perc</span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($recipe['prep_time']) && !empty($recipe['servings'])): ?>
                    <div class="stat-divider"></div>
                <?php endif; ?>
                <?php if (!empty($recipe['servings'])): ?>
                    <div class="stat-item servings-calculator">
                        <div class="servings-stepper">
                            <button class="stepper-btn" id="servings-minus" type="button">-</button>
                            <span class="stat-value" id="servings-display"><?php echo htmlspecialchars($recipe['servings']); ?></span>
                            <button class="stepper-btn" id="servings-plus" type="button">+</button>
                        </div>
                        <span class="stat-label">adag</span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($recipe['cover_image'])): ?>
            <div class="single-post-feature-image">
                <img src="<?php echo htmlspecialchars($recipe['cover_image']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
            </div>
        <?php endif; ?>

        <div class="ingredients-section-wrapper">
            <div class="ingredients-section">
                <h2><i class="fas fa-carrot"></i> Hozzávalók</h2>
                <ul class="ingredients-list">
                    <?php foreach ($ingredients as $ingredient): ?>
                        <li>
                            <span class="quantity" data-original-quantity="<?php echo htmlspecialchars($ingredient['quantity']); ?>">
                                <?php echo htmlspecialchars($ingredient['quantity']); ?>
                            </span>
                            <span class="unit"><?php echo htmlspecialchars($ingredient['unit']); ?></span>
                            <span class="name ingredient-add-to-list" title="Hozzáadás a bevásárlólistához">
                                <?php echo htmlspecialchars($ingredient['name']); ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="post-content">
            <div class="instructions-section">
                <h2><i class="fas fa-utensils"></i> Elkészítés</h2>
                <div class="instructions-list-modern">
                    <?php foreach ($instructions as $instruction): ?>
                        <div class="instruction-step-modern" tabindex="0">
                            <div class="step-number-modern">
                                <span><?php echo $instruction['step_number']; ?></span>
                                <i class="fas fa-check check-icon"></i>
                            </div>
                            <div class="step-description">
                                <p><?php echo nl2br(htmlspecialchars($instruction['description'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php if ($user_id): ?>
            <div class="cooked-action-wrapper">
                <button class="btn cooked-btn <?php if ($recipe['is_cooked']) echo 'cooked'; ?>" id="cookedBtn" data-recipe-id="<?php echo $recipe['id']; ?>">
                    <span class="icon-wrapper">
                        <i class="fas fa-check check-icon"></i>
                        <i class="fas fa-plus plus-icon"></i>
                    </span>
                    <span class="text">
                        <?php echo $recipe['is_cooked'] ? 'Elkészítve!' : 'Elkészítettem'; ?>
                    </span>
                </button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id']) && (($_SESSION['is_admin'] ?? 0) == 1 || $_SESSION['user_id'] == $recipe['author_id'])): ?>
            <div class="admin-actions-single">
                <a href="edit_post.php?id=<?php echo $recipe['id']; ?>" class="action-btn" title="Szerkesztés">
                    <i class="fas fa-pencil-alt"></i>
                </a>
                <button class="action-btn delete-btn danger-btn" data-id="<?php echo $recipe['id']; ?>" title="Törlés">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- Lépések kezelése ---
        const steps = document.querySelectorAll('.instruction-step-modern');
        steps.forEach(step => {
            step.addEventListener('click', function () {
                this.classList.toggle('completed');
            });
            step.addEventListener('keydown', function (event) {
                if (event.key === 'Enter')
                    this.classList.toggle('completed');
            });
        });

        // --- Adagok Kezelése ---
        const originalServings = <?php echo json_encode((int) ($recipe['servings'] ?? 1)); ?>;
        if (document.getElementById('servings-display')) {
            let currentServings = originalServings;
            const servingsDisplay = document.getElementById('servings-display');
            const minusBtn = document.getElementById('servings-minus');
            const plusBtn = document.getElementById('servings-plus');
            const ingredientQuantities = document.querySelectorAll('.ingredients-list .quantity');

            function updateIngredients() {
                if (originalServings === 0)
                    return;
                const ratio = currentServings / originalServings;
                servingsDisplay.textContent = currentServings;
                ingredientQuantities.forEach(span => {
                    const originalQuantity = parseFloat(String(span.dataset.originalQuantity).replace(',', '.'));
                    if (!isNaN(originalQuantity)) {
                        let newQuantity = originalQuantity * ratio;
                        if (newQuantity < 1)
                            newQuantity = newQuantity.toFixed(2);
                        else if (newQuantity < 10)
                            newQuantity = newQuantity.toFixed(1);
                        else
                            newQuantity = Math.round(newQuantity);
                        span.textContent = newQuantity.toString().replace('.', ',');
                    }
                });
            }
            minusBtn.addEventListener('click', function () {
                if (currentServings > 1) {
                    currentServings--;
                    updateIngredients();
                }
            });
            plusBtn.addEventListener('click', function () {
                if (currentServings < 10) {
                    currentServings++;
                    updateIngredients();
                }
            });
        }

        // --- "Elkészítettem" Gomb Kezelése ---
        const cookedBtn = document.getElementById('cookedBtn');
        if (cookedBtn) {
            cookedBtn.addEventListener('click', function () {
                const recipeId = this.dataset.recipeId;
                fetch('toggle_cooked_status.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({recipe_id: recipeId})
                })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                this.classList.toggle('cooked');
                                const textSpan = this.querySelector('.text');
                                const titleH1 = document.querySelector('.single-post-header h1');
                                if (data.status === 'added') {
                                    textSpan.textContent = 'Elkészítve!';
                                    if (!titleH1.querySelector('.cooked-checkmark-title')) {
                                        titleH1.insertAdjacentHTML('beforeend', ' <span class="cooked-checkmark-title" title="Ezt a receptet már elkészítetted"><i class="fas fa-check-circle"></i></span>');
                                    }
                                } else {
                                    textSpan.textContent = 'Elkészítettem';
                                    const checkmark = titleH1.querySelector('.cooked-checkmark-title');
                                    if (checkmark)
                                        checkmark.remove();
                                }
                            } else {
                                alert(data.message || 'Hiba történt.');
                            }
                        });
            });
        }

        // ===== JAVÍTOTT CSILLAGOS ÉRTÉKELÉS KEZELÉSE =====
        const ratingWrapper = document.querySelector('.rating-display-wrapper');
        if (ratingWrapper && <?php echo $user_id ? 'true' : 'false'; ?>) {
            const stars = ratingWrapper.querySelectorAll('.stars-display i');
            const ratingCountSpan = ratingWrapper.querySelector('.rating-count');
            const recipeId = ratingWrapper.dataset.recipeId;

            // Változók az állapotok tárolására
            let userRating = parseInt(ratingWrapper.dataset.userRating);
            let avgRating = Math.round(parseFloat(<?php echo json_encode($recipe['avg_rating']); ?>));

            // Funkció a csillagok vizuális frissítésére
            function setStarsVisual(displayRating) {
                stars.forEach((star, index) => {
                    // 1. lépés: a csillagok feltöltése a `displayRating` (átlag vagy hover) alapján
                    if (index < displayRating) {
                        star.className = 'fas fa-star filled';
                    } else {
                        star.className = 'far fa-star';
                    }
                    // 2. lépés: a felhasználó saját értékelésének külön kiemelése
                    if (index < userRating) {
                        star.classList.add('user-rated');
                    } else {
                        star.classList.remove('user-rated');
                    }
                });
            }

            // Hover és kattintás események
            stars.forEach((star, index) => {
                star.addEventListener('mouseover', () => {
                    setStarsVisual(index + 1); // Hover-re megmutatja, mit választana a user
                });
                star.addEventListener('mouseout', () => {
                    setStarsVisual(avgRating); // Egér elhúzásakor VISSZAÁLL AZ ÁTLAGRA
                });
                star.addEventListener('click', () => {
                    const newRating = index + 1;

                    fetch('submit_rating.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({recipe_id: recipeId, rating: newRating})
                    })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Állapotok frissítése a szerver válasza alapján
                                    userRating = newRating;
                                    avgRating = Math.round(parseFloat(data.new_avg_rating));

                                    // HTML elemek frissítése
                                    ratingWrapper.dataset.userRating = newRating;
                                    ratingCountSpan.textContent = `(${data.new_rating_count} értékelés)`;

                                    // Csillagok újrarajzolása a FRISS ÁTLAG alapján
                                    setStarsVisual(avgRating);
                                } else {
                                    alert(data.message || 'Hiba történt az értékelés mentésekor.');
                                }
                            });
                });
            });

            // Kezdeti állapot beállítása az oldal betöltésekor
            setStarsVisual(avgRating);
        }
        // ===================================================
        // ## BEVÁSÁRLÓLISTA LOGIKA ##
        // ===================================================
        const shoppingListContainer = document.getElementById('shopping-list-container');
        const listHeader = shoppingListContainer.querySelector('.shopping-list-header');
        const listItemsUl = document.getElementById('shopping-list-items');
        const itemCounter = shoppingListContainer.querySelector('.item-counter');
        const clearListBtn = document.getElementById('clear-list-btn');
        const toast = document.getElementById('toast-notification');
        const toastMessage = document.getElementById('toast-message');
        const ingredientsList = document.querySelector('.ingredients-list');

        // 1. Lista betöltése a böngésző memóriájából
        let shoppingList = JSON.parse(localStorage.getItem('shoppingList')) || [];

        // 2. Funkciók
        const saveList = () => {
            localStorage.setItem('shoppingList', JSON.stringify(shoppingList));
        };

        const renderList = () => {
            listItemsUl.innerHTML = ''; // Lista kiürítése
            if (shoppingList.length === 0) {
                shoppingListContainer.classList.remove('has-items');
                const emptyLi = document.createElement('li');
                emptyLi.className = 'empty-list-item';
                emptyLi.textContent = 'A bevásárlólistád üres.';
                listItemsUl.appendChild(emptyLi);
            } else {
                shoppingListContainer.classList.add('has-items');
                shoppingList.forEach((item, index) => {
                    const li = document.createElement('li');
                    li.className = 'shopping-list-item';
                    if (item.checked)
                        li.classList.add('checked');

                    li.innerHTML = `
                    <div class="item-checkbox" data-index="${index}" title="Megvettem"></div>
                    <span class="item-text">${item.text}</span>
                    <button class="item-delete" data-index="${index}" title="Törlés">&times;</button>
                `;
                    listItemsUl.appendChild(li);
                });
            }
            itemCounter.textContent = shoppingList.length;
        };

        const showToast = (message) => {
            toastMessage.textContent = message;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000); // 3 másodperc után eltűnik
        };

        // 3. Eseménykezelők
        if (ingredientsList) {
            ingredientsList.addEventListener('click', function (e) {
                if (e.target.classList.contains('ingredient-add-to-list')) {
                    const li = e.target.closest('li');
                    const quantity = li.querySelector('.quantity').textContent.trim();
                    const unit = li.querySelector('.unit').textContent.trim();
                    const name = e.target.textContent.trim();
                    const fullText = `${quantity} ${unit} ${name}`;

                    // Ellenőrizzük, hogy a tétel már a listán van-e
                    if (shoppingList.some(item => item.text === fullText)) {
                        showToast('Ez a tétel már a listádon van!');
                        return;
                    }

                    shoppingList.push({text: fullText, checked: false});
                    saveList();
                    renderList();
                    showToast('Hozzáadva a bevásárlólistához!');
                }
            });
        }

        listHeader.addEventListener('click', () => {
            shoppingListContainer.classList.toggle('collapsed');
        });

        clearListBtn.addEventListener('click', () => {
            if (confirm('Biztosan törölni szeretnéd a teljes bevásárlólistát?')) {
                shoppingList = [];
                saveList();
                renderList();
            }
        });

        listItemsUl.addEventListener('click', function (e) {
            const index = e.target.dataset.index;
            if (e.target.classList.contains('item-checkbox')) {
                shoppingList[index].checked = !shoppingList[index].checked;
                saveList();
                renderList();
            }
            if (e.target.classList.contains('item-delete')) {
                shoppingList.splice(index, 1);
                saveList();
                renderList();
            }
        });

        // 4. Kezdeti renderelés
        renderList();
    });
</script>

<?php
require_once './footer.php';
?>