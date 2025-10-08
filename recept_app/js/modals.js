document.addEventListener('DOMContentLoaded', () => {
    const allModals = Array.from(document.querySelectorAll('.modal'));
    if (allModals.length === 0) return;

    // A globális tinyMceConfig objektumot használjuk (a footer.php-ból)
    const tinyMceConfig = window.tinyMceConfig || {};

    // --- FLASH ÜZENETEK KEZELÉSE HIBA ESETÉN ---
    if (typeof uploadMessageType !== 'undefined' && uploadMessageType === 'error') {
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
    if (typeof uploadMessageType !== 'undefined' && uploadMessageType === 'success') {
        alert(uploadMessage);
    }

    // --- MODÁLIS ABLAKOKAT MEGNYITÓ GOMBOK ---

    // Menü szerkesztése
    const openMenuBtn = document.getElementById('openMenuEditModalBtn');
    if (openMenuBtn) { openMenuBtn.addEventListener('click', () => { document.getElementById('menuEditModal').style.display = 'block'; }); }

    // Footer szerkesztése
    const openFooterBtn = document.getElementById('openFooterEditModalBtn');
    if (openFooterBtn) { openFooterBtn.addEventListener('click', () => { document.getElementById('footerEditModal').style.display = 'block'; }); }

    // Profilkép cseréje
    const profileImgTrigger = document.getElementById('profile-image-trigger');
    if (profileImgTrigger) { profileImgTrigger.addEventListener('click', () => { document.getElementById('profilePictureModal').style.display = 'block'; }); }
    
    // Új bejegyzés
    const openNewPostBtn = document.getElementById('openNewPostModalBtn');
    if (openNewPostBtn) {
        openNewPostBtn.addEventListener('click', () => {
            const modal = document.getElementById('newPostModal');
            modal.style.display = 'block';
            tinymce.init({ ...tinyMceConfig, selector: '#new-content-textarea' });
        });
    }

    // Banner szerkesztése
    const editBannerBtn = document.getElementById('editBannerBtn');
    if (editBannerBtn) {
        editBannerBtn.addEventListener('click', (e) => {
            document.getElementById('banner_title_input').value = e.currentTarget.dataset.title;
            document.getElementById('bannerEditModal').style.display = 'block';
        });
    }

    // Logó szerkesztése
    const editLogoBtn = document.getElementById('editLogoBtn');
    if (editLogoBtn) { editLogoBtn.addEventListener('click', () => { document.getElementById('logoEditModal').style.display = 'block'; }); }

    // Általános bejegyzés szerkesztése
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', () => {
            const modal = document.getElementById('editModal');
            document.getElementById('edit-post-id').value = button.dataset.id;
            document.getElementById('edit-title').value = button.dataset.title;
            document.getElementById('edit-content').value = button.dataset.content;
            modal.style.display = 'block';
            tinymce.init({ ...tinyMceConfig, selector: '#edit-content' });
        });
    });
    
    // --- HIÁNYZÓ LOGIKÁK PÓTLÁSA ---

    // Bejegyzés törlése
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', (e) => {
            const modal = document.getElementById('deleteConfirmationModal');
            document.getElementById('delete-post-id-input').value = e.currentTarget.dataset.id;
            modal.style.display = 'block';
        });
    });
    
    // Email küldése
    document.querySelectorAll('.send-email-btn').forEach(button => {
        button.addEventListener('click', (e) => {
            const modal = document.getElementById('sendEmailModal');
            document.getElementById('email-to-input').value = e.currentTarget.dataset.email;
            modal.style.display = 'block';
        });
    });

    // Felhasználó törlése
    document.querySelectorAll('.delete-user-btn').forEach(button => {
        button.addEventListener('click', (e) => {
            const modal = document.getElementById('deleteUserConfirmationModal');
            const username = e.currentTarget.dataset.username;
            document.getElementById('delete-user-id-input').value = e.currentTarget.dataset.id;
            document.getElementById('delete-user-username-span').textContent = username;
            modal.style.display = 'block';
        });
    });

    // Felhasználó jelentése
    document.querySelectorAll('.report-user-btn').forEach(button => {
        button.addEventListener('click', (e) => {
            const modal = document.getElementById('reportUserModal');
            document.getElementById('reported-user-id-input').value = e.currentTarget.dataset.id;
            modal.style.display = 'block';
        });
    });


    // --- MODÁLIS ABLAKOK BEZÁRÁSA ---
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
    });
});