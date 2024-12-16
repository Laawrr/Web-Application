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
                {{ notification.data.message }}
              </p>
              <p class="text-xs text-gray-500">{{ notification.created_at }}</p>
            </div>

            <!-- Unread indicator -->
            <span 
              v-if="!notification.read_at" 
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
import { ref, onMounted } from 'vue';
import axios from 'axios';

const isOpen = ref(false);
const unreadCount = ref(0);
const notifications = ref([]);

// Toggle the dropdown visibility
const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
};

// Fetch notifications from the server
const fetchNotifications = async () => {
  try {
    const response = await axios.get('/notifications');  // Assuming this is your API route
    notifications.value = response.data.map(notification => {
      // Parse the 'data' field if it's a string
      if (typeof notification.data === 'string') {
        notification.data = JSON.parse(notification.data);
      }
      return notification;
    });
    unreadCount.value = notifications.value.filter(n => !n.read_at).length;
  } catch (error) {
    console.error('Error fetching notifications:', error);
  }
};
// Mark notification as read
const markAsRead = async (id) => {
  try {
    await axios.put(`/notifications/${id}/read`);  // No need for Authorization header
    const notification = notifications.value.find(n => n.id === id);
    if (notification) {
      notification.read_at = true; // Mark as read in the frontend
      unreadCount.value = notifications.value.filter(n => !n.read_at).length;
    }
  } catch (error) {
    console.error('Error marking notification as read:', error);
  }
};

// Fetch notifications when the component is mounted
onMounted(fetchNotifications);
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
