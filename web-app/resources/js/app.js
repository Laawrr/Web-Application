// --------- Frontend / Inertia / Vue / Vuetify Setup ---------
import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// Import Vuetify for Vue 3
import { createVuetify } from 'vuetify';
import 'vuetify/styles'; // Vuetify styles
import * as components from 'vuetify/components'; // Import Vuetify components
import * as directives from 'vuetify/directives'; // Import Vuetify directives

// Create Vuetify instance
const vuetify = createVuetify({
    components,
    directives,
});

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue')
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin);
        app.use(ZiggyVue);
        app.use(vuetify); // Add Vuetify instance here

        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// --------- Backend / Express Setup ---------
const express = require('express'); 
const lostItemsRoute = require('./routes/lostItems'); // Import your route file for lost items
const app = express();

// Middleware to parse incoming JSON requests
app.use(express.json());

// Use the routes from the lostItems.js file
app.use('/api', lostItemsRoute); // All routes in lostItems.js will be prefixed with /api

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`Server is running on port ${PORT}`);
});
