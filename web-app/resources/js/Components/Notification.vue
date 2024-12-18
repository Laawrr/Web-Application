<template>
  <div class="relative" ref="notificationRef">
    <button class="relative focus:outline-none" @click.stop="toggleDropdown">
      <svg class="w-6 h-6 text-gray-600 hover:text-teal-500" fill="none" stroke="currentColor" stroke-width="2"
        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341A6.002 6.002 0 006 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h11z">
        </path>
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 22c1.656 0 3-1.343 3-3H9c0 1.657 1.344 3 3 3z">
        </path>
      </svg>

      <span v-if="unreadCount > 0"
        class="absolute top-0 right-0 bg-red-500 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center">
        {{ unreadCount }}
      </span>
    </button>

    <div v-if="isOpen" class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg overflow-hidden z-50">
      <div class="py-2">
        <h3 class="text-center font-bold text-gray-700 p-2 border-b">Notifications</h3>

        <div v-if="notifications.length > 0" class="max-h-64 overflow-y-auto">
          <div v-for="(notification, index) in notifications" :key="index"
            class="flex items-center px-4 py-3 hover:bg-gray-100 cursor-pointer" @click="markAsRead(notification.id)">
            <div class="w-10 h-10 rounded-full bg-teal-500 flex items-center justify-center text-white font-bold">
              N
            </div>

            <div class="ml-3">
              <p class="text-sm text-gray-700">
                <strong>{{ notification.user?.name }}</strong> commented on <strong>{{ notification.itemName }}</strong>
              </p>
              <p class="text-xs text-gray-500">{{ notification.created_at }}</p>
            </div>

            <span v-if="!notification.read_at" class="ml-auto bg-red-500 w-3 h-3 rounded-full"></span>
          </div>
        </div>

        <div v-else class="p-4 text-center text-gray-500">
          No notifications yet
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const isOpen = ref(false);
const unreadCount = ref(0);
const notifications = ref([]);
const users = ref({});
const items = ref({});
const currentUserId = ref(null);
const notificationRef = ref(null);

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
  emit('dropdown-toggled', isOpen.value);
};

const closeDropdown = () => {
  isOpen.value = false;
  emit('dropdown-toggled', false);
};

// Close when clicking outside
const handleClickOutside = (event) => {
  if (notificationRef.value && !notificationRef.value.contains(event.target)) {
    closeDropdown();
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  fetchCurrentUser();
  fetchUsers();
  fetchItems();
  fetchNotifications();
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});

const emit = defineEmits(['dropdown-toggled']);

defineExpose({
  closeDropdown
});

const fetchCurrentUser = async () => {
  try {
    const response = await axios.get(window.userID);
    currentUserId.value = response.data.id;
  } catch (error) {
    console.error("Error fetching user ID:", error.message);
  }
};

const fetchUsers = async () => {
  try {
    const response = await axios.get('/users');
    response.data.forEach(user => {
      users.value[user.id] = user;
    });
  } catch (error) {
    console.error('Error fetching users:', error);
  }
};

const fetchItems = async () => {
  try {
    const lostItemsResponse = await axios.get('/lost-items');
    const foundItemsResponse = await axios.get('/found-items');

    lostItemsResponse.data.forEach(item => {
      items.value[item.id] = { item_name: item.item_name, user_id: item.user_id };
    });
    foundItemsResponse.data.forEach(item => {
      items.value[item.id] = { item_name: item.item_name, user_id: item.user_id };
    });
  } catch (error) {
    console.error('Error fetching items:', error);
  }
};

const fetchNotifications = async () => {
  try {
    const response = await axios.get('/notifications');
    notifications.value = response.data
      .filter(notification => {
        const relatedLostItem = items.value[notification.notifiable_id];
        const isUserItem = relatedLostItem && relatedLostItem.user_id === currentUserId.value;
        return isUserItem;
      })
      .map(notification => {
        notification.user = users.value[notification.user_id] || { username: 'Unknown User' };
        notification.itemName = items.value[notification.notifiable_id]?.item_name || 'Unknown Item';
        notification.comment = notification.data?.comment || 'No details available';
        notification.created_at = formatDate(notification.created_at);
        return notification;
      });

    notifications.value.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

    unreadCount.value = notifications.value.filter((n) => !n.read_at).length;
  } catch (error) {
    console.error('Error fetching notifications:', error);
  }
};

const formatDate = (date) => {
  const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
  return new Date(date).toLocaleString(undefined, options);
};

const markAsRead = async (id) => {
  try {
    await axios.put(`/notifications/${id}/read`);
    const notification = notifications.value.find(n => n.id === id);
    if (notification) {
      notification.read_at = true;
      unreadCount.value = notifications.value.filter(n => !n.read_at).length;
    }
  } catch (error) {
    console.error('Error marking notification as read:', error);
  }
};
</script>

<style scoped>
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
