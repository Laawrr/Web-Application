<template>
    <!-- Navbar -->
    <nav class="bg-white shadow-sm">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-3">
                    <img src="/img/logo.png" alt="Logo" class="w-10 h-10 ">
                    <span class="text-xl font-bold text-gray-800">LostNoMore</span>
                </div>

                <div class="flex items-center space-x-6">
                    <Link href="/" class="text-gray-600 hover:text-teal-500">Home</Link>

                    <a href="/edit-profile" class="text-gray-600 hover:text-teal-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 12c2.2 0 4-1.8 4-4s-1.8-4-4-4-4 1.8-4 4 1.8 4 4 4zM12 14c-3.3 0-6 1.7-6 4v2h12v-2c0-2.3-2.7-4-6-4z" />
                        </svg>
                    </a>



                    <!-- Import Notification Component -->
                    <Notification />

                    <button @click="showLoginModal = true"
                        class="bg-teal-500 text-white px-4 py-2 rounded-lg hover:bg-teal-600 transition-colors">
                        Sign Out
                    </button>
                </div>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { ref } from 'vue';
import Notification from './Notification.vue'; // Import the new Notification component
</script>

<script>
export default {
    name: 'HeaderBar',
};
</script>

<style scoped>
/* Custom styles for HeaderBar */
fwb-navbar {
    background-color: #008080;
    /* Teal for the navbar */
}

fwb-navbar-logo {
    color: white;
}

fwb-navbar-link {
    color: white;
    font-weight: bold;
}

fwb-navbar-link.is-active {
    color: #ADD8E6;
    /* Light Blue for active link */
}

fwb-navbar-link:hover {
    color: #FF7F50;
    /* Orange for hover effect */
}
</style>
