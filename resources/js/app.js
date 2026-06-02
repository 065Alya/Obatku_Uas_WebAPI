import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// ─── PWA: Service Worker + Push + Offline Queue ───
import './pwa.js';

// ─── ObatKu Global Helpers ───

/**
 * Toggle mobile sidebar
 */
window.toggleSidebar = function () {
    const sidebar = document.getElementById('obk-sidebar');
    const overlay = document.getElementById('obk-sidebar-overlay');
    if (sidebar && overlay) {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }
};

/**
 * Close all dropdown menus
 */
window.closeDropdowns = function () {
    document.querySelectorAll('[data-dropdown]').forEach(el => {
        el.classList.add('hidden');
    });
};

/**
 * Flash message auto-dismiss
 */
document.addEventListener('DOMContentLoaded', () => {
    const flashMessages = document.querySelectorAll('[data-flash]');
    flashMessages.forEach(msg => {
        setTimeout(() => {
            msg.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            msg.style.opacity = '0';
            msg.style.transform = 'translateY(-8px)';
            setTimeout(() => msg.remove(), 400);
        }, 4000);
    });
});

/**
 * Confirm delete modal
 */
window.confirmDelete = function (formId, itemName = 'item ini') {
    if (confirm(`Apakah Anda yakin ingin menghapus ${itemName}?`)) {
        document.getElementById(formId).submit();
    }
};
