<template>
  <div class="flex flex-col min-h-screen bg-gray-100">
    <!-- Header -->
    <HeaderBar class="w-full shadow-md bg-white" />
    <!-- Main Content -->
    <div class="flex-grow max-w-5xl mx-auto py-12 px-4 md:px-8">
      <!-- Title -->
      <h1 class="text-4xl font-semibold text-center text-blue-900 mb-8"> News Feed</h1>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center space-x-2 text-xl text-gray-600">
        <div class="animate-spin rounded-full border-t-4 border-blue-500 w-8 h-8"></div>
        <span>Loading...</span>
      </div>

      <!-- Posts Section -->
      <div v-else>
        <div v-for="item in lostItems" :key="item.id"
          class="bg-white shadow-lg rounded-2xl mb-8 p-6 hover:shadow-2xl hover:scale-102 transition-all duration-300">
          <!-- LOST or FOUND Label -->
          <div :class="item.isFound ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
            class="uppercase text-xs font-semibold py-1 px-3 rounded-full inline-block mb-4">
            {{ item.isFound ? 'FOUND' : 'LOST' }}
          </div>

          <!-- Post Header -->
          <div class="flex items-center space-x-4 mb-4">
            <h2 class="text-xl font-semibold text-gray-800">{{ item.item_name }}</h2>
            <p class="text-sm text-gray-500">Posted by: {{ item.userName }} | {{ formatDate(item.created_at) }}</p>
          </div>

          <!-- Description -->
          <p class="text-gray-700 text-base mb-4">{{ item.description }}</p>

          <!-- Image -->
          <div v-if="item.image_url" class="mb-4 overflow-hidden rounded-lg">
            <img :src="item.image_url" alt="Lost Item" class="w-full object-cover rounded-lg shadow-sm" />
          </div>
          <div class="flex justify-end">
            <button 
              @click="handleClaim(item)"
              class="bg-green-500 hover:bg-green-600 mt-5 text-white px-4 py-2 rounded-lg flex  items-center space-x-2 transition-colors duration-200"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>Claim Item</span>
            </button>
          </div>

          <!-- Comments Section -->
          <div class="">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Comments</h3>

            <!-- Comment Button -->
            <button @click="openCommentModal(item)"
              class="flex items-center space-x-2 text-blue-500 hover:text-blue-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-2" fill="none" stroke="currentColor"
                viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M21 15a2 2 0 10-4 0 2 2 0 004 0zM19 19H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2z" />
              </svg>
              <span>Comments ({{ item.comments?.length || 0 }})</span>
            </button>

            <!-- Comment Modal -->
            <CommentModal
              v-if="activeCommentModal === item.id"
              :show="true"
              :comments="item.comments || []"
              :item="selectedItem || item"
              :item-id="item.id"
              :item-type="item.isFound ? 'found' : 'lost'"
              @close="closeCommentModal"
              @submit-comment="submitComment"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <FooterBar />
  </div>
</template>

<script>
import { ref, onMounted } from "vue";
import axios from "axios";
import HeaderBar from "@/Components/HeaderBar.vue";
import FooterBar from "@/Components/FooterBar.vue";
import CommentModal from "@/Components/CommentModal.vue";
import { router } from '@inertiajs/vue3';

