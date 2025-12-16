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
                <div class="ingredients-header">
                    <h2><i class="fas fa-carrot"></i> Hozzávalók</h2>
                    <button id="add-all-ingredients-btn" class="btn-modern-blue" title="Összes hozzávaló a listához">
                        <i class="fas fa-cart-plus"></i>
                        <span class="btn-text">Összes hozzáadása</span>
                    </button>
                </div>
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

            let userRating = parseInt(ratingWrapper.dataset.userRating);
            let avgRating = Math.round(parseFloat(<?php echo json_encode($recipe['avg_rating']); ?>));

            function setStarsVisual(displayRating) {
                stars.forEach((star, index) => {
                    if (index < displayRating) {
                        star.className = 'fas fa-star filled';
                    } else {
                        star.className = 'far fa-star';
                    }
                    if (index < userRating) {
                        star.classList.add('user-rated');
                    } else {
                        star.classList.remove('user-rated');
                    }
                });
            }

            stars.forEach((star, index) => {
                star.addEventListener('mouseover', () => {
                    setStarsVisual(index + 1);
                });
                star.addEventListener('mouseout', () => {
                    setStarsVisual(avgRating);
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
                                    userRating = newRating;
                                    avgRating = Math.round(parseFloat(data.new_avg_rating));
                                    ratingWrapper.dataset.userRating = newRating;
                                    ratingCountSpan.textContent = `(${data.new_rating_count} értékelés)`;
                                    setStarsVisual(avgRating);
                                } else {
                                    alert(data.message || 'Hiba történt az értékelés mentésekor.');
                                }
                            });
                });
            });
            setStarsVisual(avgRating);
        }
    });
</script>

<?php
require_once './footer.php';
?>