<template>
  <div class="flex flex-col min-h-screen">
    <!-- Full-width Header -->
    <HeaderBar class="w-full" />

    <!-- Main Content Container -->
    <div class="flex-grow max-w-4xl mx-auto py-8">
      <!-- Title of the feed -->
      <h1 class="text-4xl font-bold text-center text-gray-800 mb-8">Lost Items News Feed</h1>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center space-x-2 text-xl text-gray-600">
        <div class="animate-spin rounded-full border-t-4 border-blue-500 w-8 h-8"></div>
        <span>Loading...</span>
      </div>
      
      <!-- Lost items list -->
      <div v-else>
        <!-- List each lost item with a styled post -->
        <div v-for="item in lostItems" :key="item.id" class="bg-white rounded-lg shadow-md mb-6 p-6">
          
          <!-- Post Header with title and user information -->
          <div class="flex items-center space-x-4 mb-4">
            <div class="h-12 w-12 rounded-full bg-gray-200"></div> <!-- Placeholder for user avatar -->
            <div>
              <h2 class="text-xl font-semibold text-gray-800">{{ item.title }}</h2>
              <p class="text-sm text-gray-500">Reported on: {{ formatDate(item.created_at) }}</p>
            </div>
          </div>

          <!-- Post Description -->
          <p class="text-gray-700 mb-4">{{ item.description }}</p>

          <!-- Optional Image (if available) -->
          <div v-if="item.image_url" class="mb-4">
            <img :src="item.image_url" alt="Lost Item Image" class="w-full rounded-md shadow-sm">
          </div>

          <!-- Post Footer: Like and Share buttons -->
          <div class="flex justify-between items-center text-gray-600">
            <button class="flex items-center space-x-1 hover:text-blue-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H10a2 2 0 00-2 2v14a2 2 0 002 2h4a2 2 0 002-2V4a2 2 0 00-2-2z" />
              </svg>
              <span>Like</span>
            </button>

            <button class="flex items-center space-x-1 hover:text-blue-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9L18 9M12 4v12" />
              </svg>
              <span>Share</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Full-width Footer -->
    <FooterBar class="w-full" />
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import HeaderBar from '@/Components/HeaderBar.vue';  // Adjust the path according to your project structure
import FooterBar from '@/Components/FooterBar.vue';  // Adjust the path according to your project structure

export default {
  name: 'NewsFeed',
  components: {
    HeaderBar,
    FooterBar
  },
  setup() {
    const lostItems = ref([]);
    const loading = ref(true);

    const fetchLostItems = async () => {
      try {
        const response = await axios.get('http://localhost:5000/lost-items'); // Fetch lost items from the backend
        lostItems.value = response.data;
      } catch (error) {
        console.error('Error fetching lost items:', error);
      } finally {
        loading.value = false;
      }
    };

    const formatDate = (dateString) => {
      const date = new Date(dateString);
      return date.toLocaleDateString();
    };

    onMounted(() => {
      fetchLostItems();
    });

    return { lostItems, loading, formatDate };
  }
};
</script>

<style scoped>
/* Custom spinner style */
.spinner-border {
  border-top-color: transparent;
}

/* Add more styles as needed */
</style>
