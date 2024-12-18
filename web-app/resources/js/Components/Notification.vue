<template>
  <div class="relative" @click="toggleDropdown">
    <!-- Notification Bell Icon -->
    <button class="relative focus:outline-none">
      <svg class="w-6 h-6 text-gray-600 hover:text-teal-500" fill="none" stroke="currentColor" stroke-width="2"
        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341A6.002 6.002 0 006 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h11z">
        </path>
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 22c1.656 0 3-1.343 3-3H9c0 1.657 1.344 3 3 3z">
        </path>
      </svg>

      <!-- Unread Notifications Indicator -->
      <span v-if="unreadCount > 0"
        class="absolute top-0 right-0 bg-red-500 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center">
        {{ unreadCount }}
      </span>
    </button>

    <!-- Notification Dropdown -->
    <div v-if="isOpen" class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg overflow-hidden z-50">
      <div class="py-2">
        <h3 class="text-center font-bold text-gray-700 p-2 border-b">Notifications</h3>

        <!-- Loop through the notifications -->
        <div v-if="notifications.length > 0" class="max-h-64 overflow-y-auto">
          <div v-for="(notification, index) in notifications" :key="index"
            class="flex items-center px-4 py-3 hover:bg-gray-100 cursor-pointer" @click="markAsRead(notification.id)">
            <div class="w-10 h-10 rounded-full bg-teal-500 flex items-center justify-center text-white font-bold">
              N
            </div>

            <div class="ml-3">
              <!-- Display username and item name -->
              <p class="text-sm text-gray-700">
                <strong>{{ notification.user?.name }}</strong> commented on <strong>{{ notification.itemName }}</strong>
              </p>
              <p class="text-xs text-gray-500">{{ notification.created_at }}</p>
            </div>

            <!-- Unread indicator -->
            <span v-if="!notification.read_at" class="ml-auto bg-red-500 w-3 h-3 rounded-full"></span>
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
const users = ref({}); // To store user data mapped by user_id
const items = ref({}); // To store item names mapped by notifiable_id

// Toggle the dropdown visibility
const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
};

// Fetch users separately and map them by user_id
const fetchUsers = async () => {
  try {
    const response = await axios.get('/users'); // Fetch all users
    response.data.forEach(user => {
      users.value[user.id] = user; // Store user data in a map (key: user_id, value: user object)
    });
  } catch (error) {
    console.error('Error fetching users:', error);
  }
};

// Fetch lost and found items and store their names mapped by notifiable_id
const fetchItems = async () => {
  try {
    const lostItemsResponse = await axios.get('/lost-items'); // Fetch all lost items
    const foundItemsResponse = await axios.get('/found-items'); // Fetch all found items

    // Store item names using the notifiable_id as the key
    lostItemsResponse.data.forEach(item => {
      items.value[item.id] = item.item_name;
    });
    foundItemsResponse.data.forEach(item => {
      items.value[item.id] = item.item_name;
    });
  } catch (error) {
    console.error('Error fetching items:', error);
  }
};

// Fetch notifications from the server and associate with users and items
const fetchNotifications = async () => {
  try {
    const response = await axios.get('/notifications'); // Fetch notifications
    notifications.value = response.data.map(notification => {
      // Get the user associated with the notification using user_id
      notification.user = users.value[notification.user_id] || { username: 'Unknown User' };  // Use fallback if user is not found

      // Get the item name using the notifiable_id
      notification.itemName = items.value[notification.notifiable_id] || 'Unknown Item';  // Use fallback if item is not found

      notification.comment = notification.data?.comment || 'No details available';
      notification.created_at = formatDate(notification.created_at);
      return notification;
    });

    // Sort notifications by created_at in descending order
    notifications.value.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

    // Update unreadCount
    unreadCount.value = notifications.value.filter((n) => !n.read_at).length;
  } catch (error) {
    console.error('Error fetching notifications:', error);
  }
};

// Helper function to format the date
const formatDate = (date) => {
  const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
  return new Date(date).toLocaleString(undefined, options);
};

// Mark notification as read
const markAsRead = async (id) => {
  try {
    await axios.put(`/notifications/${id}/read`);  // Mark as read in the backend
    const notification = notifications.value.find(n => n.id === id);
    if (notification) {
      notification.read_at = true; // Mark as read in the frontend
      unreadCount.value = notifications.value.filter(n => !n.read_at).length;
    }
  } catch (error) {
    console.error('Error marking notification as read:', error);
  }
};

// Fetch users, items, and notifications when the component is mounted
onMounted(async () => {
  await fetchUsers();  // First fetch users
  await fetchItems();  // Then fetch items
  await fetchNotifications();  // Finally fetch notifications
});
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
