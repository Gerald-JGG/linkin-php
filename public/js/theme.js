// Manejo de tema claro/oscuro con localStorage
(function() {
    const THEME_KEY = 'aventones_theme';

    function applyTheme(theme) {
        if (theme === 'dark') {
            document.body.classList.add('dark-mode');
        } else {
            document.body.classList.remove('dark-mode');
        }
    }

    // Al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        const saved = localStorage.getItem(THEME_KEY);
        if (saved === 'dark') {
            applyTheme('dark');
        } else if (saved === 'light') {
            applyTheme('light');
        }
    });

    // Hacer global para usar desde otros scripts
    window.setTheme = function(theme) {
        if (theme !== 'dark') {
            theme = 'light';
        }
        localStorage.setItem(THEME_KEY, theme);
        applyTheme(theme);
    };

    window.getTheme = function() {
        return document.body.classList.contains('dark-mode') ? 'dark' : 'light';
    };
})();
