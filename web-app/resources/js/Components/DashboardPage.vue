<template>
  <div class="dashboard-container" :style="containerStyle">
    <div>
      <main class="dashboard">
        <!-- Dashboard Header -->
        <section class="dashboard-header">
          <div class="search-bar">
            <input type="text" v-model="searchQuery" placeholder="Search for lost items..." @input="filterPosts" />
          </div>
        </section>

        <!-- Featured Posts -->
        <section class="featured-posts">
  <div v-if="filteredPosts.length === 0" class="no-posts">
    <p>No posts found.</p>
  </div>
  
  <div v-for="post in filteredPosts" :key="post.id" class="card clickable" @click="openPostModal(post)">
    <img v-if="post.image_url" :src="post.image_url" alt="Item Image" class="card-image" />
    <p class="post-date">{{ post.lost_date || post.found_date }}</p>
    <p class="post-info"><strong>Status:</strong> {{ post.isFound ? 'Found' : 'Lost' }}</p>
    <p class="post-info"><strong>Category:</strong> {{ post.category }}</p>
    <p class="post-info"><strong>Description:</strong> {{ post.description }}</p>

    <!-- Delete button -->
    <button @click.stop="deletePost(post.id)" class="delete-button">Delete</button>
  </div>
</section>


        <!-- Post Detail Modal -->
        <div v-if="showPostModal" class="post-modal__overlay" @click="closePostModal">
          <div class="post-modal__content" @click.stop>
            <h2 class="post-modal__title">Item Details</h2>

            <img v-if="currentPost.image_url" :src="currentPost.image_url" alt="Item Image" class="post-modal__image" />

            <div class="post-modal__info">
              <p class="post-modal__text"><strong>Item Name:</strong> {{ currentPost.item_name }}</p>
              <p class="post-modal__text"><strong>Status:</strong> {{ currentPost.isFound ? 'Found' : 'Lost' }}</p>
              <p class="post-modal__text"><strong>Category:</strong> {{ currentPost.category }}</p>
              <p class="post-modal__text"><strong>Description:</strong> {{ currentPost.description }}</p>
              <p class="post-modal__text"><strong>Facebook:</strong> {{ currentPost.facebook_link }}</p>
              <p class="post-modal__text"><strong>Contact:</strong> {{ currentPost.contact_number }}</p>
            </div>

            <!-- Edit Button -->
            <button @click="openEditModal" class="post-modal__edit-btn">Edit</button>

            <!-- Close Button -->
            <button @click="closePostModal" class="post-modal__close-btn">Close</button>
          </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="showEditModal" class="edit-modal__overlay" @click="closeEditModal">
          <div class="edit-modal__content" @click.stop>
            <h2 class="edit-modal__title">Edit Item</h2>

            <form @submit.prevent="submitEditForm">
              <div class="form-group">
                <label for="editItemName">Item Name</label>
                <input type="text" id="editItemName" v-model="currentPost.item_name" required />
              </div>
              <div class="form-group">
                <label for="editDescription">Description</label>
                <textarea id="editDescription" v-model="currentPost.description" required></textarea>
              </div>
              <div class="form-group">
                <label for="editFacebook">Facebook</label>
                <input type="text" id="editFacebook" v-model="currentPost.facebook_link" required />
              </div>
              <div class="form-group">
                <label for="editContact">Contact</label>
                <input type="text" id="editContact" v-model="currentPost.contact_number" required />
              </div>

              <div class="form-actions">
                <button type="submit" class="submit-btn">Save Changes</button>
                <button type="button" @click="closeEditModal" class="cancel-btn">Cancel</button>
              </div>
            </form>

            <!-- Close Button for Edit Modal -->
            <button @click="closeEditModal" class="edit-modal__close-btn">Close</button>
          </div>
        </div>

        <!-- Upload Form Modal (unchanged) -->
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

                <!-- Right Column -->
                <div class="form-column">
                  <div class="map-wrapper">
                    <div class="map-area">
                      <div class="map-container" :class="{ enabled: mapEnabled }">
                        <Map ref="mapComponent" @location-selected="updateLocation" :disabled="!mapEnabled" />
                        <div class="map-blur-overlay" :class="{ enabled: mapEnabled }"></div>
                      </div>
                      <div class="map-controls">
                        <div class="map-overlay" v-if="!mapEnabled">
                          <button class="add-location-btn" @click="enableLocationSelection">
                            <i class="fas fa-map-marker-alt"></i> Add Location (Click to Enable Map)
                          </button>
                        </div>
                        <div v-if="locationSelected" class="location-status" style="margin-bottom: 320px" >
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
import axios from "axios";
import Map from "./map.vue";
import { debounce } from "lodash";

