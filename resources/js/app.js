import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { Ziggy } from './ziggy';

// Configurar Ziggy para usar la URL actual en lugar de la configurada
if (typeof window !== 'undefined') {
    Ziggy.url = window.location.origin;
}

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Asegurar que el DOM esté listo antes de inicializar Inertia
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initializeInertia();
    });
} else {
    initializeInertia();
}

function initializeInertia() {
    createInertiaApp({
        title: (title) => `${title} - ${appName}`,
        resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
        setup({ el, App, props, plugin }) {
            if (!el) {
                console.error('Inertia root element not found. Make sure @inertia directive is present in app.blade.php');
                return null;
            }
            
            // Props siempre tiene la estructura correcta de Inertia.js (initialPage, initialComponent, etc.)
            // No necesitamos verificar props.initialPage ya que Inertia.js garantiza su existencia
            
            return createApp({ render: () => h(App, props) })
                .use(plugin)
                .use(ZiggyVue, Ziggy)
                .mount(el);
        },
        progress: {
            color: '#4B5563',
        },
    });
}