export default {
  name: "NewsFeed",
  components: { HeaderBar, FooterBar, CommentModal },
  setup() {
    const lostItems = ref([]);
    const loading = ref(true);
    const newComments = ref({});
    const userName = ref(null);
    const currentUserId = ref(null);
    const activeCommentModal = ref(null);
    const selectedItem = ref(null);

    // Fetch the current user's ID
    const fetchCurrentUser = async () => {
      try {
        if (!window.userID) {
          console.error("window.userID is not defined.");
          return;
        }

        const response = await axios.get(window.userID);
        currentUserId.value = response.data.id;
        console.log("Fetched user ID:", currentUserId.value);

        fetchUser();
      } catch (error) {
        console.error("Error fetching user ID:", error.message);
      }
    };

    // Fetch the current user's name based on the currentUserId
    const fetchUser = async () => {
      try {
        if (!currentUserId.value) {
          console.error("Current user ID is not set.");
          return;
        }

        const response = await axios.get("/users");
        console.log("Response data:", response.data);

        if (Array.isArray(response.data)) {
          const user = response.data.find(u => u.id === currentUserId.value);

          if (user) {
            userName.value = user.name;
            console.log("User fetched:", userName.value);
          } else {
            console.log("User not found.");
          }
        } else {
          console.error("Invalid response data format. Expected an array.");
        }
      } catch (error) {
        console.error("Error fetching user data:", error.message);
      }
    };

    // Fetch posts (lost and found items)
    const fetchPosts = async () => {
      try {
        const [lost, found] = await Promise.all([
          axios.get(window.lostItemsUrl),
          axios.get(window.foundItemsUrl),
        ]);

        const lostPosts = lost.data.map(async (item) => {
          const user = await fetchUserById(item.user_id);
          return {
            ...item,
            isFound: false,
            comments: [],
            showCommentSection: false,
            userName: user.name,
          };
        });

        const foundPosts = found.data.map(async (item) => {
          const user = await fetchUserById(item.user_id);
          return {
            ...item,
            isFound: true,
            comments: [],
            showCommentSection: false,
            userName: user.name,
          };
        });

        lostItems.value = [...await Promise.all(lostPosts), ...await Promise.all(foundPosts)];
        fetchComments();
      } catch (error) {
        console.error("Error fetching posts:", error.message);
      } finally {
        loading.value = false;
      }
    };

    // Fetch user by ID
    const fetchUserById = async (userId) => {
      try {
        const response = await axios.get(`/users/${userId}`);
        return response.data;
      } catch (error) {
        console.error(`Error fetching user ${userId}:`, error.message);
        return { name: "Unknown User" };
      }
    };

    // Fetch comments for all items
    const fetchComments = async () => {
      try {
        console.log("Fetching comments...");
        
        for (let item of lostItems.value) {
          try {
            const itemType = item.isFound ? 'found' : 'lost';
            const response = await axios.get(`/comments/${itemType}/${item.id}`);
            console.log(`Comments for item ${item.id}:`, response.data);

            // Handle different response formats and ensure we have an array
            let commentsArray = [];
            if (response.data?.comments?.data) {
              commentsArray = response.data.comments.data;
            } else if (response.data?.comments) {
              commentsArray = Array.isArray(response.data.comments)
                ? response.data.comments
                : Object.values(response.data.comments);
            } else if (Array.isArray(response.data)) {
              commentsArray = response.data;
            } else if (typeof response.data === 'object') {
              commentsArray = Object.values(response.data);
            }

            // Ensure commentsArray is actually an array and filter out null values
            commentsArray = Array.isArray(commentsArray) 
              ? commentsArray.filter(comment => comment !== null)
              : [];
            
            // Ensure each comment has a userName and required fields
            item.comments = commentsArray.map(comment => {
              // Ensure we have the comment user data
              const userName = comment.user?.name || comment.userName || 'Unknown User';
              
              return {
                ...comment,
                userName,
                text: comment.text || '',
                id: comment.id || null,
                created_at: comment.created_at || new Date().toISOString(),
                user: {
                  ...comment.user,
                  name: userName
                }
              };
            });

            // Sort comments by date, newest first
            item.comments.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
          } catch (error) {
            console.error(`Error fetching comments for item ${item.id}:`, error.message);
            item.comments = [];
          }
        }
      } catch (error) {
        console.error("Error in fetchComments:", error.message);
      }
    };

    // Submit a new comment
    const submitComment = async (data) => {
      try {
        const { itemId, text, itemType } = data;
        
        if (!text.trim()) return;

        console.log('Submitting comment with data:', {
          item_id: itemId,
          text: text.trim(),
          item_type: itemType
        });

        const response = await axios.post("/comments", {
          item_id: itemId,
          text: text.trim(),
          item_type: itemType
        });

        // Find the item and add the new comment
        const item = lostItems.value.find(item => item.id === itemId);
        if (item) {
          if (!item.comments) {
            item.comments = [];
          }
          
          // Add the new comment with user data from the response
          if (response.data.comment) {
            const newComment = {
              ...response.data.comment,
              userName: userName.value // Add the current user's name to the comment
            };
            item.comments.unshift(newComment);
            
            // Refresh comments to ensure consistency
            await fetchComments();
          }
        }

        return response.data;
      } catch (error) {
        console.error("Error submitting comment:", error);
        if (error.response && error.response.data) {
          console.error("Server error details:", error.response.data);
        }
        throw error;
      }
    };

    const handleClaim = (item) => {
      router.visit(`/claims?item_id=${item.id}&item_type=${item.isFound ? 'found' : 'lost'}`);
    };

    const openCommentModal = (item) => {
      if (item) {
        selectedItem.value = { ...item };  // Create a copy of the item
        activeCommentModal.value = item.id;
        console.log('Opening modal for item:', selectedItem.value);
      }
    };

    const closeCommentModal = () => {
      activeCommentModal.value = null;
      selectedItem.value = null;
    };

    // Format date helper function
    const formatDate = (date) => {
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    };

    onMounted(() => {
      fetchCurrentUser();
      fetchPosts();
    });

    return {
      lostItems,
      loading,
      activeCommentModal,
      selectedItem,
      handleClaim,
      submitComment,
      openCommentModal,
      closeCommentModal,
      formatDate
    };
  }
};
</script>

