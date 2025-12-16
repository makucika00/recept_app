<?php
// search_results.php
session_start();
require_once 'db_config.php';

$search_query = $_GET['query'] ?? '';
$user_id = $_SESSION['user_id'] ?? null;
$results = [];

if (!empty($search_query)) {
    $search_term = '%' . $search_query . '%';
    try {
        $sql = "SELECT r.*, u.username,
                (SELECT COUNT(*) FROM user_cooked_recipes ucr WHERE ucr.recipe_id = r.id AND ucr.user_id = :user_id) as is_cooked
                FROM recipes r
                LEFT JOIN users u ON r.author_id = u.id 
                WHERE r.title LIKE :query";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':query' => $search_term, ':user_id' => $user_id]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Hiba kezelése
    }
}

require_once './header.php';
?>

<div class="search-results-container">
    <div class="results-header">
        <h1>Találatok a következőre: "<?php echo htmlspecialchars($search_query); ?>"</h1>
        <p><?php echo count($results); ?> találat</p>
    </div>

    <div class="posts-container posts-grid-layout">
        <?php if (empty($results)): ?>
            <p style="grid-column: 1 / -1;">Nincs a keresésnek megfelelő találat.</p>
        <?php else: ?>
            <?php
            foreach ($results as $recipe):
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