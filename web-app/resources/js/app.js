import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// Import Vuetify for Vue 3 correctly
import { createVuetify } from 'vuetify';  // Correct way to import Vuetify 3
import 'vuetify/styles';  // Import Vuetify styles

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        // Create Vuetify instance
        const vuetify = createVuetify();

        app.use(plugin);
        app.use(ZiggyVue);
        app.use(vuetify);  // Use Vuetify 3 instance

        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
