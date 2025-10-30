/**
 * Search Functionality
 * Provides live search suggestions and validation
 */

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');
    
    // Search form validation
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            if (searchInput.value.trim() === '') {
                e.preventDefault();
                alert('Please enter a search term');
                searchInput.focus();
                return false;
            }
        });
    }
    
    // Add focus effect to search input
    if (searchInput) {
        searchInput.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
            this.parentElement.style.transition = 'transform 0.3s ease';
        });
        
        searchInput.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    }
    
    // Animate user results
    const userResults = document.querySelectorAll('.user-result');
    userResults.forEach((result, index) => {
        result.style.opacity = '0';
        result.style.transform = 'translateX(-20px)';
        
        setTimeout(() => {
            result.style.transition = 'all 0.5s ease';
            result.style.opacity = '1';
            result.style.transform = 'translateX(0)';
        }, index * 100);
    });
    
    // Highlight search term in results
    const searchQuery = searchInput.value.trim();
    if (searchQuery) {
        userResults.forEach(result => {
            const textElements = result.querySelectorAll('h3, p');
            textElements.forEach(element => {
                const text = element.textContent;
                const regex = new RegExp('(' + searchQuery + ')', 'gi');
                element.innerHTML = text.replace(regex, '<span style="background: yellow; font-weight: bold;">$1</span>');
            });
        });
    }
});