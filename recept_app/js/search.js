document.addEventListener('DOMContentLoaded', () => {
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
                searchBtn.innerHTML = ICON_SEARCH;
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

        window.addEventListener('click', (event) => {
            const searchItem = document.querySelector('.menu-search-item');
            if (searchItem && !searchItem.contains(event.target)) {
                if (searchContainer.classList.contains('active')) {
                    searchContainer.classList.remove('active');
                    searchBtn.innerHTML = ICON_SEARCH;
                }
                if (liveSearchResultsContainer) {
                    liveSearchResultsContainer.style.display = 'none';
                }
            }
        });
    }
});