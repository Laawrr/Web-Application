<template>
    <nav class="sticky top-0 bg-white shadow-md">
        <div class="container mx-auto px-6 py-3">
            <div class="flex justify-between items-center">
              <div class="flex items-center space-x-3">
                    <img src="/img/image2.png" alt="Logo" class="w-10 h-10">
                    <Link href="/" class="text-xl font-bold text-teal-500">Lost Finder</Link>
                </div>

                <div class="flex items-center space-x-6">
                    <Link href="/" class="text-gray-600 hover:text-teal-500">Home</Link>

                    <!-- Import Notification Component -->
                    <Notification ref="notificationRef" @dropdown-toggled="handleNotificationToggle" />

                    <!-- Profile Dropdown -->
                    <div class="relative">
                        <button @click="toggleProfileMenu" 
                            class="w-8 h-8 rounded-full bg-teal-500 text-white flex items-center justify-center hover:bg-teal-600 transition-colors">
                            {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                        </button>

                        <!-- Dropdown Menu -->
                        <div v-if="showProfileMenu" 
                            class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                            <Link :href="route('profile.edit')" 
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Edit Profile
                            </Link>
                            <button @click="showLoginModal = true"
                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Sign Out
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>
  
<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import Notification from './Notification.vue'; // Import the Notification component
  
const showLoginModal = ref(false);
const showProfileMenu = ref(false);
const notificationRef = ref(null);

// Toggle profile menu and close notification
const toggleProfileMenu = () => {
    showProfileMenu.value = !showProfileMenu.value;
    if (showProfileMenu.value && notificationRef.value) {
        notificationRef.value.closeDropdown();
    }
};

// Handle notification toggle
const handleNotificationToggle = (isOpen) => {
    if (isOpen) {
        showProfileMenu.value = false;
    }
};

// Close dropdowns when clicking outside
const closeDropdown = (e) => {
    if (!e.target.closest('.relative')) {
        showProfileMenu.value = false;
    }
};

// Add click outside listener
onMounted(() => {
    document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
});
</script>
  
<style scoped>
/* Custom styles for HeaderBar */
nav {
    background-color: #ffffff;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
}

nav .container {
    max-width: 1200px;
}

nav a {
    font-size: 16px;
    font-weight: 600;
}

nav a:hover {
    text-decoration: none;  /* Remove underline on hover */
}

nav .text-teal-500 {
    transition: all 0.3s ease;
}

nav .text-teal-500:hover {
    color: #004d40; /* Darker teal for hover */
}

nav .text-gray-800 {
    font-weight: bold;
}

nav .text-gray-600 {
    color: #4a4a4a;
}

nav .hover\:text-teal-500:hover {
    color: #00695c; /* Hover effect for teal links */
}

/* Dropdown styles */
.dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    padding: 8px 0;
}

.dropdown a {
    padding: 8px 16px;
    color: #333;
    text-decoration: none;
}

.dropdown a:hover {
    background-color: #e5f4f4;
}

.dropdown button {
    padding: 8px 16px;
    width: 100%;
    text-align: left;
}

.transition-all {
    transition: all 0.3s ease-in-out;
}

.sticky {
    position: sticky;
    top: 0;
    z-index: 999;
}

/* Search bar */
input[type="text"] {
    border-radius: 9999px;
    border: 1px solid #d1d5db;
    transition: border-color 0.2s;
}

input[type="text"]:focus {
    border-color: #38b2ac;
    outline: none;
}

@media (max-width: 768px) {
    .md\\:block {
        display: none;
    }
}
</style>