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
                <div class="form-group">
                  <div class="map-wrapper">
                    <div class="map-overlay" v-if="!locationSelected">
                      <button class="add-location-btn" @click="enableLocationSelection">
                        <i class="fas fa-map-marker-alt"></i>
                        Add Location (Click to Enable Map)
                      </button>
                    </div>
                    <div v-if="locationSelected" class="location-status">
                      <span v-if="newItem.location">Location selected ✓</span>
                      <span v-else>Click on the map to place a pin</span>
                    </div>
                    <Map ref="mapComponent" @location-selected="updateLocation" :disabled="!locationSelected" />
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
      isSubmitting: false,
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

.radio-group input[type="radio"]:checked {
  background-color: #008080;
}

.map-wrapper {
  position: relative;
  width: 100%;
  height: 450px;
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
