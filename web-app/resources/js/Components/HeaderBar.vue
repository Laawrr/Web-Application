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
  import Notification from './Notification.vue'; // Import the Notification component
  
  const showDropdown = ref(false); // Dropdown state
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
    text-decoration: underline;
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
  