export default {
  components: { Map },
  data() {
    return {
      isLoading: false,
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
        minHeight: "100vh",
        paddingBottom: "100px", // Initial padding
      },
      showPostModal: false,
      showEditModal: false,
      currentPost: {},
    };
  },
  created() {
    this.debouncedFilter = debounce(this.filterPosts, 300);
    this.fetchUserId().then(() => this.fetchPosts());
  },
  watch: {
    posts: {
      handler(newPosts) {
        this.updateSpacing(newPosts);
      },
      deep: true,
    },
  },
  methods: {
    async fetchUserId() {
      try {
        const response = await axios.get(window.userID);
        this.newItem.user_id = response.data.id;
      } catch (error) {
        console.error("Error fetching user ID:", error.message);
        this.showError("Failed to fetch user ID");
      }
    },
    async fetchPosts() {
      this.isLoading = true;
      try {
        const [lostResponse, foundResponse] = await Promise.all([ 
          axios.get(window.lostItemsUrl), 
          axios.get(window.foundItemsUrl) 
        ]);

        const processItems = (items, isFound) => {
          return items
            .filter((post) => post.user_id === this.newItem.user_id)
            .map((post) => ({ ...post, isFound }));
        };

        const lostPosts = processItems(lostResponse.data, false);
        const foundPosts = processItems(foundResponse.data, true);

        this.posts = [...lostPosts, ...foundPosts];
        this.filterPosts();
      } catch (error) {
        console.error("Error fetching posts:", error.message);
        this.showError("Failed to fetch posts");
      } finally {
        this.isLoading = false;
      }
    },
    filterPosts() {
      const query = this.searchQuery.toLowerCase();
      this.filteredPosts = query
        ? this.posts.filter(
            (post) =>
              post.item_name.toLowerCase().includes(query) ||
              post.description.toLowerCase().includes(query) ||
              post.category.toLowerCase().includes(query)
          )
        : [...this.posts];
    },
    handleFileUpload(event) {
      const file = event.target.files[0];
      if (file) {
        this.newItem.image_file = file;
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

        if (this.newItem.image_file) {
          formData.append("image_url", this.newItem.image_file);
        }

        const formFields = {
          item_name: this.newItem.item_name,
          status: this.newItem.status,
          category: this.newItem.category,
          description: this.newItem.description,
          facebook_link: this.newItem.facebook_link,
          contact_number: this.newItem.contact_number,
          user_id: this.newItem.user_id,
          latitude: this.newItem.location.lat,
          longitude: this.newItem.location.lng,
        };

        formFields.location = `${this.newItem.location.lat},${this.newItem.location.lng}`;

        if (this.newItem.status === "Lost") {
          formFields.lost_date = this.newItem.lost_date;
        } else {
          formFields.found_date = this.newItem.found_date;
        }

        Object.keys(formFields).forEach((key) => {
          if (formFields[key] !== null && formFields[key] !== undefined) {
            formData.append(key, formFields[key]);
          }
        });

        const token = document.head.querySelector('meta[name="csrf-token"]');

        if (!token) {
          this.showError("CSRF token not found. Please refresh the page.");
          return;
        }

        formData.append("_token", token.content);

        const url =
          this.newItem.status === "Lost" ? window.lostItemsStore : window.foundItemsStore;

        const response = await axios.post(url, formData, {
          headers: {
            "Content-Type": "multipart/form-data",
            "X-CSRF-TOKEN": token.content,
            "X-Requested-With": "XMLHttpRequest",
          },
          withCredentials: true,
        });

        this.showSuccess("Post created successfully!");
        this.closeUploadForm();
        await this.fetchPosts();
      } catch (error) {
        if (error.response && error.response.status === 422) {
          const validationErrors = error.response.data.errors;
          const errorMessages = Object.values(validationErrors)
            .flat()
            .join("\n");
          this.showError("Validation failed:\n" + errorMessages);
        } else {
          this.showError("Error creating post: " + error.message);
        }
      } finally {
        this.isSubmitting = false;
      }
    },
    showError(message) {
      const errorLines = message.split("\n");
      const formattedMessage = errorLines.join("\n• ");
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
  // Confirm the deletion action
  const confirmDelete = confirm('Are you sure you want to delete this post?');
  if (!confirmDelete) return;

  // Optimistic UI update: Remove the post from the UI immediately
  const postToDelete = this.posts.find(post => post.id === postId);
  const filteredPostToDelete = this.filteredPosts.find(post => post.id === postId);

  // Remove the post from posts and filteredPosts immediately
  this.posts = this.posts.filter(post => post.id !== postId);
  this.filteredPosts = this.filteredPosts.filter(post => post.id !== postId);

  try {
    // Send the DELETE request with the correct URL
    const response = await axios.delete(`/found-items/${postId}`);  // This matches your route in web.php
    
    if (response.status === 200) {
      // If successful, show the success message and do nothing since the post was already removed
      alert('Post deleted successfully!');
    } 
  } catch (error) {
    // If an error occurs, show the error and restore the post
    console.error('Error deleting post:', error);
    alert('An error occurred while deleting the post.');
    this.posts.push(postToDelete);  // Restore the post to the posts array
    this.filteredPosts.push(filteredPostToDelete);  // Restore the post to the filteredPosts array
  }
},
    enableLocationSelection() {
      this.mapEnabled = !this.mapEnabled;
    },
    updateSpacing(newPosts) {
      if (newPosts.length < 5) {
        this.containerStyle.paddingBottom = "100px";
      } else {
        this.containerStyle.paddingBottom = "150px";
      }
    },
    openPostModal(post) {
      this.currentPost = post;
      this.showPostModal = true;
    },
    closePostModal() {
      this.showPostModal = false;
      this.currentPost = {};
    },
    openEditModal() {
      this.showEditModal = true;
    },
    closeEditModal() {
      this.showEditModal = false;
    },
    submitEditForm() {
      // Handle the form submission logic for editing a post
      this.showEditModal = false;
      this.showSuccess("Post updated successfully!");
    }
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

/* ======== Modal Overlay ======== */
.post-modal__overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  /* Dark overlay for depth */
  backdrop-filter: blur(8px);
  /* Blurred background for a modern look */
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  /* Make sure it stays on top */
  animation: fadeIn 0.4s ease-in-out;
}

