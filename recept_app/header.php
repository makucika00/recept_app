<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_config.php';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ReceptApp01</title>
        <meta name="description" content="ReceptApp01">
        <link rel="stylesheet" type="text/css" href="css/styles.css">
        <link rel="stylesheet" type="text/css" href="dynamic_styles.php">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    </head>
    <body>
        <nav class="navbar">
            <div class="logo-container">
                <?php
                    $logo_image_path = 'images/default_logo.png';
                    try {
                        $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'logo_image'");
                        $stmt->execute();
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($result && !empty($result['setting_value'])) {
                            $logo_image_path = htmlspecialchars($result['setting_value']);
                        }
                    } catch (PDOException $e) {}
                ?>
                <a href="index.php"><img src="<?php echo $logo_image_path; ?>" alt="Logó" class="logo-image"></a>
                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                    <button id="editLogoBtn" class="action-btn logo-edit-btn" title="Logó szerkesztése"><i class="fas fa-pencil-alt"></i></button>
                <?php endif; ?>
            </div>

            <button class="menu-toggle" id="menu-toggle" aria-label="Toggle menu">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>

            <ul class="menu" id="main-menu">
                <li class="menu-search-item">
                    <form action="search_results.php" method="GET" class="search-form">
                        <div class="search-container">
                            <input type="text" name="query" placeholder="Keresés..." class="search-input" required autocomplete="off">
                            <button type="submit" class="search-btn"><i class="fa fa-search"></i></button>
                        </div>
                    </form>
                    <div id="liveSearchResults" class="search-results-live"></div>
                </li>
                
                <?php
                    $settings_menu = [];
                    try {
                        $stmt = $conn->query("SELECT setting_key, setting_value FROM settings WHERE setting_key = 'navbar_menu'");
                        $settings_menu = $stmt->fetch(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {}
                    
                    $menu_items = json_decode($settings_menu['setting_value'] ?? '[]', true);
                    foreach ($menu_items as $item):
                ?>
                    <li><a href="<?php echo htmlspecialchars($item['href']); ?>"><?php echo htmlspecialchars($item['text']); ?></a></li>
                <?php endforeach; ?>

                <?php
                if (isset($_SESSION['user_id'])) {
                    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
                        echo '<li><a href="admin_panel.php" class="button-link">Beállítások</a></li>';
                    }
                ?>
                    <li><a href="profile.php" title="Profil" class="button-link profile-icon"><i class="fas fa-user"></i></a></li>
                    <li><a href="logout.php" title="Kijelentkezés" class="button-link logout-btn"><i class="fas fa-right-from-bracket"></i></a></li>
                <?php
                } else {
                ?>
                    <li><a href="login.php" class="button-link">Bejelentkezés</a></li>
                <?php
                }
                ?>
            </ul>

            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                <button id="openMenuEditModalBtn" class="action-btn page-edit-btn" title="Menü szerkesztése"><i class="fas fa-pencil-alt"></i></button>
            <?php endif; ?>
        </nav>
        <div id="loginModal" class="modal">
            <div class="modal-content login-modal-content">
                <span class="close-btn">&times;</span>
                <div class="login-header">
                    <h2>Üdvözlünk újra!</h2>
                    <p>Jelentkezz be a fiókodba</p>
                </div>
                
                <div id="login-error-msg" class="modal-error-message" style="display: none;"></div>

                <form action="login.php" method="POST" class="login-form">
                    <div class="form-group">
                        <label for="modal_login_user"><i class="fas fa-user"></i> Felhasználónév vagy Email</label>
                        <input type="text" id="modal_login_user" name="login_user" class="form-control" required placeholder="Írd be a felhasználóneved...">
                    </div>
                    
                    <div class="form-group">
                        <label for="modal_login_password"><i class="fas fa-lock"></i> Jelszó</label>
                        <input type="password" id="modal_login_password" name="login_password" class="form-control" required placeholder="Írd be a jelszavad...">
                    </div>

                    <div class="forgot-password-link">
                        <a href="forgot_password.php">Elfelejtetted a jelszavad?</a>
                    </div>

                    <button type="submit" class="submit-btn login-submit-btn">Bejelentkezés</button>
                </form>

                <div class="login-footer">
                    <p>Még nincs fiókod? <a href="register.php">Regisztrálj itt!</a></p>
                </div>
            </div>
        </div>
        <main>