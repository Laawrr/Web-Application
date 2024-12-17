// --------- Frontend / Inertia / Vue / Vuetify Setup ---------
import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// Import Vue Router
import { createRouter, createWebHistory } from 'vue-router'; // Add this line

// Import Vuetify for Vue 3
import { createVuetify } from 'vuetify';
import 'vuetify/styles'; // Vuetify styles
import * as components from 'vuetify/components'; // Import Vuetify components
import * as directives from 'vuetify/directives'; // Import Vuetify directives

// Import Font Awesome library
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faPenToSquare, faTrash } from '@fortawesome/free-solid-svg-icons';

library.add(faPenToSquare, faTrash);

// Create Vuetify instance
const vuetify = createVuetify({
    components,
    directives,
});

// Create the router instance
const router = createRouter({
    history: createWebHistory(), // Use HTML5 history mode
    routes: [
        {
            path: '/admin',
            component: () => import('./Pages/Admin.vue'),
            children: [
                {
                    path: 'users',
                    component: () => import('./Components/UsersView.vue'),
                },
                {
                    path: 'users-log',
                    component: () => import('./Components/UsersLog.vue'),
                },
                {
                    path: 'reported-items',
                    component: () => import('./Components/ReportedItems.vue'),
                },
            ],
        },
    ],
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
        app.use(router); // Add the router instance here
        app.component('font-awesome-icon', FontAwesomeIcon); // Add Font Awesome component

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