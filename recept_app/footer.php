<footer class="footer">
    <?php
    $footer_settings = [];
    try {
        if (!isset($conn)) {
            require_once 'db_config.php';
        }
        $stmt = $conn->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'footer_%'");
        $footer_settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (PDOException $e) {
        
    }
    ?>
    <div class="footer-left">
        <h3>Elérhetőségek</h3>
        <p><?php echo htmlspecialchars($footer_settings['footer_address'] ?? 'Cím: Adja meg a címet'); ?></p>
        <p><?php echo htmlspecialchars($footer_settings['footer_phone'] ?? 'Telefon: Adja meg a telefonszámot'); ?></p>
        <p><?php echo htmlspecialchars($footer_settings['footer_email'] ?? 'Email: Adja meg az email címet'); ?></p>
    </div>
    <div class="footer-right">
        <h3>Egyéb információk</h3>
        <p>© <?php echo date('Y'); ?> Minden jog fenntartva.</p>
    </div>
    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
        <button id="openFooterEditModalBtn" class="action-btn page-edit-btn" title="Footer szerkesztése"><i class="fas fa-pencil-alt"></i></button>
    <?php endif; ?>
</footer>
</main>

<div id="menuEditModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Menü szerkesztése</h2>
        <form action="save_settings.php" method="POST">
            <?php
            if (!isset($settings_menu)) {
                try {
                    $stmt = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'navbar_menu'");
                    $settings_menu = $stmt->fetch(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    $settings_menu = ['setting_value' => '[]'];
                }
            }
            $menu_items_modal = json_decode($settings_menu['setting_value'] ?? '[]', true);
            for ($i = 0; $i < 3; $i++):
                ?>
                <div class="form-group-inline" style="margin-bottom: 10px;">
                    <label>Menüpont <?php echo $i + 1; ?>:</label>
                    <input type="text" name="menu_text[]" placeholder="Szöveg" value="<?php echo htmlspecialchars($menu_items_modal[$i]['text'] ?? ''); ?>">
                    <input type="text" name="menu_href[]" placeholder="Link" value="<?php echo htmlspecialchars($menu_items_modal[$i]['href'] ?? ''); ?>">
                </div>
            <?php endfor; ?>
            <button type="submit" class="filter-btn" style="margin-top: 20px;">Menü Mentése</button>
        </form>
    </div>
</div>

<div id="footerEditModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Footer szerkesztése</h2>
        <form action="save_settings.php" method="POST">
            <div class="form-group"><label for="footer_address_modal">Cím:</label><input type="text" id="footer_address_modal" name="footer_address" value="<?php echo htmlspecialchars($footer_settings['footer_address'] ?? ''); ?>"></div>
            <div class="form-group"><label for="footer_phone_modal">Telefon:</label><input type="text" id="footer_phone_modal" name="footer_phone" value="<?php echo htmlspecialchars($footer_settings['footer_phone'] ?? ''); ?>"></div>
            <div class="form-group"><label for="footer_email_modal">Email:</label><input type="text" id="footer_email_modal" name="footer_email" value="<?php echo htmlspecialchars($footer_settings['footer_email'] ?? ''); ?>"></div>
            <button type="submit" class="filter-btn" style="margin-top: 20px;">Footer Mentése</button>
        </form>
    </div>
</div>

<div id="bannerEditModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Banner szerkesztése</h2>
        <div id="bannerUploadError" class="modal-error-message"></div>
        <form id="bannerUploadForm" action="upload_banner.php" method="POST" enctype="multipart/form-data">
            <div style="margin-bottom: 20px;"><label for="banner_title_input">Banner Cím:</label><input type="text" id="banner_title_input" name="banner_title" required></div>
            <div><label for="banner_image_upload">Válassz új banner képet (nem kötelező):</label><input type="file" id="banner_image_upload" name="banner_image" accept="image/*"></div>
            <p style="margin-top: 10px; font-size: 0.9em; color: #666;">Ha nem választasz új képet, csak a cím fog frissülni.</p>
            <button type="submit" style="margin-top: 20px;">Módosítások mentése</button>
        </form>
    </div>
</div>

<div id="logoEditModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Logó kép feltöltése</h2>
        <div id="logoUploadError" class="modal-error-message"></div>
        <form id="logoUploadForm" action="upload_logo.php" method="POST" enctype="multipart/form-data">
            <div><label for="logo_image_upload">Válassz új logó képet:</label><input type="file" id="logo_image_upload" name="logo_image" accept="image/png, image/svg+xml, image/jpeg" required></div>
            <p style="margin-top: 10px; font-size: 0.9em; color: #666;">Ajánlott formátum: PNG vagy SVG. Max fájlméret: 1MB.</p>
            <button type="submit" style="margin-top: 20px;">Feltöltés és frissítés</button>
        </form>
    </div>
</div>

<div id="newPostModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Új bejegyzés létrehozása</h2>
        <form id="newPostForm" action="create_post.php" method="POST" enctype="multipart/form-data">
            <div class="form-group"><label for="new-title">Cím:</label><input type="text" id="new-title" name="title" required></div>
            <div class="form-group"><label for="new-cover-image">Borítókép (max 2MB):</label><input type="file" id="new-cover-image" name="cover_image" accept="image/*"></div>
            <div class="form-group"><label for="new-content-textarea">Tartalom:</label><textarea name="content" id="new-content-textarea"></textarea></div>
            <button type="submit" class="filter-btn">Bejegyzés közzététele</button>
        </form>
    </div>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Bejegyzés szerkesztése</h2>
        <form id="editForm" action="edit_post.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" id="edit-post-id" name="post_id">
            <div><label for="edit-title">Cím:</label><input type="text" id="edit-title" name="title" required></div>
            <div class="form-group"><label for="edit-cover-image">Borítókép cseréje (max 2MB):</label><input type="file" id="edit-cover-image" name="cover_image" accept="image/*"></div>
            <div><label for="edit-content">Tartalom:</label><textarea id="edit-content" name="content"></textarea></div>
            <button type="submit">Módosítások mentése</button>
        </form>
    </div>
</div>

<div id="deleteModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Törlés megerősítése</h2>
        <p>Biztosan törölni szeretnéd ezt a bejegyzést?</p>
        <form id="deleteForm" action="delete_post.php" method="POST">
            <input type="hidden" id="delete-post-id" name="post_id">
            <button type="submit" class="danger-btn">Igen, törlés</button>
            <button type="button" class="cancel-btn">Mégse</button>
        </form>
    </div>
</div>

<div id="emailModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Email küldése</h2>
        <form action="send_email.php" method="POST">
            <div class="form-group"><label for="email-to">Címzett:</label><input type="email" id="email-to" name="email_to" readonly></div>
            <div class="form-group"><label for="email-subject">Tárgy:</label><input type="text" id="email-subject" name="subject" required></div>
            <div class="form-group"><label for="email-message">Üzenet:</label><textarea id="email-message" name="message" rows="8" required></textarea></div>
            <button type="submit" class="filter-btn">Küldés</button>
        </form>
    </div>
</div>

<div id="deleteUserModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Felhasználó törlésének megerősítése</h2>
        <p>Biztosan törölni szeretnéd a(z) <strong id="delete-username"></strong> nevű felhasználót? Ezzel a felhasználó **összes bejegyzése is véglegesen törlődik!**</p>
        <form id="deleteUserForm" action="delete_user.php" method="POST">
            <input type="hidden" id="delete-user-id" name="user_id">
            <button type="submit" class="danger-btn">Igen, véglegesen törlöm</button>
            <button type="button" class="cancel-btn">Mégse</button>
        </form>
    </div>
</div>

<div id="profilePictureModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Profilkép cseréje</h2>
        <div id="profileUploadError" class="modal-error-message"></div>
        <form action="upload_profile_picture.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="profile_image" accept="image/*" required>
            <button type="submit" class="filter-btn" style="margin-top:15px;">Feltöltés</button>
        </form>
    </div>
</div>

<div id="reportUserModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Felhasználó jelentése</h2>
        <form action="report_user.php" method="POST">
            <input type="hidden" id="reported-user-id" name="reported_user_id">
            <div class="form-group">
                <label for="report-reason">A jelentés oka:</label>
                <textarea id="report-reason" name="reason" rows="5" required></textarea>
            </div>
            <button type="submit" class="filter-btn">Jelentés elküldése</button>
        </form>
    </div>
</div>


<script src="https://cdn.tiny.cloud/1/vv97jvd58917tzz57f6jud0a25oxauu9j799tpe7df0een7y/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
    selector: '#edit-content-textarea',
            plugins: 'autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
            toolbar: 'undo redo | blocks | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | image link | help',
            images_upload_url: 'upload_image.php',
            automatic_uploads: true,
            file_picker_types: 'image',
            images_upload_base_path: '/recept_app'
    });</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = document.getElementById('menu-toggle');
    const mainMenu = document.getElementById('main-menu');
    if (menuToggle && mainMenu) {
    menuToggle.addEventListener('click', () => {
    mainMenu.classList.toggle('active');
    menuToggle.classList.toggle('active'); // MÓDOSÍTÁS
    });
    }
    // --- 1. KERESŐ ÉS LIVE SEARCH MŰKÖDÉSE ---
    const searchContainer = document.querySelector('.search-container');
    const searchBtn = document.querySelector('.search-btn');
    const searchInput = document.querySelector('.search-input');
    const liveSearchResultsContainer = document.getElementById('liveSearchResults');
    if (searchContainer && searchBtn && searchInput) {
    const ICON_SEARCH = '<i class="fas fa-search"></i>';
    const ICON_CLOSE = '<i class="fas fa-times"></i>';
    searchBtn.innerHTML = ICON_SEARCH;
    searchBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    searchContainer.classList.toggle('active');
    if (searchContainer.classList.contains('active')) {
    searchInput.focus();
    searchBtn.innerHTML = ICON_CLOSE;
    } else {
    searchBtn.innerHTML = ICON_CLOSE;
    }
    });
    if (liveSearchResultsContainer) {
    searchInput.addEventListener('input', function() {
    const query = this.value;
    if (query.length < 2) {
    liveSearchResultsContainer.innerHTML = '';
    liveSearchResultsContainer.style.display = 'none';
    return;
    }
    fetch('live_search.php?query=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
            liveSearchResultsContainer.innerHTML = '';
            if (data.length > 0) {
            data.forEach(item => {
            const link = document.createElement('a');
            link.href = 'search_results.php?query=' + encodeURIComponent(item.title);
            link.textContent = item.title;
            liveSearchResultsContainer.appendChild(link);
            });
            liveSearchResultsContainer.style.display = 'block';
            } else {
            liveSearchResultsContainer.style.display = 'none';
            }
            });
    });
    }
    }

    // --- 2. FELTÖLTÉSI ÜZENETEK KEZELÉSE ---
    const uploadMessage = <?php echo json_encode($upload_message ?? null); ?>;
    const uploadMessageType = <?php echo json_encode($upload_message_type ?? null); ?>;
    const sourceModal = <?php echo json_encode($_SESSION['source_modal'] ?? null); ?>;
<?php unset($_SESSION['source_modal']); ?>
    if (uploadMessageType === 'error') {
    let errorModal, errorDiv;
    if (sourceModal === 'banner') {
    errorModal = document.getElementById('bannerEditModal');
    errorDiv = document.getElementById('bannerUploadError');
    } else if (sourceModal === 'logo') {
    errorModal = document.getElementById('logoEditModal');
    errorDiv = document.getElementById('logoUploadError');
    } else if (sourceModal === 'profile') {
    errorModal = document.getElementById('profilePictureModal');
    errorDiv = document.getElementById('profileUploadError');
    }
    if (errorModal && errorDiv) {
    errorDiv.textContent = uploadMessage;
    errorDiv.style.display = 'block';
    errorModal.style.display = 'block';
    }
    }
    if (uploadMessageType === 'success') {
    alert(uploadMessage);
    }

    // --- 3. MODÁLIS ABLAKOK NYITÁSA ---
    document.getElementById('openMenuEditModalBtn')?.addEventListener('click', () => { document.getElementById('menuEditModal').style.display = 'block'; });
    document.getElementById('openFooterEditModalBtn')?.addEventListener('click', () => { document.getElementById('footerEditModal').style.display = 'block'; });
    document.getElementById('profile-image-trigger')?.addEventListener('click', () => { document.getElementById('profilePictureModal').style.display = 'block'; });
    document.getElementById('openNewPostModalBtn')?.addEventListener('click', () => {
    const modal = document.getElementById('newPostModal');
    modal.style.display = 'block';
    tinymce.init({
    selector: '#new-content-textarea',
            height: 400,
            plugins: 'autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
            toolbar: 'undo redo | blocks | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | image link | help',
            images_upload_url: 'upload_image.php',
            automatic_uploads: true,
            file_picker_types: 'image',
            images_upload_base_path: '/recept_app'
    });
    });
    document.getElementById('editBannerBtn')?.addEventListener('click', (e) => {
    document.getElementById('banner_title_input').value = e.currentTarget.dataset.title;
    document.getElementById('bannerEditModal').style.display = 'block';
    });
    document.getElementById('editLogoBtn')?.addEventListener('click', () => { document.getElementById('logoEditModal').style.display = 'block'; });
    document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', () => {
    const modal = document.getElementById('editModal');
    document.getElementById('edit-post-id').value = button.dataset.id;
    document.getElementById('edit-title').value = button.dataset.title;
    document.getElementById('edit-content').value = button.dataset.content;
    modal.style.display = 'block';
    tinymce.init({
    selector: '#edit-content',
            height: 400,
            plugins: 'autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
            toolbar: 'undo redo | blocks | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | image link | help',
            images_upload_url: 'upload_image.php',
            automatic_uploads: true,
            file_picker_types: 'image',
            images_upload_base_path: '/recept_app'
    });
    });
    });
    document.querySelectorAll('.delete-btn').forEach(button => {
    button.addEventListener('click', () => {
    document.getElementById('delete-post-id').value = button.dataset.id;
    document.getElementById('deleteModal').style.display = 'block';
    });
    });
    document.querySelectorAll('.send-email-btn').forEach(button => {
    button.addEventListener('click', () => {
    document.getElementById('email-to').value = button.dataset.email;
    document.getElementById('emailModal').style.display = 'block';
    });
    });
    document.querySelectorAll('.delete-user-btn').forEach(button => {
    button.addEventListener('click', () => {
    document.getElementById('delete-user-id').value = button.dataset.id;
    document.getElementById('delete-username').textContent = button.dataset.username;
    document.getElementById('deleteUserModal').style.display = 'block';
    });
    });
    document.querySelectorAll('.report-user-btn').forEach(button => {
    button.addEventListener('click', () => {
    document.getElementById('reported-user-id').value = button.dataset.id;
    document.getElementById('reportUserModal').style.display = 'block';
    });
    });
    // --- 4. POST.PHP OLDAL NÉZETVÁLTÁSA ---
    const editPostBtnOnPage = document.getElementById('editPostBtn');
    const cancelEditBtnOnPage = document.getElementById('cancelEditBtn');
    const postView = document.getElementById('post-view');
    const postEdit = document.getElementById('post-edit');
    if (editPostBtnOnPage && postView && postEdit) {
    editPostBtnOnPage.addEventListener('click', () => {
    postView.style.display = 'none';
    postEdit.style.display = 'block';
    });
    }
    if (cancelEditBtnOnPage && postView && postEdit) {
    cancelEditBtnOnPage.addEventListener('click', () => {
    postView.style.display = 'block';
    postEdit.style.display = 'none';
    });
    }

    // --- 5. AJAX-VEZÉRELT GOMBOK (KIEMELÉS, KÖVETÉS) ---
    document.querySelectorAll('.feature-toggle-btn').forEach(button => {
    button.addEventListener('click', (e) => {
    e.preventDefault();
    const postId = button.dataset.id;
    const formData = new FormData();
    formData.append('post_id', postId);
    fetch('toggle_feature.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
            if (data.success) {
            button.classList.toggle('featured', data.new_status == 1);
            const postEntry = button.closest('.post-entry, .single-post, .post-card');
            if (postEntry) {
            postEntry.classList.toggle('kiemelt', data.new_status == 1);
            }
            } else {
            alert('Hiba: ' + (data.message || 'Ismeretlen hiba történt.'));
            }
            });
    });
    });
    document.getElementById('follow-toggle-btn')?.addEventListener('click', function() {
    const button = this;
    const userId = button.dataset.id;
    const formData = new FormData();
    formData.append('user_id', userId);
    fetch('toggle_follow.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
            if (data.success) {
            button.classList.toggle('following', data.is_following);
            } else {
            alert(data.message || 'Hiba történt.');
            }
            });
    });
    // --- 6. ÁLTALÁNOS BEZÁRÁS LOGIKA ---
    const allModals = Array.from(document.querySelectorAll('.modal'));
    document.querySelectorAll('.close-btn, .cancel-btn').forEach(button => {
    button.addEventListener('click', (e) => {
    const modalToClose = e.target.closest('.modal');
    if (modalToClose) {
    modalToClose.style.display = 'none';
    if (modalToClose.id === 'newPostModal') { tinymce.remove('#new-content-textarea'); }
    if (modalToClose.id === 'editModal') { tinymce.remove('#edit-content'); }
    }
    });
    });
    window.addEventListener('click', (event) => {
    allModals.forEach(modal => {
    if (event.target == modal) {
    modal.style.display = 'none';
    if (modal.id === 'newPostModal') { tinymce.remove('#new-content-textarea'); }
    if (modal.id === 'editModal') { tinymce.remove('#edit-content'); }
    }
    });
    const searchItem = document.querySelector('.menu-search-item');
    if (searchItem && !searchItem.contains(event.target)) {
    if (searchContainer?.classList.contains('active')) {
    searchContainer.classList.remove('active');
    if (searchBtn) { searchBtn.innerHTML = ICON_SEARCH; }
    }
    if (liveSearchResultsContainer) {
    liveSearchResultsContainer.style.display = 'none';
    }
    }
    });
// ===================================================
    // ## BEVÁSárlóLISTA LOGIKA ##
    // ===================================================
    const shoppingListContainer = document.getElementById('shopping-list-container');
    if (shoppingListContainer) {
    const listHeader = shoppingListContainer.querySelector('.shopping-list-header');
    const listItemsUl = document.getElementById('shopping-list-items');
    const itemCounter = shoppingListContainer.querySelector('.item-counter');
    const clearListBtn = document.getElementById('clear-list-btn');
    const toast = document.getElementById('toast-notification');
    const toastMessage = document.getElementById('toast-message');
    const ingredientsList = document.querySelector('.ingredients-list');
    const addAllBtn = document.getElementById('add-all-ingredients-btn');
    const arrowIcon = shoppingListContainer.querySelector('.shopping-list-arrow');
    let shoppingList = JSON.parse(localStorage.getItem('shoppingList')) || [];
    const saveList = () => {
    localStorage.setItem('shoppingList', JSON.stringify(shoppingList));
    };
    const renderList = () => {
    listItemsUl.innerHTML = '';
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
    if (item.checked) li.classList.add('checked');
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
    }, 3000);
    };
    if (ingredientsList) {
    ingredientsList.addEventListener('click', function (e) {
    if (e.target.classList.contains('ingredient-add-to-list')) {
    const li = e.target.closest('li');
    const quantity = li.querySelector('.quantity').textContent.trim();
    const unit = li.querySelector('.unit').textContent.trim();
    const name = e.target.textContent.trim();
    const fullText = `${quantity} ${unit} ${name}`;
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

    if (addAllBtn && ingredientsList) {
    addAllBtn.addEventListener('click', () => {
    const allIngredientElements = ingredientsList.querySelectorAll('li');
    let addedCount = 0;
    let totalIngredients = allIngredientElements.length;
    allIngredientElements.forEach(li => {
    const quantity = li.querySelector('.quantity')?.textContent.trim();
    const unit = li.querySelector('.unit')?.textContent.trim();
    const name = li.querySelector('.name')?.textContent.trim();
    if (quantity && unit && name) {
    const fullText = `${quantity} ${unit} ${name}`;
    // Csak akkor adja hozzá, ha még nincs a listán
    if (!shoppingList.some(item => item.text === fullText)) {
    shoppingList.push({ text: fullText, checked: false });
    addedCount++;
    }
    }
    });
    if (addedCount > 0) {
    saveList();
    renderList();
    showToast(`${addedCount} új tétel hozzáadva a listához!`);
    } else {
    showToast('Minden hozzávaló már a listádon van.');
    }

    // GOMB ÁTALAKÍTÁSA ÉS LETILTÁSA (MÓDOSÍTVA)
    if (totalIngredients > 0) {
    addAllBtn.classList.add('added-to-list');
    // A szöveget a "btn-text" span-be tesszük
    addAllBtn.innerHTML = '<i class="fas fa-check"></i><span class="btn-text">Bevásárló listában</span>';
    addAllBtn.disabled = true;
    }
    });
    }


    listHeader.addEventListener('click', () => {
    shoppingListContainer.classList.toggle('collapsed');
    arrowIcon.classList.toggle('rotated');
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
    renderList();
    if (shoppingListContainer.classList.contains('collapsed')) {
    arrowIcon.classList.add('rotated');
    }
    }
    });
</script>

<div id="toast-notification">
    <i class="fas fa-check-circle"></i>
    <span id="toast-message"></span>
</div>

<div id="shopping-list-container" class="collapsed">
    <div class="shopping-list-header">
        <h3>
            <i class="fas fa-shopping-basket"></i>
            Bevásárló listám
            <i class="fa-solid fa-angle-down shopping-list-arrow"></i>
        </h3>
        <span class="item-counter">0</span>
    </div>
    <div class="shopping-list-body">
        <ul id="shopping-list-items">
        </ul>
        <button id="clear-list-btn">Lista törlése</button>
    </div>
</div>

</body>
</html>