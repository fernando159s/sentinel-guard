/**
 * SecuriForm — JavaScript compartido
 * Bootstrap 5.3 y Chart.js 4.x se cargan desde CDN en el layout.
 */

'use strict';

const SecuriForm = {
    /**
     * Inicialización global.
     */
    init() {
        this.initSidebarToggle();
        this.initTooltips();
        this.initAutoHideAlerts();
    },

    /**
     * Toggle del sidebar en móvil.
     */
    initSidebarToggle() {
        const toggler = document.getElementById('sf-sidebar-toggle');
        const sidebar = document.getElementById('sf-sidebar');
        if (!toggler || !sidebar) return;

        toggler.addEventListener('click', () => {
            sidebar.classList.toggle('show');
        });

        document.addEventListener('click', (e) => {
            if (
                sidebar.classList.contains('show') &&
                !sidebar.contains(e.target) &&
                !toggler.contains(e.target)
            ) {
                sidebar.classList.remove('show');
            }
        });
    },

    /**
     * Activar tooltips de Bootstrap.
     */
    initTooltips() {
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(el => new bootstrap.Tooltip(el));
    },

    /**
     * Auto-ocultar alertas después de 5 segundos.
     */
    initAutoHideAlerts() {
        document.querySelectorAll('.alert-dismissible.sf-auto-hide').forEach(alert => {
            setTimeout(() => {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert.close();
            }, 5000);
        });
    },

    /**
     * Confirmar acción destructiva.
     */
    confirm(message) {
        return window.confirm(message || 'Esta acción no se puede deshacer. ¿Continuar?');
    }
};

document.addEventListener('DOMContentLoaded', () => SecuriForm.init());
