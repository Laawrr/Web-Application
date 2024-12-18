<template>
  <div class="relative">
    <button class="relative focus:outline-none" @click="toggleDropdown">
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

    <div v-if="isOpen" class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-lg overflow-hidden z-50">
      <div class="py-2">
        <h3 class="text-lg leading-6 font-medium text-gray-900 px-4 py-2 border-b">Notifications</h3>

        <div v-if="notifications.length > 0" class="max-h-[70vh] overflow-y-auto">
          <div v-for="(notification, index) in notifications" :key="index"
            class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b last:border-b-0" 
            @click="openNotificationDetails(notification)">
            
            <!-- Notification Content -->
            <div class="space-y-2">
              <!-- User Info and Status -->
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 rounded-full bg-teal-500 flex items-center justify-center text-white font-bold">
                    {{ notification.user?.name?.[0]?.toUpperCase() || 'U' }}
                  </div>
                  <div>
                    <p class="font-medium text-gray-900">{{ notification.user?.name }}</p>
                    <p class="text-sm text-gray-500">commented on <span class="font-medium">{{ notification.data.item_name }}</span></p>
                  </div>
                </div>
                <span v-if="!notification.read_at" 
                  class="bg-red-500 w-2.5 h-2.5 rounded-full"
                  title="Unread notification"></span>
              </div>

              <!-- Comment Preview -->
              <div class="bg-gray-50 p-3 rounded-lg">
                <p class="text-gray-700 text-sm">{{ notification.data.comment_text }}</p>
              </div>

              <!-- Timestamp -->
              <div class="text-xs text-gray-500">
                {{ formatDate(notification.created_at) }}
              </div>
            </div>
          </div>
        </div>

        <div v-else class="p-6 text-center text-gray-500">
          <p class="text-sm">No notifications yet</p>
        </div>
      </div>
    </div>

    <CommentModal
      v-if="selectedItem"
      :show="showCommentModal"
      :item="selectedItem"
      :comments="selectedItem?.comments || []"
      :itemId="selectedItem.id"
      :itemType="selectedItem.isFound ? 'found' : 'lost'"
      @close="closeModal"
      @submit-comment="handleCommentSubmit"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import CommentModal from './CommentModal.vue';

const isOpen = ref(false);
const unreadCount = ref(0);
const notifications = ref([]);
const users = ref({});
const items = ref({});
const currentUserId = ref(null);
const selectedNotification = ref(null);
const showCommentModal = ref(false);
const selectedItem = ref(null);
const newComment = ref('');

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
};

const openNotificationDetails = async (notification) => {
  try {
    console.log('Opening notification details:', notification);
    
    // Get the item details
    const itemType = notification.data.item_type;
    const itemId = notification.data.item_id;
    const response = await axios.get(`/${itemType}-items/${itemId}`);
    
    // Get user name
    const userName = notification.data.commenter_name || 'Unknown User';

    // Set up the selected item with comments
    selectedItem.value = {
      ...response.data,
      id: notification.data.item_id,
      isFound: notification.data.item_type === 'found',
      userName: userName,
      item_name: notification.data.item_name,
      comments: notification.comments || []
    };
    
    // Show the modal
    showCommentModal.value = true;
    isOpen.value = false;
    await markAsRead(notification.id);
  } catch (error) {
    console.error('Error fetching details:', error);
  }
};

