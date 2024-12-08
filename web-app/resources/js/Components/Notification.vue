<template>
    <div class="relative" @click="toggleDropdown">
      <!-- Notification Bell Icon -->
      <button class="relative focus:outline-none">
        <svg class="w-6 h-6 text-gray-600 hover:text-teal-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341A6.002 6.002 0 006 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h11z"></path>
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 22c1.656 0 3-1.343 3-3H9c0 1.657 1.344 3 3 3z"></path>
        </svg>
  
        <!-- Unread Notifications Indicator -->
        <span v-if="unreadCount > 0" class="absolute top-0 right-0 bg-red-500 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center">
          {{ unreadCount }}
        </span>
      </button>
  
      <!-- Notification Dropdown -->
      <div 
        v-if="isOpen" 
        class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg overflow-hidden z-50"
      >
        <div class="py-2">
          <h3 class="text-center font-bold text-gray-700 p-2 border-b">Notifications</h3>
          
          <!-- Loop through the notifications -->
          <div v-if="notifications.length > 0" class="max-h-64 overflow-y-auto">
            <div 
              v-for="(notification, index) in notifications" 
              :key="index" 
              class="flex items-center px-4 py-3 hover:bg-gray-100 cursor-pointer"
              @click="markAsRead(notification.id)"
            >
              <div class="w-10 h-10 rounded-full bg-teal-500 flex items-center justify-center text-white font-bold">
                {{ notification.initials }}
              </div>
              
              <div class="ml-3">
                <p class="text-sm text-gray-700">
                  {{ notification.message }}
                </p>
                <p class="text-xs text-gray-500">{{ notification.timeAgo }}</p>
              </div>
  
              <!-- Unread indicator -->
              <span 
                v-if="!notification.read" 
                class="ml-auto bg-red-500 w-3 h-3 rounded-full"
              ></span>
            </div>
          </div>
  
          <!-- No notifications message -->
          <div v-else class="p-4 text-center text-gray-500">
            No notifications yet
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue';
  
  const isOpen = ref(false);
  const unreadCount = ref(3); // Example unread count
  const notifications = ref([
    { id: 1, message: 'John Doe commented on your post', timeAgo: '2m ago', read: false, initials: 'JD' },
    { id: 2, message: 'Your item has been approved', timeAgo: '1h ago', read: false, initials: 'AI' },
    { id: 3, message: 'Welcome to Lost Finder!', timeAgo: '1d ago', read: true, initials: 'LF' },
  ]);
  
  const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
  };
  
  const markAsRead = (id) => {
    const notification = notifications.value.find(n => n.id === id);
    if (notification && !notification.read) {
      notification.read = true;
      unreadCount.value = notifications.value.filter(n => !n.read).length;
    }
  };
  </script>
  
  <style scoped>
  /* Optional: Customize the notification bell */
  button svg {
    width: 24px;
    height: 24px;
  }
  
  button:focus {
    outline: none;
  }
  
  .notification-dropdown {
    width: 16rem;
  }
  
  .notification-dropdown div {
    cursor: pointer;
  }
  
  .notification-dropdown div:hover {
    background-color: #f3f4f6;
  }
  </style>
  