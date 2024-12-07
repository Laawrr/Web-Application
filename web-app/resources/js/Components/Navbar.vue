<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const isOpen = ref(false);
const emit = defineEmits(['showLogin']);

const toggleMenu = () => {
    isOpen.value = !isOpen.value;
};

const showLoginModal = () => {
    emit('showLogin');
    if (isOpen.value) {
        isOpen.value = false; // Close mobile menu if open
    }
};
</script>

<template>
    <nav class="bg-white shadow-sm">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo and brand -->
                <div class="flex items-center space-x-3">
                    <img src="/img/image2.png" alt="Logo" class="w-10 h-10">
                    <Link href="/" class="text-2xl font-bold text-teal-500">Lost Finder</Link>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-4">
                    <Link href="/about" class="text-gray-600 hover:text-teal-500">About</Link>
                    <Link href="/contact" class="text-gray-600 hover:text-teal-500">Contact</Link>
                    <Link :href="route('register')" class="text-gray-600 hover:text-teal-500">Register</Link>
                    <button @click="showLoginModal" 
                        class="px-4 py-2 rounded-lg bg-teal-500 text-white hover:bg-teal-600 transition-colors">
                        Sign In
                    </button>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button @click="toggleMenu" class="text-gray-600 hover:text-teal-500 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path v-if="!isOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                            <path v-if="isOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation -->
            <div v-show="isOpen" class="md:hidden">
                <div class="px-2 pt-2 pb-3 space-y-1">
                    <Link href="/about"
                        class="block px-3 py-2 rounded-md text-gray-600 hover:text-teal-500 hover:bg-gray-50">
                        About
                    </Link>
                    <Link href="/contact"
                        class="block px-3 py-2 rounded-md text-gray-600 hover:text-teal-500 hover:bg-gray-50">
                        Contact
                    </Link>
                    <Link :href="route('register')"
                        class="block px-3 py-2 rounded-md text-gray-600 hover:text-teal-500 hover:bg-gray-50">
                        Register
                    </Link>
                    <button @click="showLoginModal"
                        class="block w-full text-left px-3 py-2 rounded-md text-teal-500 hover:text-teal-600 hover:bg-gray-50">
                        Sign In
                    </button>
                </div>
            </div>
        </div>
    </nav>
</template>
