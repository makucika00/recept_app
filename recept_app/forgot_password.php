<?php
require_once 'header.php'; // Betölti a stílusokat és a menüt

$step = 1; // 1: űrlap, 2: sikeres üzenet

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    // Itt történne az email küldés logikája (PHPMailer, adatbázis token mentés stb.)
    // Most csak szimuláljuk a sikert.
    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $step = 2;
    } else {
        $error = "Kérjük, adjon meg egy érvényes e-mail címet.";
    }
}
?>

<div class="main-content-area">
    <div class="forgot-password-container">
        <?php if ($step == 1): ?>
            <div class="forgot-password-icon">
                <i class="fas fa-key"></i>
            </div>
            <h2>Elfelejtett jelszó</h2>
            <p style="color: #666; margin-bottom: 30px;">
                Add meg az e-mail címedet, amivel regisztráltál, és küldünk egy linket a jelszó visszaállításához.
            </p>

            <?php if (isset($error)): ?>
                <div class="modal-error-message" style="display:block; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="forgot_password.php" method="POST" class="login-form" style="padding: 0;">
                <div class="form-group" style="text-align: left;">
                    <label for="email"><i class="fas fa-envelope"></i> E-mail cím</label>
                    <input type="email" id="email" name="email" class="form-control" required placeholder="pelda@email.hu">
                </div>
                <button type="submit" class="submit-btn login-submit-btn">Link küldése</button>
            </form>
            <div style="margin-top: 20px;">
                <a href="index.php" style="color: #888; text-decoration: none;">&larr; Vissza a főoldalra</a>
            </div>

        <?php else: ?>
            <div class="forgot-password-icon" style="color: #4db057;">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2>Levél elküldve!</h2>
            <p style="color: #666; margin-bottom: 30px; line-height: 1.6;">
                Ha a megadott e-mail cím (<strong><?php echo htmlspecialchars($email); ?></strong>) szerepel a rendszerünkben, hamarosan megérkezik rá a jelszó-visszaállító link.
            </p>
            <p style="font-size: 0.9em; color: #999;">Kérjük, ellenőrizze a SPAM mappát is.</p>
            
            <a href="index.php" class="submit-btn login-submit-btn" style="display: inline-block; text-decoration: none; margin-top: 20px;">Vissza a főoldalra</a>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>