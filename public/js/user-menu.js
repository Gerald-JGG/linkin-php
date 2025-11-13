document.addEventListener('DOMContentLoaded', function () {
    const btn  = document.getElementById('userMenuButton');
    const menu = document.getElementById('userMenu');

    if (!btn || !menu) {
        // Si no existen, no hacemos nada
        return;
    }

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        menu.classList.toggle('show');
    });

    document.addEventListener('click', function () {
        menu.classList.remove('show');
    });
});
