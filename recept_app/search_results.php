<?php
// search_results.php
require_once 'db_config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Alapértelmezett értékek
$search_query = $_GET['query'] ?? '';
$filter_featured = isset($_GET['filter_featured']);
$sort_by = $_GET['sort_by'] ?? 'date_desc';
$results = [];

// Logó útvonalának lekérése az alapértelmezett képhez
$logo_image_path = 'images/default_logo.png';
try {
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'logo_image'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result && !empty($result['setting_value'])) {
        $logo_image_path = htmlspecialchars($result['setting_value']);
    }
} catch (PDOException $e) {
    
}


if (!empty($search_query)) {
    $search_term = '%' . $search_query . '%';

    $sql = "SELECT posts.*, users.username 
            FROM posts 
            LEFT JOIN users ON posts.author_id = users.id 
            WHERE (posts.title LIKE :query OR posts.content LIKE :query)";

    $params = [':query' => $search_term];

    if ($filter_featured) {
        $sql .= " AND posts.is_featured = 1";
    }

    $order_clause = " ORDER BY posts.created_at DESC";
    switch ($sort_by) {
        case 'date_asc': $order_clause = " ORDER BY posts.created_at ASC";
            break;
        case 'title_asc': $order_clause = " ORDER BY posts.title ASC";
            break;
        case 'title_desc': $order_clause = " ORDER BY posts.title DESC";
            break;
    }
    $sql .= $order_clause;

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        
    }
}

require_once './header.php';
?>

<div class="search-results-container">
    <div class="results-header">
        <h1>Találatok a következőre: "<?php echo htmlspecialchars($search_query); ?>"</h1>
        <p><?php echo count($results); ?> találat</p>
    </div>

    <div class="search-controls">
        <form action="search_results.php" method="GET">
            <input type="hidden" name="query" value="<?php echo htmlspecialchars($search_query); ?>">
            <div class="filter-group">
                <input type="checkbox" name="filter_featured" id="filter_featured" <?php if ($filter_featured) echo 'checked'; ?>>
                <label for="filter_featured">Csak a kiemelt találatok</label>
            </div>
            <div class="filter-group">
                <label for="sort_by">Rendezés:</label>
                <select name="sort_by" id="sort_by">
                    <option value="date_desc" <?php if ($sort_by == 'date_desc') echo 'selected'; ?>>Dátum szerint (legújabb elöl)</option>
                    <option value="date_asc" <?php if ($sort_by == 'date_asc') echo 'selected'; ?>>Dátum szerint (legrégebbi elöl)</option>
                    <option value="title_asc" <?php if ($sort_by == 'title_asc') echo 'selected'; ?>>Cím szerint (A-Z)</option>
                    <option value="title_desc" <?php if ($sort_by == 'title_desc') echo 'selected'; ?>>Cím szerint (Z-A)</option>
                </select>
            </div>
            <button type="submit" class="filter-btn">Szűrés és Rendezés</button>
        </form>
    </div>

    <div class="posts-container posts-grid-layout">
        <?php if (empty($results)): ?>
            <p style="grid-column: 1 / -1;">Nincs a keresésnek megfelelő találat.</p>
        <?php else: ?>
            <?php
            foreach ($results as $post):
                $cover_path = !empty($post['cover_image']) ? htmlspecialchars($post['cover_image']) : $logo_image_path;
                // Egyszerűsített class: csak 'post-card' vagy 'post-card kiemelt' lesz
                $post_class = $post['is_featured'] ? 'post-card kiemelt' : 'post-card';
                ?>

                <div class="<?php echo $post_class; ?>">
                    <div class="card-image-wrapper">
                        <a href="post.php?id=<?php echo $post['id']; ?>" class="card-image-link">
                            <div class="card-image" style="background-image: url('<?php echo $cover_path; ?>');"></div>
                        </a>
                    </div>
                    <div class="card-info">
                        <div class="card-admin-actions">
                            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                                <button class="action-btn feature-toggle-btn <?php if ($post['is_featured']) echo 'featured'; ?>" data-id="<?php echo $post['id']; ?>" title="Kiemelés/Visszavonás"><i class="fas fa-star"></i></button>
                            <?php endif; ?>
        <?php if (isset($_SESSION['user_id']) && (($_SESSION['is_admin'] ?? 0) == 1 || $_SESSION['user_id'] == $post['author_id'])): ?>
                                <button class="action-btn edit-btn" data-id="<?php echo $post['id']; ?>" data-title="<?php echo htmlspecialchars($post['title']); ?>" data-content="<?php echo htmlspecialchars($post['content']); ?>" title="Szerkesztés"><i class="fas fa-pencil-alt"></i></button>
                                <button class="action-btn delete-btn" data-id="<?php echo $post['id']; ?>" title="Törlés"><i class="fas fa-times"></i></button>
                        <?php endif; ?>
                        </div>
        <?php if (!empty($post['username'])): ?>
                            <div class="post-author">
                                <i class="fas fa-user"></i>
                                <a href="profile.php?id=<?php echo $post['author_id']; ?>">
            <?php echo htmlspecialchars($post['username']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        <h3><a href="post.php?id=<?php echo $post['id']; ?>" class="post-title-link"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                        <?php
                        $decoded_content = html_entity_decode($post['content']);
                        $plain_content = strip_tags($decoded_content);
                        $excerpt_length = 120;
                        $excerpt = (mb_strlen($plain_content) > $excerpt_length) ? mb_substr($plain_content, 0, $excerpt_length) . '...' : $plain_content;
                        ?>
                        <p><?php echo htmlspecialchars($excerpt); ?></p>
                        <a href="post.php?id=<?php echo $post['id']; ?>" class="read-more-link">Tovább olvasom →</a>
                        <small>Publikálva: <?php echo date('Y. m. d.', strtotime($post['created_at'])); ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
<?php endif; ?>
    </div>
</div>

<?php
require_once './footer.php';
?>