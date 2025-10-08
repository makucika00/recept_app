document.addEventListener('DOMContentLoaded', () => {
    // --- KIEMELÉS GOMB MŰKÖDÉSE ---
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

    // --- KÖVETÉS GOMB MŰKÖDÉSE ---
    const followBtn = document.getElementById('follow-toggle-btn');
    if (followBtn) {
        followBtn.addEventListener('click', function() {
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
    }
});