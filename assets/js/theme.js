(function () {
    const savedTheme = localStorage.getItem('theme') || 'light';

    document.documentElement.setAttribute('data-theme', savedTheme);

    function setupThemeButton() {
        const button = document.getElementById('themeToggle');

        if (!button) return;

        function updateButton(theme) {
            if (theme === 'dark') {
                button.innerHTML = '<i class="bi bi-sun-fill"></i>';
                button.setAttribute('aria-label', 'Aktifkan Light Mode');
                button.setAttribute('title', 'Light Mode');
            } else {
                button.innerHTML = '<i class="bi bi-moon-stars-fill"></i>';
                button.setAttribute('aria-label', 'Aktifkan Dark Mode');
                button.setAttribute('title', 'Dark Mode');
            }
        }

        updateButton(savedTheme);

        button.addEventListener('click', function () {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);

            updateButton(newTheme);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupThemeButton);
    } else {
        setupThemeButton();
    }
})();