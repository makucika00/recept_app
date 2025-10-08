document.addEventListener('DOMContentLoaded', () => {
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
});