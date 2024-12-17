<template>
  <div class="dashboard-container" :style="containerStyle">
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

            <h2 style="font-size: 25px; font-weight: bolder;" class="mb-3">Add Item</h2>

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
                  <div class="map-wrapper">
                    <div class="map-area">
                      <div class="map-container" :class="{ enabled: mapEnabled }">
                        <Map ref="mapComponent" @location-selected="updateLocation" :disabled="false" />
                        <div class="map-blur-overlay" :class="{ enabled: mapEnabled }"></div>
                      </div>
                      <div class="map-controls">
                        <div class="map-overlay" v-if="!locationSelected">
                          <button class="add-location-btn" @click="enableLocationSelection">
                            <i class="fas fa-map-marker-alt"></i>
                            Add Location (Click to Enable Map)
                          </button>
                        </div>
                        <div v-if="locationSelected" class="location-status" style="margin-bottom: 340px">
                          <span v-if="newItem.location">Location selected ✓</span>
                          <span v-else>Click on the map to place a pin</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-actions">
                <button type="button" @click="closeUploadForm" class="cancel-btn">Cancel</button>
                <button type="submit" class="submit-btn" :disabled="isSubmitting || !newItem.location">
                  <span v-if="isSubmitting" class="spinner"></span>
                  <span v-else>Submit</span>
                </button>
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
        image_file: null,
        image_preview_url: null,
        user_id: null,
      },
      posts: [],
      filteredPosts: [],
      searchQuery: "",
      showUploadForm: false,
      enlargedImage: null,
      locationSelected: false,
      mapEnabled: false,
      isSubmitting: false,
      containerStyle: {
        minHeight: '100vh',
        paddingBottom: '100px', // Initial padding
      }
    };
  },
  created() {
    this.fetchPosts();
    this.fetchUserId();
  },
  watch: {
    posts: {
      handler(newPosts) {
        this.updateSpacing(newPosts);
      },
      deep: true
    }
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
        this.newItem.image_file = file;
        
        // Create a preview URL
        const reader = new FileReader();
        reader.onload = (e) => {
          this.newItem.image_preview_url = e.target.result;
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
        image_file: null,
        image_preview_url: null,
        user_id: this.newItem.user_id,
      };
    },
    updateLocation(location) {
      this.newItem.location = location;
      this.locationSelected = true;
    },
    async submitForm() {
      if (!this.newItem.location) {
        this.showError("Please select a location on the map first");
        return;
      }
      
      this.isSubmitting = true;
      try {
        const formData = new FormData();
        
        // Handle the image file
        if (this.newItem.image_file) {
          formData.append('image_url', this.newItem.image_file); 
        }
        
        // Handle all other form fields
        const formFields = {
          item_name: this.newItem.item_name,
          status: this.newItem.status,
          category: this.newItem.category,
          description: this.newItem.description,
          facebook_link: this.newItem.facebook_link,
          contact_number: this.newItem.contact_number,
          user_id: this.newItem.user_id,
          latitude: this.newItem.location.lat,
          longitude: this.newItem.location.lng
        };

        formFields.location = `${this.newItem.location.lat},${this.newItem.location.lng}`;

        // Add lost_date or found_date based on status
        if (this.newItem.status === 'Lost') {
          formFields.lost_date = this.newItem.lost_date;
        } else {
          formFields.found_date = this.newItem.found_date;
        }
        
        // Append all form fields
        Object.keys(formFields).forEach(key => {
          if (formFields[key] !== null && formFields[key] !== undefined) {
            formData.append(key, formFields[key]);
          }
        });

        // Get CSRF token from meta tag
        const token = document.head.querySelector('meta[name="csrf-token"]');
        
        if (!token) {
          this.showError("CSRF token not found. Please refresh the page.");
          return;
        }

        // Add CSRF token to form data
        formData.append('_token', token.content);

        const url = this.newItem.status === "Lost" ? window.lostItemsStore : window.foundItemsStore;

        const response = await axios.post(url, formData, {
          headers: {
            "Content-Type": "multipart/form-data",
            "X-CSRF-TOKEN": token.content,
            "X-Requested-With": "XMLHttpRequest"
          },
          withCredentials: true
        });

        this.showSuccess("Post created successfully!");
        this.closeUploadForm();
        await this.fetchPosts(); 
      } catch (error) {
        console.error('Form Data:', this.newItem); 
        if (error.response && error.response.status === 422) {
          // Handle validation errors
          const validationErrors = error.response.data.errors;
          const errorMessages = Object.values(validationErrors)
            .flat()
            .join('\n');
          this.showError("Validation failed:\n" + errorMessages);
        } else {
          this.showError("Error creating post: " + error.message);
        }
      } finally {
        this.isSubmitting = false;
      }
    },
    
    showError(message) {
      // Replace alert with a more user-friendly error display
      const errorLines = message.split('\n');
      const formattedMessage = errorLines.join('\n• ');
      alert("• " + formattedMessage);
    },
    showSuccess(message) {
      alert(message);
    },
    closeUploadForm() {
      this.showUploadForm = false;
      this.resetNewItem();
      this.locationSelected = false;
      this.mapEnabled = false;
    },
    enlargeImage(imageUrl) {
      this.enlargedImage = imageUrl;
    },
    closeImage() {
      this.enlargedImage = null;
    },
    async deletePost(postId) {
      if (confirm("Are you sure you want to delete this post?")) {
        try {
          const post = this.posts.find(p => p.id === postId);
          const url = post.isFound ? window.foundItemsUrl : window.lostItemsUrl;
          await axios.delete(`${url}/${postId}`, {
            headers: {
              "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
            },
          });
          
          // Remove post from both arrays
          this.posts = this.posts.filter(post => post.id !== postId);
          this.filteredPosts = this.filteredPosts.filter(post => post.id !== postId);
          
          alert("Post deleted successfully!");
        } catch (error) {
          console.error("Error deleting post:", error);
          alert("Error deleting post: " + error.message);
        }
      }
    },
    enableLocationSelection() {
      this.locationSelected = true;
      this.mapEnabled = true;
    },
    updateSpacing(posts) {
      const baseSpacing = 100; // Base padding
      const rowCount = Math.ceil(posts.length / 3);
      const additionalSpacing = rowCount > 2 ? (rowCount - 2) * 20 : 0; // Add 20px for each row beyond 2
      this.containerStyle.paddingBottom = `${baseSpacing + additionalSpacing}px`;
    },
  },
};
</script>

