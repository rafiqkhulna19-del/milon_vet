const themeDropdown = document.getElementById('themeDropdown');
const themeButtons = document.querySelectorAll('[data-theme-value]');
const themeCheckIcons = document.querySelectorAll('.theme-check');
const sidebarToggle = document.getElementById('sidebarToggle');
const saveAppSettings = document.getElementById('saveAppSettings');
const sbExpanded = document.getElementById('sbExpanded');
const sbCompact = document.getElementById('sbCompact');
const sbHoverToggle = document.getElementById('sbHoverToggle');

const applyTheme = (theme) => {
    let resolved = theme;
    if (theme === 'system') {
        resolved = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    document.documentElement.setAttribute('data-bs-theme', resolved);
    themeCheckIcons.forEach((icon, idx) => {
        icon.classList.toggle('opacity-0', themeButtons[idx]?.dataset.themeValue !== theme);
    });
};

const storedTheme = localStorage.getItem('milon-theme') || 'light';
applyTheme(storedTheme);

if (themeButtons.length) {
    themeButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const value = btn.dataset.themeValue;
            localStorage.setItem('milon-theme', value);
            applyTheme(value);
        });
    });
}

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    const current = localStorage.getItem('milon-theme') || 'light';
    if (current === 'system') {
        applyTheme('system');
    }
});

const applySidebarMode = (mode) => {
    document.documentElement.classList.toggle('sidebar-compact', mode === 'compact');
    localStorage.setItem('milon-sidebar-mode', mode);
    if (sidebarToggle) {
        sidebarToggle.classList.toggle('is-compact', mode === 'compact');
    }
};

const storedMode = localStorage.getItem('milon-sidebar-mode') || 'expanded';
applySidebarMode(storedMode);

if (sidebarToggle) {
    sidebarToggle.addEventListener('click', () => {
        const compact = document.documentElement.classList.contains('sidebar-compact');
        applySidebarMode(compact ? 'expanded' : 'compact');
    });
}

const applyHoverMode = (enabled) => {
    document.documentElement.classList.toggle('sidebar-hover', enabled);
    localStorage.setItem('milon-sidebar-hover', enabled ? '1' : '0');
};

const storedHover = localStorage.getItem('milon-sidebar-hover') === '1';
applyHoverMode(storedHover);

if (sbExpanded && sbCompact) {
    sbExpanded.checked = storedMode === 'expanded';
    sbCompact.checked = storedMode === 'compact';
}
if (sbHoverToggle) {
    sbHoverToggle.checked = storedHover;
}

if (saveAppSettings) {
    saveAppSettings.addEventListener('click', () => {
        const mode = sbCompact?.checked ? 'compact' : 'expanded';
        applySidebarMode(mode);
        applyHoverMode(!!sbHoverToggle?.checked);
    });
}

const tooltipElements = document.querySelectorAll('.sidebar .nav-link[data-label]');
if (tooltipElements.length) {
    tooltipElements.forEach((el) => {
        el.addEventListener('mouseenter', () => {
            if (!document.documentElement.classList.contains('sidebar-compact')) return;
            el.classList.add('show-tooltip');
        });
        el.addEventListener('mouseleave', () => {
            el.classList.remove('show-tooltip');
        });
    });
}
