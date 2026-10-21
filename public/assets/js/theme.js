/**
 * theme.js - Complete Dark Mode & Light Mode Controller with Persistence for SMCC Connect
 * Handles:
 *  - Immediate theme application on documentElement to prevent flash
 *  - Theme toggle functionality (window.toggleTheme)
 *  - Accessible button updates (icons, aria-label, title) across desktop and mobile
 *  - Reliable localStorage persistence with fallback to system preference
 */
(function() {
    'use strict';

    var THEME_STORAGE_KEY = 'smcc_theme_preference';
    var THEME_DARK = 'dark';
    var THEME_LIGHT = 'light';

    /**
     * Safely read theme from localStorage or fallback to system preference
     */
    function getStoredTheme() {
        try {
            var stored = localStorage.getItem(THEME_STORAGE_KEY);
            if (stored === THEME_DARK || stored === THEME_LIGHT) {
                return stored;
            }
        } catch (e) {
            console.warn('localStorage read error:', e);
        }

        // Check system preference
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return THEME_DARK;
        }

        return THEME_LIGHT;
    }

    /**
     * Apply theme attributes and classes to the root element
     */
    function applyTheme(theme) {
        var root = document.documentElement;
        if (theme === THEME_DARK) {
            root.setAttribute('data-bs-theme', 'dark');
            root.classList.add('dark-theme');
            root.classList.remove('light-theme');
        } else {
            root.setAttribute('data-bs-theme', 'light');
            root.classList.remove('dark-theme');
            root.classList.add('light-theme');
        }

        updateToggleButtons(theme);
    }

    /**
     * Synchronize all theme toggle buttons across the UI
     */
    function updateToggleButtons(activeTheme) {
        var current = activeTheme || (document.documentElement.getAttribute('data-bs-theme') === THEME_DARK ? THEME_DARK : THEME_LIGHT);
        var buttons = document.querySelectorAll('.theme-toggle-btn');

        buttons.forEach(function(btn) {
            var icon = btn.querySelector('.theme-toggle-icon');
            if (current === THEME_DARK) {
                if (icon) {
                    icon.className = 'theme-toggle-icon bi bi-sun-fill';
                }
                btn.setAttribute('title', 'Switch to Light Mode');
                btn.setAttribute('aria-label', 'Switch to Light Mode');
            } else {
                if (icon) {
                    icon.className = 'theme-toggle-icon bi bi-moon-fill';
                }
                btn.setAttribute('title', 'Switch to Dark Mode');
                btn.setAttribute('aria-label', 'Switch to Dark Mode');
            }
        });
    }

    /**
     * Global Toggle Function
     */
    window.toggleTheme = function() {
        var currentTheme = document.documentElement.getAttribute('data-bs-theme') === THEME_DARK ? THEME_DARK : THEME_LIGHT;
        var newTheme = (currentTheme === THEME_DARK) ? THEME_LIGHT : THEME_DARK;

        try {
            localStorage.setItem(THEME_STORAGE_KEY, newTheme);
        } catch (e) {
            console.warn('localStorage write error:', e);
        }

        applyTheme(newTheme);
    };

    // Public hook to allow re-syncing buttons if DOM is dynamically changed
    window.updateThemeToggleButtons = updateToggleButtons;

    // Apply saved or system theme immediately
    var initialTheme = getStoredTheme();
    applyTheme(initialTheme);

    // Sync button icons once DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            updateToggleButtons();
        });
    } else {
        updateToggleButtons();
    }
})();
