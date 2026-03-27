/**
 * Appearance/Theme management composable
 */

export function initializeTheme() {
    // Check for saved theme in localStorage or default to system preference
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    const theme = savedTheme || (prefersDark ? 'dark' : 'light');
    
    // Apply theme to document
    applyTheme(theme);
    
    // Listen for system theme changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (!localStorage.getItem('theme')) {
            applyTheme(e.matches ? 'dark' : 'light');
        }
    });
}

export function applyTheme(theme) {
    const root = document.documentElement;
    
    if (theme === 'dark') {
        root.classList.add('dark');
    } else {
        root.classList.remove('dark');
    }
    
    // Store preference
    localStorage.setItem('theme', theme);
}

export function toggleTheme() {
    const current = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
    const newTheme = current === 'dark' ? 'light' : 'dark';
    applyTheme(newTheme);
    return newTheme;
}

export function getCurrentTheme() {
    return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
}
