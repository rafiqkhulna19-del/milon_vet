const themeToggle = document.getElementById('themeToggle');

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
