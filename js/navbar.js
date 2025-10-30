
document.addEventListener("DOMContentLoaded", function() {
    const toggleBtn = document.getElementById('menu-toggle');
    const navMenu = document.getElementById('navbar-menu');

    if (toggleBtn && navMenu) {
        toggleBtn.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }
});