/* ======== Modal Content ======== */
.post-modal__content {
  background: #ffffff;
  /* Clean white background */
  border-radius: 20px;
  padding: 30px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
  /* Subtle shadow for depth */
  position: relative;
  text-align: center;
  animation: slideIn 0.5s ease-in-out;
  overflow: hidden;
  transition: all 0.3s ease;
}

.post-modal__title {
  font-size: 1.8rem;
  font-weight: bold;
  color: #333;
  margin-bottom: 20px;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.post-modal__info {
  margin-bottom: 20px;
  text-align: left;
}

.post-modal__text {
  font-size: 1rem;
  color: #555;
  margin-bottom: 15px;
  line-height: 1.6;
}

.post-modal__text strong {
  color: #222;
  font-weight: bold;
}

.post-modal__image {
  width: 100%;
  border-radius: 15px;
  margin-bottom: 20px;
  max-height: 200px;
  object-fit: cover;
  /* Ensures the image stays within bounds */
}

/* ======== Close Button ======== */
.post-modal__close-btn {
  position: absolute;
  top: 20px;
  right: 20px;
  background: #ff4b5c;
  color: #fff;
  border: none;
  border-radius: 50%;
  width: 50px;
  height: 40px;
  font-size: 1.2rem;
  font-weight: bold;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  transition: all 0.3s ease;
}

.post-modal__close-btn:hover {
  background: #ff2c3c;
}

.post-modal__close-btn:active {
  transform: scale(0.95);
}

/* ======== Animations ======== */
@keyframes fadeIn {
  from {
    opacity: 0;
  }

  to {
    opacity: 1;
  }
}

@keyframes slideIn {
  from {
    transform: translateY(-20px);
    opacity: 0;
  }

  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.dashboard-header {
  margin-bottom: 30px;
}

.dashboard {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
  position: relative;
}

.search-bar {
  display: flex;
  justify-content: center;
  /* Centers the search bar horizontally */
  margin-bottom: 30px;
  /* Adds spacing between the search bar and other content */
}

.search-bar input {
  width: 100%;
  max-width: 500px;
  /* Increase width for a larger search bar */
  padding: 12px 20px;
  /* More padding for a larger input field */
  border: 2px solid #ddd;
  border-radius: 30px;
  /* Rounded corners for a more modern look */
  font-size: 1rem;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.search-bar input:focus {
  border-color: #008080;
  /* Green border when focused */
  box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
  /* Light green shadow on focus */
}

.search-bar input::placeholder {
  color: #888;
  /* Lighter color for the placeholder */
  font-style: italic;
  font-size: 1rem;
  /* Adjust placeholder text size */
}

/* Add styles for the button (if applicable) */
.search-bar button {
  padding: 12px 20px;
  margin-left: 10px;
  background-color: #008080;
  color: white;
  border: none;
  border-radius: 30px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.search-bar button:hover {
  background-color: #006666;
}

.search-bar button:active {
  background-color: #004d4d;
}

.featured-posts {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
  margin-bottom: 40px;
}

.card {
  background: white;
  border-radius: 10px;
  /* Increased border radius for rounded corners */
  padding: 20px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  /* Slightly larger shadow for better depth */
  position: relative;
  margin-bottom: 20px;
  transition: all 0.3s ease-in-out;
  color: #008080;
}

.card:hover {
  box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
  /* Hover effect: increase shadow */
  transform: translateY(-5px);
  /* Slight lift on hover */
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

.delete-button {
  background-color: red;
  color: white;
  border: none;
  padding: 5px 10px;
  cursor: pointer;
  margin-top: 10px;
  font-size: 14px;
}

.delete-button:hover {
  background-color: darkred;
}


.enlarged-image {
  max-width: 90%;
  max-height: 90vh;
  object-fit: contain;
}

/* ======== Edit Modal ======== */
.edit-modal__overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  /* Dark overlay */
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.edit-modal__content {
  background: #ffffff;
  border-radius: 20px;
  padding: 30px;
  width: 80%;
  max-width: 600px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  position: relative;
  text-align: center;
  animation: slideIn 0.5s ease-in-out;
}

/* Edit Modal Title */
.edit-modal__title {
  font-size: 1.8rem;
  font-weight: bold;
  color: #333;
  margin-bottom: 20px;
}

/* Edit Modal Close Button */
.edit-modal__close-btn {
  position: absolute;
  top: 20px;
  right: 20px;
  background: #ff4b5c;
  color: #fff;
  border: none;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  font-size: 1.2rem;
  font-weight: bold;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  transition: all 0.3s ease;
}

.edit-modal__close-btn:hover {
  background: #ff2c3c;
  transform: rotate(90deg);
}

.edit-modal__close-btn:active {
  transform: scale(0.95);
}

/* ======== Edit Button in the Item Modal ======== */
.post-modal__edit-btn {
  position: absolute;
  top: 20px;
  left: 20px;
  background: #008080;
  color: #fff;
  border: none;
  border-radius: 50%;
  width: 45px;
  height: 40px;
  font-size: 1.2rem;
  font-weight: bold;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  transition: all 0.3s ease;
}

.post-modal__edit-btn:hover {
  background: #45a049;
}

.post-modal__edit-btn:active {
  transform: scale(0.95);
}

/* Styling for input fields and form */
.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.submit-btn {
  background-color: #008080;
  color: white;
  border: none;
  padding: 10px 20px;
  margin-right: 10px;
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
</style>