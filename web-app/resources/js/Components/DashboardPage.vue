<template>
  <div>
    <main class="dashboard">
      <!-- Dashboard Header -->
      <section class="dashboard-header">
        <h1>Lost and Found</h1>
        <div class="search-bar">
          <input type="text" v-model="searchQuery" placeholder="Search for lost items..." @input="filterPosts" />
        </div>
      </section>

      <!-- Featured Posts -->
      <section class="featured-posts">
        <div v-if="filteredPosts.length === 0" class="no-posts">
          <p>No posts found.</p>
        </div>
        <div v-for="post in filteredPosts" :key="post.id" class="card">
          <img v-if="post.image_url" :src="post.image_url" alt="Item Image" class="card-image clickable"
            @click="enlargeImage(post.image_url)" />
          <p class="post-date">{{ post.lost_date || post.found_date }}</p>

          <p class="post-info"><strong>Status:</strong> {{ post.isFound ? 'Found' : 'Lost' }}</p>
          <p class="post-info"><strong>Category:</strong> {{ post.category }}</p>
          <p class="post-info"><strong>Description:</strong> {{ post.description }}</p>
          <p class="post-info"><strong>Facebook:</strong> {{ post.facebook_link }}</p>
          <p class="post-info"><strong>Contact:</strong> {{ post.contact_number }}</p>
          <button @click="deletePost(post.id)" class="delete-btn">
            <span class="delete-icon">&#10005;</span>
          </button>
        </div>
      </section>

      <!-- Upload Form Modal -->
      <div v-if="showUploadForm" class="modal-overlay">
        <div class="modal-content">
          <h2>Add Lost Item</h2>
          <form @submit.prevent="submitForm" enctype="multipart/form-data">
            <div class="form-grid">
              <!-- Left Column -->
              <div class="form-column">
                <div class="form-group">
                  <label for="itemName">Item Name</label>
                  <input type="text" id="itemName" v-model="newItem.item_name" required />
                </div>
                <div class="form-group">
                  <label for="itemStatus">Status</label>
                  <div id="itemStatus" class="radio-group">
                    <label>
                      <input type="radio" v-model="newItem.status" value="Lost" required />Lost
                    </label>
                    <label>
                      <input type="radio" v-model="newItem.status" value="Found" required />Found
                    </label>
                  </div>
                </div>
                <div class="form-group">
                  <label for="category">Category</label>
                  <select id="category" v-model="newItem.category" required>
                    <option value="Electronics">Electronics</option>
                    <option value="Clothing">Clothing</option>
                    <option value="Wallets">Wallets</option>
                    <option value="Bags">Bags</option>
                    <option value="Jewelry">Jewelry</option>
                    <option value="Cards">Cards</option>
                    <option value="Books">Books</option>
                    <option value="Accessories">Accessories</option>
                  </select>
                </div>
                <div class="form-group" v-if="newItem.status === 'Lost'">
                  <label for="dateLost">Date of Loss</label>
                  <input type="date" id="dateLost" v-model="newItem.lost_date" required />
                </div>
                <div class="form-group" v-if="newItem.status === 'Found'">
                  <label for="dateFound">Date Found</label>
                  <input type="date" id="dateFound" v-model="newItem.found_date" required />
                </div>
                <div class="form-group">
                  <label for="description">Item Description</label>
                  <textarea id="description" v-model="newItem.description" rows="3" required></textarea>
                </div>
                <div class="form-group">
                  <label for="facebookLink">Facebook Link</label>
                  <input type="url" id="facebookLink" v-model="newItem.facebook_link" required />
                </div>
                <div class="form-group">
                  <label for="contactNumber">Contact Number</label>
                  <input type="tel" id="contactNumber" v-model="newItem.contact_number" required />
                </div>
                <div class="form-group">
                  <label for="itemImage">Upload Image</label>
                  <input type="file" id="itemImage" accept="image/*" @change="handleFileUpload" />
                  <img v-if="newItem.image_preview_url" :src="newItem.image_preview_url" alt="Preview"
                    class="image-preview" />
                </div>
              </div>
              <div class="form-column">
                <div class="form-group">
                  <label>Location</label>
                  <div class="map-wrapper">
                    <Map @location-selected="updateLocation" />
                  </div>
                </div>
              </div>
            </div>
            <div class="form-actions">
              <button type="button" class="btn secondary" @click="closeUploadForm">Cancel</button>
              <button type="submit" class="btn primary">Submit</button>
            </div>
          </form>
        </div>
      </div>

      <div v-if="enlargedImage" class="modal-overlay" @click="closeImage">
        <img :src="enlargedImage" alt="Enlarged view" class="enlarged-image" />
      </div>
    </main>

    <button class="floating-btn" @click="showUploadForm = true">
      <span class="plus-icon">+</span>
    </button>
  </div>
