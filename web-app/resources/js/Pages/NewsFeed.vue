<template>
  <div class="flex flex-col min-h-screen bg-gray-100">
    <!-- Header -->
    <HeaderBar class="w-full shadow-md bg-white" />

    <!-- Main Content -->
    <div class="flex-grow max-w-5xl mx-auto py-12 px-4 md:px-8">
      <!-- Title -->
      <h1 class="text-4xl font-semibold text-center text-blue-900 mb-8">Lost Items News Feed</h1>

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
            <p class="text-sm text-gray-500">Reported on: {{ formatDate(item.created_at) }}</p>
          </div>

          <!-- Description -->
          <p class="text-gray-700 text-base mb-4">{{ item.description }}</p>

          <!-- Image -->
          <div v-if="item.image_url" class="mb-4 overflow-hidden rounded-lg">
            <img :src="item.image_url" alt="Lost Item" class="w-full object-cover rounded-lg shadow-sm" />
          </div>

          <!-- Comments Section -->
          <div class="mt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Comments</h3>

            <!-- Toggle Button -->
            <button @click="toggleCommentSection(item.id)"
              class="flex items-center space-x-2 text-blue-500 hover:text-blue-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" stroke="currentColor"
                viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M21 15a2 2 0 10-4 0 2 2 0 004 0zM19 19H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2z" />
              </svg>
              <span>Comment</span>
            </button>

            <!-- Comment Input -->
            <div v-if="item.showCommentSection" class="mt-4">
              <div class="flex items-center space-x-4 mb-4">
                <input v-model="newComments[item.id]" type="text" placeholder="Add a comment..."
                  class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500" />
                <button @click="submitComment(item.id, item.isFound ? 'found' : 'lost')"
                  :disabled="!newComments[item.id]?.trim()"
                  class="bg-blue-500 text-white px-4 py-2 rounded-lg disabled:opacity-50">
                  Post
                </button>
              </div>

              <!-- List of Comments -->
              <div v-for="comment in item.comments" :key="comment.id" class="bg-gray-100 p-2 rounded-lg mb-2">
                <div class="flex items-center space-x-2">
                  <span class="font-semibold text-gray-800">{{ comment.userName }}</span>
                  <p class="text-gray-700">{{ comment.text }}</p>
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
import { ref, onMounted } from "vue";
import axios from "axios";
import HeaderBar from "@/Components/HeaderBar.vue";
import FooterBar from "@/Components/FooterBar.vue";

export default {
  name: "NewsFeed",
  components: { HeaderBar, FooterBar },
  setup() {
    const lostItems = ref([]);
    const loading = ref(true);
    const newComments = ref({});
    const userName = ref(""); // Store the user's name here

    // Fetch the current user's name
    const fetchUser = async () => {
      try {
        const response = await axios.get("/user"); // Assuming the endpoint to fetch user details
        userName.value = response.data.name; // Update userName with the response
        console.log("User fetched:", userName.value);
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

        const lostPosts = lost.data.map((item) => ({
          ...item,
          isFound: false,
          comments: [],
          showCommentSection: false,
        }));
        const foundPosts = found.data.map((item) => ({
          ...item,
          isFound: true,
          comments: [],
          showCommentSection: false,
        }));

        lostItems.value = [...lostPosts, ...foundPosts];
        fetchComments(); // Fetch comments after posts are fetched
      } catch (error) {
        console.error("Error fetching posts:", error.message);
      } finally {
        loading.value = false;
      }
    };

    // Fetch comments for each post
    const fetchComments = async () => {
      console.log("Fetching comments for all items...");

      for (let item of lostItems.value) {
        console.log(`Fetching comments for item with ID: ${item.id}, type: ${item.isFound ? 'found' : 'lost'}`);

        try {
          const itemType = item.isFound ? 'found' : 'lost';
          const response = await axios.get(`/comments/${itemType}/${item.id}`);

          console.log(`Comments fetched for item ${item.id}:`, response.data.comments);

          // Include user name for each comment
          item.comments = response.data.comments.map(comment => ({
            ...comment,
            userName: comment.user.name, // Assuming the API response includes user data
          }));
        } catch (error) {
          console.error(`Error fetching comments for item ${item.id}:`, error.message);
        }
      }

      console.log("Finished fetching comments for all items.");
    };

    // Toggle the visibility of the comment section
    const toggleCommentSection = (id) => {
      const item = lostItems.value.find((item) => item.id === id);
      if (item) item.showCommentSection = !item.showCommentSection;
    };

    // Submit a new comment
    const submitComment = async (id, type) => {
      const text = newComments.value[id]?.trim();
      console.log("Attempting to submit comment", { id, type, text });

      if (!text) {
        console.log("No comment text provided.");
        return;
      }

      try {
        console.log("Sending comment to server...");
        const response = await axios.post("/comments", {
          item_id: id,
          item_type: type,
          text,
          user_name: userName.value, // Use the fetched user name
        });

        console.log("Response received:", response.data);

        const item = lostItems.value.find((item) => item.id === id);
        if (item) {
          item.comments.push({
            ...response.data.comment,
            userName: userName.value, // Add the user name to the new comment
          });
          console.log("Updated comments for item:", item);
        }

        newComments.value[id] = "";
        console.log("Cleared input field after submission.");
      } catch (error) {
        console.error("Error posting comment:", error.message);
      }
    };

    // Format the date to display it in a readable format
    const formatDate = (date) => new Date(date).toLocaleDateString(undefined, {
      year: "numeric",
      month: "long",
      day: "numeric",
    });

    // Fetch the posts and user data when the component is mounted
    onMounted(() => {
      fetchUser();  // Fetch the current user first
      fetchPosts(); // Then fetch the posts
    });

    return { lostItems, loading, newComments, toggleCommentSection, submitComment, formatDate, userName };
  },
};
</script>


<style scoped>
/* Simplified for clarity */
input:focus {
  border-color: #3b82f6;
  outline: none;
  box-shadow: 0 0 4px rgba(59, 130, 246, 0.3);
}

.hover\:scale-102:hover {
  transform: scale(1.02);
}

.hover\:shadow-2xl:hover {
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}
</style>