<style scoped>
.dashboard-container {
  padding: 20px;
  background-color: #f5f5f5;
  transition: all 0.3s ease;
  min-height: 100vh;
  position: relative;
}

.dashboard {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
  position: relative;
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
  margin-bottom: 40px;
}

.card {
  background: white;
  border-radius: 8px;
  padding: 15px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  position: relative;
  margin-bottom: 20px;
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
  background: white;
  border-radius: 8px;
  overflow: hidden;
  position: relative;
  width: 100%;
  height: 750px;
}

.map-area {
  position: relative;
  height: 100%;
  width: 100%;
}

.map-container {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  width: 100%;
  height: 100%;
}

.map-container.enabled {
  z-index: 2;
}

.map-blur-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  width: 100%;
  height: 100%;
  backdrop-filter: blur(8px);
  background: rgba(255, 255, 255, 0.5);
  z-index: 3;
  transition: all 0.3s ease;
}

.map-blur-overlay.enabled {
  opacity: 0;
  pointer-events: none;
  z-index: 1;
}

.map-controls {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 4;
  pointer-events: none;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
}

.map-overlay {
  pointer-events: auto;
}

.location-status {
  pointer-events: auto;
  background: white;
  padding: 8px 16px;
  border-radius: 4px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.add-location-btn {
  background-color: #4fb9af;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: background-color 0.3s ease;
}

.add-location-btn:hover {
  background-color: #45a399;
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
  margin-top: 20px;
}

.spinner {
  display: inline-block;
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

.submit-btn {
  background-color: #008080;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  min-width: 100px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.submit-btn:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.cancel-btn {
  background-color: #6c757d;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  margin-right: 10px;
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