<style scoped>
/* === Global Layout Improvements === */
body {
  font-family: 'Arial', sans-serif;
}

.flex {
  display: flex;
}

.flex-col {
  flex-direction: column;
}

.min-h-screen {
  min-height: 100vh;
}

.bg-gray-100 {
  background-color: #f3f4f6;
}

.bg-white {
  background-color: #ffffff;
}

.text-center {
  text-align: center;
}

.text-blue-900 {
  color: #1e3a8a;
}

.text-gray-700 {
  color: #4b5563;
}

.text-gray-800 {
  color: #374151;
}

.text-gray-500 {
  color: #6b7280;
}

.max-w-5xl {
  max-width: 64rem;
}

.mx-auto {
  margin-left: auto;
  margin-right: auto;
}

.p-6 {
  padding: 1.5rem;
}

.px-4 {
  padding-left: 1rem;
  padding-right: 1rem;
}

.md\\:px-8 {
  padding-left: 2rem;
  padding-right: 2rem;
}

.py-12 {
  padding-top: 3rem;
  padding-bottom: 3rem;
}

/* === Loading Spinner === */
.loading-spinner {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 1rem;
}

.loading-spinner .animate-spin {
  width: 50px;
  height: 50px;
  border-width: 4px;
}

.loading-spinner p {
  margin-left: 0.75rem;
  font-size: 1.25rem;
  color: #555;
}

/* === Post Cards === */
.bg-white {
  background-color: #ffffff;
}

.shadow-lg {
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
}

.rounded-2xl {
  border-radius: 1rem;
}

.mb-8 {
  margin-bottom: 2rem;
}

.p-6 {
  padding: 1.5rem;
}

.hover\\:shadow-2xl:hover {
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.hover\\:scale-102:hover {
  transform: scale(1.02);
}

.transition-all {
  transition: all 0.3s ease;
}

/* === Labels for Lost / Found === */
.bg-red-100 {
  background-color: #fee2e2;
}

.text-red-700 {
  color: #b91c1c;
}

.bg-green-100 {
  background-color: #d1fae5;
}

.text-green-700 {
  color: #047857;
}

.uppercase {
  text-transform: uppercase;
}

.text-xs {
  font-size: 0.75rem;
}

.font-semibold {
  font-weight: 600;
}

.py-1 {
  padding-top: 0.25rem;
  padding-bottom: 0.25rem;
}

.px-3 {
  padding-left: 0.75rem;
  padding-right: 0.75rem;
}

.rounded-full {
  border-radius: 9999px;
}

.inline-block {
  display: inline-block;
}

/* === Post Header === */
.text-xl {
  font-size: 1.25rem;
}

.text-sm {
  font-size: 0.875rem;
}

.font-semibold {
  font-weight: 600;
}

.space-x-4 {
  display: flex;
  gap: 1rem;
}

.mb-4 {
  margin-bottom: 1rem;
}

.flex {
  display: flex;
}

.items-center {
  align-items: center;
}

/* === Description Section === */
.text-base {
  font-size: 1rem;
}

.text-gray-700 {
  color: #4b5563;
}

.mb-4 {
  margin-bottom: 1rem;
}

/* === Image Section === */
.overflow-hidden {
  overflow: hidden;
}

.rounded-lg {
  border-radius: 0.75rem;
}

.img {
  width: 100%;
  object-fit: cover;
  border-radius: 0.75rem;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

/* === Claim Button === */
button {
  border: none;
  outline: none;
}

.bg-green-500 {
  background-color: #10b981;
}

.hover\\:bg-green-600:hover {
  background-color: #059669;
}

.text-white {
  color: #ffffff;
}

.px-4 {
  padding-left: 1rem;
  padding-right: 1rem;
}

.py-2 {
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
}

.rounded-lg {
  border-radius: 0.75rem;
}

.flex {
  display: flex;
}

.items-center {
  align-items: center;
}

.space-x-2 {
  display: flex;
  gap: 0.5rem;
}

.transition-colors {
  transition: color 0.3s ease;
}

.duration-200 {
  transition-duration: 200ms;
}

/* === Comments Section === */
.mt-6 {
  margin-top: 1.5rem;
}

.text-lg {
  font-size: 1.125rem;
}

.mb-2 {
  margin-bottom: 0.5rem;
}

.button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.text-blue-500 {
  color: #3b82f6;
}

.hover\\:text-blue-600:hover {
  color: #2563eb;
}

/* === Footer Section === */
footer {
  background-color: #1f2937;
  color: #f9fafb;
  padding: 1rem;
  text-align: center;
}


</style>