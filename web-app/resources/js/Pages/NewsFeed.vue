<template>
  <div class="flex flex-col min-h-screen bg-gray-100">

    <!-- Header -->
    <HeaderBar class="w-full shadow-md bg-white" />

    <!-- Main Content -->
    <div class="flex-grow max-w-5xl mx-auto py-12 px-4 md:px-8">

      <!-- Title of the Feed -->
      <h1 class="text-4xl font-semibold text-center text-blue-900 mb-8">Lost Items News Feed</h1>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center space-x-2 text-xl text-gray-600">
        <div class="animate-spin rounded-full border-t-4 border-blue-500 w-8 h-8"></div>
        <span>Loading...</span>
      </div>

      <!-- Lost items list -->
      <div v-else>
        <div v-for="item in lostItems" :key="item.id" class="bg-white shadow-lg rounded-2xl mb-8 p-6 transition-all transform hover:scale-102 hover:shadow-2xl duration-300">

          <!-- Post Header -->
          <div class="flex items-center space-x-4 mb-4">
            <div>
              <h2 class="text-xl font-semibold text-gray-800">{{ item.item_name }}</h2>
              <p class="text-sm text-gray-500">Reported on: {{ formatDate(item.created_at) }}</p>
            </div>
          </div>

          <!-- Post Description -->
          <p class="text-gray-700 text-base mb-4">{{ item.description }}</p>

          <!-- Optional Image (if available) -->
          <div v-if="item.image_url" class="mb-4 rounded-lg overflow-hidden">
            <img :src="item.image_url" alt="Lost Item Image" class="w-full object-cover rounded-lg shadow-sm">
          </div>

          <!-- Comment Section -->
          <div class="mt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Comments</h3>

            <!-- Comment Icon to trigger comment input -->
            <div class="flex items-center space-x-4 mb-4">
              <button 
                @click="toggleCommentSection(item.id)"
                class="flex items-center space-x-2 text-blue-500 hover:text-blue-600"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21 15a2 2 0 10-4 0 2 2 0 004 0zM19 19H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2z" />
                </svg>
                <span class="text-sm">Comment</span>
              </button>
            </div>

            <!-- Comment input form (toggle visibility based on click) -->
            <div v-if="item.showCommentSection" class="transition-all duration-500 ease-in-out mt-4">
              <div class="flex items-center space-x-4 mb-4">
                <input 
                  v-model="newComment" 
                  type="text" 
                  @input="onCommentInput(item.id)"
                  class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Add a comment..." 
                />
                <button 
                  @click="submitComment(item.id)" 
                  :disabled="newComment.trim() === ''"
                  class="bg-blue-500 text-white p-2 rounded-lg disabled:opacity-50"
                >
                  Post
                </button>
              </div>

              <!-- List of comments -->
              <div class="space-y-2">
                <div v-for="comment in item.comments" :key="comment.id" class="flex items-center space-x-2 bg-gray-100 p-2 rounded-lg">
                  <div class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center">
                    <span class="font-semibold text-sm">U</span>
                  </div>
                  <p class="text-gray-700 text-sm">{{ comment.text }}</p>
                </div>
              </div>
            </div>
          </div>
          
        </div>
      </div>

    </div>

    <!-- Footer -->
    <FooterBar />
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import HeaderBar from '@/Components/HeaderBar.vue';
import FooterBar from '@/Components/FooterBar.vue';

export default {
  name: 'NewsFeed',
  components: {
    HeaderBar,
    FooterBar
  },
  setup() {
    const lostItems = ref([]); // Lost items data
    const loading = ref(true); // Loading state
    const newComment = ref(''); // Comment input
    const userId = ref(null); // User ID state

    // Fetch user ID
    const fetchUserId = async () => {
      try {
        const response = await axios.get(window.userID); // User ID URL
        userId.value = response.data.id; // Set user ID
      } catch (error) {
        console.error('Error fetching user ID:', error.message);
      }
    };

    // Fetch posts for lost and found items
    const fetchPosts = async () => {
      try {
        // Fetch data from both endpoints simultaneously
        const [lostResponse, foundResponse] = await Promise.all([
          axios.get(window.lostItemsUrl), // Lost items URL
          axios.get(window.foundItemsUrl), // Found items URL
        ]);

        // Combine and mark data (optional isFound flag for future use)
        const lostPosts = lostResponse.data.map(post => ({ ...post, isFound: false, comments: [], showCommentSection: false }));
        const foundPosts = foundResponse.data.map(post => ({ ...post, isFound: true, comments: [], showCommentSection: false }));

        // Merge posts into a single array
        lostItems.value = [...lostPosts, ...foundPosts];
      } catch (error) {
        console.error('Error fetching posts:', error.message);
      } finally {
        loading.value = false;
      }
    };

    // Toggle the visibility of the comment section
    const toggleCommentSection = (itemId) => {
      const item = lostItems.value.find(item => item.id === itemId);
      if (item) item.showCommentSection = !item.showCommentSection;
    };

    // Submit a new comment
    const submitComment = (itemId) => {
      const commentText = newComment.value.trim();
      if (commentText !== '') {
        const item = lostItems.value.find(item => item.id === itemId);
        if (item) {
          item.comments.push({ id: Date.now(), text: commentText });
          newComment.value = ''; // Clear input
        }
      }
    };

    // Format date helper
    const formatDate = (dateString) => {
      const date = new Date(dateString);
      return date.toLocaleDateString();
    };

    // On mount, fetch user ID and posts
    onMounted(async () => {
      await fetchUserId();
      await fetchPosts();
    });

    return {
      lostItems,
      loading,
      formatDate,
      newComment,
      submitComment,
      toggleCommentSection
    };
  }
};
</script>


<style scoped>
/* Add a custom spinner style */
.spinner-border {
  border-top-color: transparent;
}

/* Hover effect on the newsfeed post */
.post-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.card-image {
  width: 100%;
  height: 250px;
  object-fit: cover;
  border-radius: 0.5rem;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 10px;
}

.card-footer button {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #6b7280;
  cursor: pointer;
  transition: color 0.2s ease;
}

.card-footer button:hover {
  color: #008080;
}

.card-footer button:focus {
  outline: none;
}

.card-footer button svg {
  width: 20px;
  height: 20px;
}

/* Add a smooth transition for hover effects */
.transition-all {
  transition: all 0.3s ease-in-out;
}

.hover\:scale-102:hover {
  transform: scale(1.02);
}

.hover\:shadow-2xl:hover {
  box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.15);
}

/* Ensures the footer is always at the bottom */
.mt-auto {
  margin-top: auto;
}

/* Styling for smooth transition and comment input */
input:focus {
  border-color: #008080;
  box-shadow: 0 0 5px rgba(59, 130, 246, 0.5);
}

/* Style for the comment input box */
input {
  border-radius: 50px;
  padding: 10px 16px;
  background-color: #fafafa;
  font-size: 14px;
  color: #333;
}

/* Comment card styling */
.comment-card {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}
</style>
