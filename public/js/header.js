document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const mainNav = document.querySelector('.main-nav');
    const body = document.body; // To disable scroll when menu is open

    // Create an overlay element
    const overlay = document.createElement('div');
    overlay.classList.add('overlay');
    document.body.appendChild(overlay);

    function toggleMenu() {
        menuToggle.classList.toggle('active');
        mainNav.classList.toggle('active');
        overlay.classList.toggle('active'); // Toggle overlay
        body.classList.toggle('no-scroll'); // Disable/enable body scroll
    }

    menuToggle.addEventListener('click', toggleMenu);

    // Close menu when a link is clicked (useful for single-page apps or internal links)
    mainNav.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', () => {
            if (mainNav.classList.contains('active')) {
                toggleMenu(); // Close menu if it's open
            }
        });
    });

    // Close menu when clicking outside (on the overlay)
    overlay.addEventListener('click', toggleMenu);
});

// Add a global no-scroll class to body for when the menu is open
// This should be in your global CSS or just added here.
/* Example in global CSS or header.css:
body.no-scroll {
    overflow: hidden;
}
*/