</template>

<script>
import Map from "./map.vue";
import axios from "axios";

export default {
  components: { Map },
  data() {
  return {
    newItem: {
      item_name: "",
      status: "",
      category: "",
      lost_date: "",
      found_date: "",
      description: "",
      facebook_link: "",
      contact_number: "",
      location: null,
      image_url: null,
      user_id: null,
    },
    posts: [
      // Sample post to be displayed initially
      {
        id: 1,
        item_name: "Lost Wallet",
        status: "Lost",
        category: "Wallets",
        lost_date: "2024-12-18",
        found_date: "",
        description: "A black leather wallet with important cards and cash.",
        facebook_link: "https://facebook.com/sample",
        contact_number: "123-456-7890",
        image_url: "https://via.placeholder.com/150",  // Replace with a valid image URL or placeholder
        isFound: false,
      },
      // You can add more sample posts here if needed.
    ],
    filteredPosts: [],
    searchQuery: "",
    showUploadForm: false,
    enlargedImage: null,
  };
},
  created() {
    this.fetchPosts();
    this.fetchUserId();
  },
  methods: {
    async fetchUserId() {
      try {
        const response = await axios.get(window.userID);
        this.newItem.user_id = response.data.id;
      } catch (error) {
        console.error("Error fetching user ID:", error.message);
      }
    },
    async fetchPosts() {
      try {
        const [lostResponse, foundResponse] = await Promise.all([
          axios.get(window.lostItemsUrl),
          axios.get(window.foundItemsUrl),
        ]);
        const lostPosts = lostResponse.data.map(post => ({ ...post, isFound: false }));
        const foundPosts = foundResponse.data.map(post => ({ ...post, isFound: true }));
        this.posts = [...lostPosts, ...foundPosts];
        this.filterPosts();
      } catch (error) {
        console.error("Error fetching posts:", error.message);
      }
    },
    filterPosts() {
      this.filteredPosts = this.posts.filter((post) =>
        post.item_name.toLowerCase().includes(this.searchQuery.toLowerCase())
      );
    },
    handleFileUpload(event) {
      const file = event.target.files[0];
      if (file) {
        this.newItem.image_url = file;  // Store the raw file for later use (upload)

        // Create a separate property for the image preview URL
        const reader = new FileReader();
        reader.onload = (e) => {
          this.newItem.image_preview_url = e.target.result;  // Store the base64 preview URL
        };
        reader.readAsDataURL(file);
      }
    },
    resetNewItem() {
      this.newItem = {
        item_name: "",
        status: "",
        category: "",
        lost_date: "",
        found_date: "",
        description: "",
        facebook_link: "",
        contact_number: "",
        location: null,
        image_url: null,
        user_id: this.newItem.user_id,
      };
    },
    updateLocation(location) {
      this.newItem.location = location;
    },
    previewImage(event) {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
          this.newItem.image_url = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    },
    async submitForm() {
      try {
        const formData = new FormData();
        for (const key in this.newItem) {
          formData.append(key, this.newItem[key]);
        }
        const url =
          this.newItem.status === "Lost" ? window.lostItemsStore : window.foundItemsStore;

        await axios.post(url, formData, {
          headers: {
            "Content-Type": "multipart/form-data",
            "X-CSRF-TOKEN": document
              .querySelector('meta[name="csrf-token"]')
              .getAttribute("content"),
          },
        });

        alert("Item stored successfully!");
        this.fetchPosts();
        this.closeUploadForm();
      } catch (error) {
        console.error("Error submitting form:", error.message);
      }
    },
    closeUploadForm() {
      this.showUploadForm = false;
      this.resetNewItem();
    },
    enlargeImage(imageUrl) {
      this.enlargedImage = imageUrl;
    },
    closeImage() {
      this.enlargedImage = null;
    },
    closeUploadForm() {
      this.showUploadForm = false;
      this.resetNewItem();
    },
    async deletePost(postId) {
      if (confirm("Are you sure you want to delete this post?")) {
        try {
          const post = this.posts.find((post) => post.id === postId);
          const endpoint =
            post.isFound === true ? `/found-items/${postId}` : `/lost-items/${postId}`;
          await axios.delete(endpoint);

          this.posts = this.posts.filter((post) => post.id !== postId);
          this.filterPosts();
          alert("Post deleted successfully.");
        } catch (error) {
          console.error("Error deleting post:", error.message);
        }
      }
    },
  },
};
</script>


