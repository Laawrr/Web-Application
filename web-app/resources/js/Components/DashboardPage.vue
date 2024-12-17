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
      posts: [],
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
            post.status === "Found" ? `/found-items/${postId}` : `/lost-items/${postId}`;
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
.dashboard {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
  height: 100vh;
}

.dashboard-header {
  margin-bottom: 30px;
}

.search-bar input {
  width: 100%;
  max-width: 400px;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.featured-posts {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.card {
  background: white;
  border-radius: 8px;
  padding: 15px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  position: relative;
}

.card-image {
  width: 100%;
  height: 200px;
  object-fit: cover;
  border-radius: 4px;
  margin-bottom: 10px;
  cursor: pointer;
}

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
  background: white;
  padding: 20px;
  border-radius: 8px;
  width: 90%;
  max-width: 1000px;
  max-height: 90vh;
  overflow-y: auto;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}

.form-column {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.form-select {
  height: 38px;
  background-color: white;
}

.radio-group {
  display: flex;
  gap: 30px;
}

.radio-group label {
  display: flex;
  align-items: center;
}

/* Customize the radio buttons to be round */
.radio-group input[type="radio"] {
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  /* This makes the radio button round */
  border: 2px solid #4CAF50;
  /* Green border */
  background-color: white;
  margin-right: 5px;
  cursor: pointer;
  position: relative;
}

.radio-group input[type="radio"]:checked {
  background-color: #4CAF50;
  /* Green background when checked */
  border-color: #4CAF50;
  /* Green border when checked */
}

.radio-group input[type="radio"]:checked::before {
  content: '';
  position: absolute;
  top: 5px;
  left: 5px;
  width: 8px;
  height: 8px;
  background-color: white;
  /* Inner white circle */
  border-radius: 50%;
}

.map-wrapper {
  height: 780px;
  border-radius: 4px;
  overflow: hidden;
  position: relative;
}

.image-preview {
  max-width: 200px;
  max-height: 200px;
  margin-top: 10px;
  border-radius: 4px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
  padding: 15px 0;
  background: white;
}

.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn.primary {
  background: #008080;
  color: white;
}

.btn.secondary {
  background: #9e9e9e;
  color: white;
}

.floating-btn {
  position: fixed;
  bottom: 30px;
  right: 30px;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: #008080;
  color: white;
  border: none;
  cursor: pointer;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  z-index: 100;
}

.delete-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: rgba(255, 0, 0, 0.7);
  color: white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.enlarged-image {
  max-width: 90%;
  max-height: 90vh;
  object-fit: contain;
}
</style>