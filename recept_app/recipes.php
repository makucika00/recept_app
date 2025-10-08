<?php
// recipes.php
session_start();
require_once 'db_config.php';

// Szűrési és rendezési paraméterek
$filter_featured = isset($_GET['filter_featured']);
$filter_cooked = isset($_GET['filter_cooked']);
$sort_by = $_GET['sort_by'] ?? 'date_desc';
$user_id = $_SESSION['user_id'] ?? null;

try {
    // Alap lekérdezés
    $sql = "SELECT r.*, u.username, 
            (SELECT COUNT(*) FROM user_cooked_recipes ucr WHERE ucr.recipe_id = r.id AND ucr.user_id = :user_id) as is_cooked
            FROM recipes r 
            LEFT JOIN users u ON r.author_id = u.id";
    
    $params = [':user_id' => $user_id];
    $where_clauses = [];

    // JOIN hozzáadása, ha az "elkészített" szűrés aktív
    if ($filter_cooked) {
        $sql .= " JOIN user_cooked_recipes ucr_filter ON r.id = ucr_filter.recipe_id AND ucr_filter.user_id = :user_id";
    }

    // Szűrési feltétel a kiemelt receptekre
    if ($filter_featured) {
        $where_clauses[] = "r.is_featured = 1";
    }

    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(' AND ', $where_clauses);
    }
    
    // RENDEZÉSI FELTÉTEL KIEGÉSZÍTÉSE
    switch ($sort_by) {
        case 'date_asc': 
            $sql .= " ORDER BY r.created_at ASC"; 
            break;
        case 'title_asc': 
            $sql .= " ORDER BY r.title ASC"; 
            break;
        case 'title_desc': 
            $sql .= " ORDER BY r.title DESC"; 
            break;
        case 'prep_time_asc': 
            $sql .= " ORDER BY r.prep_time ASC"; 
            break;
        case 'rating_desc': // ÚJ RENDEZÉSI ESET
            $sql .= " ORDER BY r.avg_rating DESC, r.rating_count DESC"; 
            break;
        default: 
            $sql .= " ORDER BY r.created_at DESC"; 
            break;
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $recipes = [];
}

require_once './header.php';
?>

<div class="all-recipes-container"> 
    <div class="posts-header">
        <h2>Összes Recept</h2>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="create_recipe.php" class="action-btn add-btn" title="Új recept létrehozása">
                <i class="fas fa-plus"></i>
            </a>
        <?php endif; ?>
    </div>

    <div class="search-controls">
        <form action="recipes.php" method="GET">
            <div class="filter-group">
                <input type="checkbox" name="filter_featured" id="filter_featured" <?php if ($filter_featured) echo 'checked'; ?>>
                <label for="filter_featured">Csak a kiemelt receptek</label>
            </div>
            
            <?php if ($user_id): ?>
            <div class="filter-group">
                <input type="checkbox" name="filter_cooked" id="filter_cooked" <?php if ($filter_cooked) echo 'checked'; ?>>
                <label for="filter_cooked">Elkészített receptek</label>
            </div>
            <?php endif; ?>
            
            <div class="filter-group">
                <label for="sort_by">Rendezés:</label>
                <select name="sort_by" id="sort_by">
                    <option value="date_desc" <?php if ($sort_by == 'date_desc') echo 'selected'; ?>>Dátum szerint (legújabb elöl)</option>
                    <option value="rating_desc" <?php if ($sort_by == 'rating_desc') echo 'selected'; ?>>Értékelés szerint (legjobb elöl)</option>
                    <option value="date_asc" <?php if ($sort_by == 'date_asc') echo 'selected'; ?>>Dátum szerint (legrégebbi elöl)</option>
                    <option value="title_asc" <?php if ($sort_by == 'title_asc') echo 'selected'; ?>>Név szerint (A-Z)</option>
                    <option value="title_desc" <?php if ($sort_by == 'title_desc') echo 'selected'; ?>>Név szerint (Z-A)</option>
                    <option value="prep_time_asc" <?php if ($sort_by == 'prep_time_asc') echo 'selected'; ?>>Elkészítési idő szerint (növekvő)</option>
                </select>
            </div>
            <button type="submit" class="filter-btn">Szűrés és Rendezés</button>
        </form>
    </div>

    <div class="posts-container posts-grid-layout">
        <?php if (empty($recipes)): ?>
            <p style="grid-column: 1 / -1;">A szűrési feltételeknek egyetlen recept sem felel meg.</p>
        <?php else: ?>
            <?php foreach ($recipes as $recipe):
                    $cover_path = !empty($recipe['cover_image']) ? htmlspecialchars($recipe['cover_image']) : 'images/default_logo.png';
                    $post_class = $recipe['is_featured'] ? 'post-card kiemelt' : 'post-card';
                    ?>
                <a href="post.php?id=<?php echo $recipe['id']; ?>" class="<?php echo $post_class; ?> card-link">
        <?php if ($recipe['is_cooked']): ?>
                        <div class="cooked-overlay">
                            <div class="cooked-icon-circle"><i class="fas fa-check"></i></div>
                        </div>
        <?php endif; ?>
                    <div class="card-image-wrapper">
                        <div class="card-image" style="background-image: url('<?php echo $cover_path; ?>');"></div>
                    </div>
                    <div class="card-info">
                        <h3><?php echo htmlspecialchars($recipe['title']); ?></h3>
                        <div class="card-meta">
                            <div class="card-prep-time">
                                <i class="fas fa-clock"></i>
                                <span><?php echo htmlspecialchars($recipe['prep_time'] ?? 'N/A'); ?> perc</span>
                            </div>
                            <div class="card-rating">
        <?php
        $avg_rating = round($recipe['avg_rating']);
        for ($i = 1; $i <= 5; $i++):
            ?>
                                    <i class="<?php echo ($i <= $avg_rating) ? 'fas fa-star' : 'far fa-star'; ?>"></i>
                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                </a>
    <?php endforeach; ?>
<?php endif; ?>
    </div>
</div>

<?php
require_once './footer.php';
?>