<style scoped>
/* General Dashboard Layout */
.dashboard {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
  height: 100vh;
  background-color: #f8f9fa; /* Light gray background for the dashboard */
}

.dashboard-header {
  margin-bottom: 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.dashboard-header h1 {
  font-size: 2rem;
  font-weight: bold;
  color: #333;
}

.search-bar input {
  width: 100%;
  max-width: 400px;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 1rem;
  color: #555;
  background-color: #fff;
  transition: border-color 0.3s ease;
}

.search-bar input:focus {
  border-color: #008080; /* Focus effect on input */
  outline: none;
}

/* Featured Posts Section */
.featured-posts {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.card {
  background: #fff;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  position: relative;
  transition: transform 0.3s ease;
}

.card:hover {
  transform: translateY(-5px); /* Subtle hover effect */
}

.card-image {
  width: 100%;
  height: 200px;
  object-fit: cover;
  border-radius: 8px;
  margin-bottom: 15px;
  cursor: pointer;
  transition: opacity 0.3s ease;
}

.card-image:hover {
  opacity: 0.8; /* Hover effect on image */
}

.card p {
  margin: 10px 0;
  color: #555;
}

.card .post-date {
  font-size: 0.9rem;
  color: #888;
}

.card .post-info {
  font-size: 0.9rem;
  color: #555;
}

.card .post-info strong {
  font-weight: bold;
}

.delete-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background: #e74c3c;
  color: white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.delete-btn:hover {
  background-color: #c0392b;
}

/* Modal (Upload Form) */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: #fff;
  padding: 25px;
  border-radius: 8px;
  width: 100%;
  max-width: 900px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  0% {
    opacity: 0;
    transform: translateY(20px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

h2 {
  font-size: 24px;
  font-weight: bold;
  margin-bottom: 20px;
  color: #333;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
}

.form-column {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  font-size: 1rem;
  font-weight: 500;
  color: #333;
  margin-bottom: 8px;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 1rem;
  color: #333;
  background-color: #fff;
  transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  border-color: #008080;
  outline: none;
}

/* Image Preview */
.image-preview {
  max-width: 200px;
  max-height: 200px;
  margin-top: 10px;
  border-radius: 5px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.radio-group {
  display: flex;
  gap: 20px;
  font-size: 1rem;
}

.radio-group label {
  display: flex;
  align-items: center;
  color: #333;
}

.radio-group input[type="radio"] {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: 2px solid #008080;
  background-color: #fff;
  margin-right: 8px;
  cursor: pointer;
}

.modal-actions .btn.primary:hover {
  background-color: #347ea3;
  transform: translateY(-2px);
}

/* Modal Content */
.modal-content {
  background-color: #fff;
  border-radius: 8px;
  overflow: hidden;
  margin-top: 20px;
}

.map-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 99;
}

.location-status {
  position: absolute;
  top: 15px;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(255, 255, 255, 0.8);
  padding: 8px 20px;
  border-radius: 30px;
  font-size: 14px;
  color: #333;
}

.floating-btn {
  position: fixed;
  bottom: 30px;
  right: 30px;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: #008080;
  color: white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
  z-index: 1000;
  transition: transform 0.3s ease;
}

.floating-btn:hover {
  transform: translateY(-5px);
}

.submit-btn,
.cancel-btn {
  padding: 12px 20px;
  border-radius: 5px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.submit-btn {
  background-color: #008080;
  color: white;
  border: none;
}

.submit-btn:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.submit-btn:hover:not(:disabled) {
  background-color: #007373;
}

.cancel-btn {
  background-color: #6c757d;
  color: white;
  border: none;
}

.cancel-btn:hover {
  background-color: #5a6268;
}

/* Modal Cancel and Submit Button Spacing */
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

/* Spinner */
.spinner {
  width: 20px;
  height: 20px;
  border: 3px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: #fff;
  animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>