const submitComment = async () => {
  if (!newComment.value.trim()) return;
  
  try {
    const commentData = {
      item_id: selectedItem.value.id,
      item_type: selectedItem.value.isFound ? 'found' : 'lost',
      text: newComment.value.trim()
    };
    
    console.log('Submitting comment:', commentData);
    const response = await axios.post('/comments', commentData);
    console.log('Comment submitted:', response.data);
    
    // Add the new comment to the list
    if (response.data.success && response.data.comment) {
      const newCommentObj = {
        ...response.data.comment,
        userName: response.data.comment.user?.name || 'Unknown User',
        text: newComment.value.trim(),
        created_at: new Date().toISOString()
      };
      
      if (!selectedItem.value.comments) {
        selectedItem.value.comments = [];
      }
      selectedItem.value.comments.push(newCommentObj);
      
      // Clear the input
      newComment.value = '';
      
      // Refresh comments to ensure consistency
      const commentsResponse = await axios.get(`/comments/${selectedItem.value.isFound ? 'found' : 'lost'}/${selectedItem.value.id}`);
      if (commentsResponse.data.comments) {
        selectedItem.value.comments = commentsResponse.data.comments.data || [];
      }
    }
  } catch (error) {
    console.error('Error submitting comment:', error);
  }
};

const handleCommentSubmit = async ({ itemId, text, itemType }) => {
  try {
    // Post the new comment
    const { data } = await axios.post(`/api/comments`, {
      item_id: itemId,
      text: text,
      item_type: itemType
    });

    if (data.comment) {
      // Update the selectedItem's comments array by adding the new comment
      const newComment = {
        ...data.comment,
        userName: data.comment.user?.name || currentUserId.value?.name || 'Unknown User',
        user: {
          ...data.comment.user,
          name: data.comment.user?.name || currentUserId.value?.name || 'Unknown User'
        }
      };
      
      selectedItem.value = {
        ...selectedItem.value,
        comments: [...(selectedItem.value.comments || []), newComment]
      };
    }

    // Refresh all comments to ensure everything is in sync
    await fetchComments();
  } catch (error) {
    console.error('Error submitting comment:', error);
  }
};

const closeModal = () => {
  showCommentModal.value = false;
  selectedItem.value = null;
};

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
    console.log('Notifications response:', response.data);
    
    // Ensure we're working with an array of notifications
    notifications.value = Array.isArray(response.data) ? response.data : 
                         Array.isArray(response.data.notifications) ? response.data.notifications : 
                         [];
    
    // Calculate unread count after ensuring we have an array
    unreadCount.value = notifications.value.filter(n => !n.read_at).length;
    
    // Format notifications
    notifications.value = notifications.value.map(notification => ({
      ...notification,
      user: users.value[notification.data.user_id] || { name: notification.data.commenter_name },
      created_at: formatDate(notification.created_at)
    }));

    // Sort notifications by date, newest first
    notifications.value.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    
    // Fetch comments for each notification
    if (notifications.value.length > 0) {
      await fetchComments();
    }
  } catch (error) {
    console.error('Error fetching notifications:', error);
    notifications.value = []; // Ensure it's always an array even on error
    unreadCount.value = 0;
  }
};

const fetchComments = async () => {
  try {
    console.log("Fetching comments for notifications...");
    
    for (let notification of notifications.value) {
      try {
        const itemType = notification.data.item_type;
        const itemId = notification.data.item_id;
        
        console.log(`Fetching comments for ${itemType} item ${itemId}`);
        const response = await axios.get(`/comments/${itemType}/${itemId}`);
        console.log(`Comments response for item ${itemId}:`, response.data);

        if (response.data.success && Array.isArray(response.data.comments)) {
          // Map comments to include userName
          const formattedComments = response.data.comments.map(comment => ({
            id: comment.id,
            text: comment.text,
            userName: comment.user?.name || 'Unknown User',
            created_at: formatDate(comment.created_at),
            user_id: comment.user?.id
          }));

          // Sort comments by date, newest first
          formattedComments.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

          // Store comments in the notification object
          notification.comments = formattedComments;
        } else {
          console.warn(`Invalid comments response format for ${itemType} item ${itemId}:`, response.data);
          notification.comments = [];
        }
      } catch (error) {
        console.error(`Error fetching comments for notification ${notification.id}:`, error);
        notification.comments = [];
      }
    }
  } catch (error) {
    console.error('Error in fetchComments:', error);
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

onMounted(async () => {
  await fetchCurrentUser();
  await fetchUsers();
  await fetchItems();
  await fetchNotifications();
});
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
