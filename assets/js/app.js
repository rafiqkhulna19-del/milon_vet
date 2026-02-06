const themeToggle = document.getElementById('themeToggle');
const sidebarToggle = document.getElementById('sidebarToggle');

const applyTheme = (theme) => {
    document.documentElement.setAttribute('data-bs-theme', theme);
    if (themeToggle) {
        themeToggle.innerHTML = theme === 'dark'
            ? '<i class="bi bi-brightness-high"></i> <span class="d-none d-md-inline">লাইট</span>'
            : '<i class="bi bi-moon-stars"></i> <span class="d-none d-md-inline">ডার্ক</span>';
    }
};

const storedTheme = localStorage.getItem('milon-theme') || 'light';
applyTheme(storedTheme);

if (themeToggle) {
    themeToggle.addEventListener('click', () => {
        const nextTheme = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        localStorage.setItem('milon-theme', nextTheme);
        applyTheme(nextTheme);
    });
}

const applySidebarState = (collapsed) => {
    document.body.classList.toggle('sidebar-collapsed', collapsed);
    localStorage.setItem('milon-sidebar', collapsed ? 'collapsed' : 'expanded');
};

const storedSidebar = localStorage.getItem('milon-sidebar') || 'expanded';
applySidebarState(storedSidebar === 'collapsed');

if (sidebarToggle) {
    sidebarToggle.addEventListener('click', () => {
        const isCollapsed = document.body.classList.contains('sidebar-collapsed');
        applySidebarState(!isCollapsed);
    });
}
