// ── Generic collapsible-sidebar toggle ─────────────────────────────
// Works the same way at every screen size: clicking the toggle button
// hides/shows the sidebar. On wide screens the main content reflows to
// fill the freed space; on narrow screens the sidebar overlays the
// content instead (with a backdrop to tap-to-close), and defaults to
// hidden so it doesn't cover the screen on first load.
(function () {
    const toggle = document.getElementById('sidebar-toggle');
    const backdrop = document.getElementById('sidebar-backdrop');
    if (!toggle) return;

    const storageKey = toggle.dataset.storageKey || 'sidebarHidden';
    const mobileBreakpoint = 768;

    function setHidden(hidden) {
        document.body.classList.toggle('sidebar-hidden', hidden);
        toggle.setAttribute('aria-expanded', hidden ? 'false' : 'true');
        try {
            localStorage.setItem(storageKey, hidden ? '1' : '0');
        } catch (e) {
            /* localStorage unavailable (private mode, etc.) — ignore */
        }
    }

    let stored = null;
    try {
        stored = localStorage.getItem(storageKey);
    } catch (e) {
        /* ignore */
    }
    const initialHidden = stored !== null ? stored === '1' : window.innerWidth <= mobileBreakpoint;
    setHidden(initialHidden);

    toggle.addEventListener('click', function () {
        setHidden(!document.body.classList.contains('sidebar-hidden'));
    });

    backdrop?.addEventListener('click', function () {
        setHidden(true);
    });
})();
