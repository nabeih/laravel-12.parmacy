// ── Notification bell dropdown ──────────────────────────────────────
(function () {
    const toggle = document.getElementById('notif-bell-toggle');
    const dropdown = document.getElementById('notif-bell-dropdown');
    if (!toggle || !dropdown) return;

    function close() {
        dropdown.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
    }

    function open() {
        dropdown.classList.add('open');
        toggle.setAttribute('aria-expanded', 'true');
    }

    toggle.addEventListener('click', function (e) {
        e.stopPropagation();
        dropdown.classList.contains('open') ? close() : open();
    });

    document.addEventListener('click', function (e) {
        if (!dropdown.contains(e.target) && e.target !== toggle) {
            close();
        }
    });
})();
