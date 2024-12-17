<template>
  <div>
    <!-- Header Bar -->
    <HeaderBar />

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
        <div v-for="post in filteredPosts" :key="post.id" class="card" @click="showDetails(post)">
          <div class="card-content">
            <img v-if="post.image_url" :src="post.image_url" alt="Item Image" class="card-image" />
            <div class="card-text">
              <p class="post-date">{{ post.lost_date || post.found_date }}</p>
              <div class="post-info">
                <p><strong>Status:</strong> {{ post.isFound ? 'Found' : 'Lost' }}</p>
                <p><strong>Category:</strong> {{ post.category }}</p>
                <p><strong>Description:</strong> {{ post.description }}</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Details Modal -->
      <div v-if="showDetailModal" class="modal-overlay" @click="closeDetailModal">
        <div class="modal-content" @click.stop>
          <!-- Modal Header -->
          <h2 class="modal-title">{{ selectedPost.item_name }}</h2>

          <!-- Modal Image Section -->
          <div class="modal-image">
            <img v-if="selectedPost.image_url" :src="selectedPost.image_url" alt="Item Image" class="card-image" />
          </div>

          <!-- Edit Form (Conditional) -->
          <div v-if="isEditing" class="modal-form">
            <form @submit.prevent="saveChanges">
              <div class="form-group">
                <label for="edit-item-name">Item Name</label>
                <input type="text" id="edit-item-name" v-model="selectedPost.item_name" required class="input-field" />
              </div>
              <div class="form-group">
                <label for="edit-status">Status</label>
                <select v-model="selectedPost.isFound" required class="input-field">
                  <option :value="true">Found</option>
                  <option :value="false">Lost</option>
                </select>
              </div>
              <div class="form-group">
                <label for="edit-category">Category</label>
                <input type="text" id="edit-category" v-model="selectedPost.category" required class="input-field" />
              </div>
              <div class="form-group">
                <label for="edit-description">Description</label>
                <input type="text" id="edit-description" v-model="selectedPost.description" required class="input-field" />
              </div>
              <div class="form-group">
                <label for="edit-facebook-link">Facebook Link</label>
                <input type="url" id="edit-facebook-link" v-model="selectedPost.facebook_link" class="input-field" />
              </div>
              <div class="form-group">
                <label for="edit-contact-number">Contact Number</label>
                <input type="text" id="edit-contact-number" v-model="selectedPost.contact_number" class="input-field" />
              </div>
              <div class="form-group">
                <label for="edit-image-url">Item Image</label>
                <input type="file" id="edit-image-url" @change="handleImageChange" class="input-file" />
              </div>
              <div v-if="selectedPost.image_url">
                <img :src="selectedPost.image_url" alt="Image Preview" class="image-preview" />
              </div>
              <div class="modal-actions">
                <button type="submit" class="btn primary">Save Changes</button>
                <button type="button" @click="cancelEdit" class="btn secondary">Cancel</button>
                <button type="button" @click="deletePost" class="btn danger">Delete Item</button>
              </div>
            </form>
          </div>

          <!-- View Mode (when not editing) -->
          <div v-else class="modal-info">
            <p><strong>Status:</strong> {{ selectedPost.isFound ? 'Found' : 'Lost' }}</p>
            <p><strong>Category:</strong> {{ selectedPost.category }}</p>
            <p><strong>Description:</strong> {{ selectedPost.description }}</p>
            <p><strong>Facebook:</strong>
              <a :href="selectedPost.facebook_link" target="_blank" class="modal-link">{{ selectedPost.facebook_link }}</a>
            </p>
            <p><strong>Contact:</strong> {{ selectedPost.contact_number }}</p>
            <p><strong>Lost Date:</strong> {{ selectedPost.lost_date || 'N/A' }}</p>
            <p><strong>Found Date:</strong> {{ selectedPost.found_date || 'N/A' }}</p>

            <div class="modal-actions">
              <button @click="closeDetailModal" class="btn secondary">Close</button>
              <button @click="editPost" class="btn primary">Edit</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Upload Form Modal -->
      <button class="floating-btn" @click="showUploadForm = true">
        <span class="plus-icon">+</span>
      </button>

      <div v-if="showUploadForm" class="modal-overlay" @click="closeUploadForm">
        <div class="modal-content" @click.stop>
          <h2 class="modal-title">Upload Item</h2>
          <form @submit.prevent="submitForm">
            <div>
              <label for="item_name">Item Name</label>
              <input type="text" id="item_name" v-model="newItem.item_name" required />
            </div>
            <div>
              <label for="status">Status</label>
              <select v-model="newItem.status" required>
                <option value="Lost">Lost</option>
                <option value="Found">Found</option>
              </select>
            </div>
            <div>
              <label for="category">Category</label>
              <input type="text" id="category" v-model="newItem.category" required />
            </div>
            <div>
              <label for="description">Description</label>
              <textarea id="description" v-model="newItem.description" required></textarea>
            </div>
            <div>
              <label for="facebook_link">Facebook Link</label>
              <input type="url" id="facebook_link" v-model="newItem.facebook_link" />
            </div>
            <div>
              <label for="contact_number">Contact Number</label>
              <input type="text" id="contact_number" v-model="newItem.contact_number" />
            </div>
            <div>
              <label for="image_url">Item Image</label>
              <input type="file" id="image_url" @change="handleFileUpload" />
            </div>
            <div v-if="newItem.image_url">
              <img :src="newItem.image_preview_url" alt="Image Preview" class="image-preview" />
            </div>
            <div class="modal-actions">
              <button type="submit" class="btn primary">Submit</button>
              <button type="button" @click="closeUploadForm" class="btn secondary">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </main>

    <!-- Footer Bar -->
    <FooterBar />
  </div>
</template>


<script>
import axios from "axios";
import HeaderBar from '@/Components/HeaderBar.vue';
import FooterBar from '@/Components/FooterBar.vue';

export default {
  components: { HeaderBar, FooterBar },
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
        {
          id: 1,
          item_name: "Lost Wallet",
          status: "Lost",
          category: "Wallets",
          lost_date: "2024-12-15",
          found_date: "",
          description: "A black leather wallet with credit cards and ID.",
          facebook_link: "https://facebook.com/lostwallet",
          contact_number: "123-456-7890",
          image_url: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRZhPNCmXGk1VnWYPWmhvoTYwGjDRyfEMgobw&s",
          isFound: false,
        },
        {
          id: 2,
          item_name: "Found Phone",
          status: "Found",
          category: "Electronics",
          lost_date: "",
          found_date: "2024-12-16",
          description: "An iPhone 13 in a case, found near the library.",
          facebook_link: "https://facebook.com/foundphone",
          contact_number: "987-654-3210",
          image_url: "https://www.androidauthority.com/wp-content/uploads/2023/03/iPhone-14-Pro-in-hand.jpg",
          isFound: true,
        },
        {
          id: 3,
          item_name: "Lost Keys",
          status: "Lost",
          category: "Accessories",
          lost_date: "2024-12-14",
          found_date: "",
          description: "A set of house keys on a keychain.",
          facebook_link: "https://facebook.com/lostkeys",
          contact_number: "555-123-4567",
          image_url: "https://images.squarespace-cdn.com/content/v1/5eba27f35a793f6efa17541b/1591334996344-VL1XZ9RJVIGTCXFKCRBF/image-asset.jpeg",
          isFound: false,
        },
      ],
      filteredPosts: [],
      searchQuery: "",
      showUploadForm: false,
      enlargedImage: null,
      showDetailModal: false,
      selectedPost: null,
      isEditing: false, // To toggle between view and edit mode
      originalPost: null, // Store the original post to revert changes
    };
  },
  created() {
    this.filteredPosts = this.posts;
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
        const [lostResponse, foundResponse] = await Promise.all([axios.get(window.lostItemsUrl), axios.get(window.foundItemsUrl)]);
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
    showDetails(post) {
      this.selectedPost = { ...post }; // Create a copy of the post to avoid direct mutations
      this.originalPost = { ...post }; // Save original data for canceling
      this.isEditing = false;
      this.showDetailModal = true;
    },
    closeDetailModal() {
      this.showDetailModal = false;
      this.selectedPost = null;
    },
    editPost() {
      this.isEditing = true;
    },
    cancelEdit() {
      this.isEditing = false;
      // Revert to original post data
      this.selectedPost = { ...this.originalPost };
    },
    saveChanges() {
      // Save changes to the server
      axios
        .put(`/update-item/${this.selectedPost.id}`, this.selectedPost)
        .then(response => {
          this.isEditing = false;
          this.selectedPost = response.data; // Update the post with the new data
          alert("Item updated successfully!");
        })
        .catch(error => {
          console.error("Error updating item:", error);
        });
    },
    handleImageChange(event) {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
          this.selectedPost.image_url = e.target.result; // Update the image preview
        };
        reader.readAsDataURL(file);
      }
    },
    handleFileUpload(event) {
      const file = event.target.files[0];
      if (file) {
        this.newItem.image_url = file;
        const reader = new FileReader();
        reader.onload = (e) => {
          this.newItem.image_preview_url = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    },
    submitForm() {
      // Add logic to submit form
      console.log("Form submitted:", this.newItem);
      this.closeUploadForm();
    },
    closeUploadForm() {
      this.showUploadForm = false;
    },
    deletePost() {
    if (confirm("Are you sure you want to delete this item?")) {
      axios
        .delete(`/delete-item/${this.selectedPost.id}`)
        .then(() => {
          this.posts = this.posts.filter(post => post.id !== this.selectedPost.id); // Remove from local posts array
          this.filteredPosts = this.filteredPosts.filter(post => post.id !== this.selectedPost.id); // Update filtered posts
          this.showDetailModal = false;
          alert("Item deleted successfully!");
        })
        .catch(error => {
          console.error("Error deleting item:", error);
        });
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
  background-color: #f4f4f4;
  font-family: 'Roboto', sans-serif;
}

.dashboard-header {
  margin-bottom: 30px;
  text-align: center;
}

.dashboard-header h1 {
  font-size: 36px;
  color: #333;
}

.search-bar input {
  width: 100%;
  max-width: 400px;
  padding: 12px 16px;
  border: 1px solid #ddd;
  border-radius: 50px;
  font-size: 16px;
  outline: none;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.search-bar input:focus {
  border-color: #008080;
  box-shadow: 0 4px 6px rgba(0, 128, 128, 0.2);
}

.featured-posts {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
  padding: 20px;
}

.no-posts {
  text-align: center;
  color: #555;
  font-size: 1.2rem;
}

.card {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  cursor: pointer;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.card:hover {
  transform: translateY(-10px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.card-content {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.card-image {
  width: 100%;
  height: 300px;
  object-fit: cover;
  border-bottom: 2px solid #f0f0f0;
}

.card-text {
  padding: 15px;
  flex-grow: 1;
}

.post-date {
  font-size: 0.9rem;
  color: #777;
  margin-bottom: 10px;
}

.post-info {
  font-size: 1rem;
  color: #333;
}

.post-info p {
  margin: 5px 0;
}

.post-info strong {
  color: #007b7f;
}

.card-text p {
  line-height: 1.6;
}

@media (max-width: 768px) {
  .featured-posts {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 480px) {
  .featured-posts {
    grid-template-columns: 1fr;
  }
}

/* Modal styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 999;
}

/* Combined Modal Info Styling */
.modal-info {
  display: flex;
  flex-direction: column;
  gap: 15px;
  color: #333;
}

.modal-info p {
  font-size: 16px;
  margin: 10px 0;
  line-height: 1.5;
}

.modal-info strong {
  color: #007b7f;
}

/* Highlight Status */
.modal-info p strong {
  color: #008080;
  font-weight: 600;
}

/* Facebook Link Styling */
.modal-info .modal-link {
  color: #007bff;
  text-decoration: none;
}

.modal-info .modal-link:hover {
  text-decoration: underline;
}

/* Button Container */
.modal-actions {
  display: flex;
  justify-content: space-between;
  gap: 15px;
  margin-top: 20px;
}

/* Close and Edit Buttons Styling */
.modal-actions button {
  padding: 12px 20px;
  font-size: 16px;
  font-weight: 600;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.modal-actions .btn.secondary {
  background-color: #f0f0f0;
  color: #333;
  border: 1px solid #ddd;
}

.modal-actions .btn.secondary:hover {
  background-color: #ddd;
}

.modal-actions .btn.primary {
  background-color: #4e9fd1;
  color: white;
}

.modal-actions .btn.primary:hover {
  background-color: #347ea3;
  transform: translateY(-2px);
}

/* Modal Content */
.modal-content {
  background-color: #fff;
  border-radius: 8px;
  padding: 25px;
  width: 500px;
  max-width: 100%;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  max-height: 90vh;
  overflow-y: auto;
}

/* Scrollbar Styling */
.modal-content::-webkit-scrollbar {
  width: 8px;
}

.modal-content::-webkit-scrollbar-thumb {
  background-color: #888;
  border-radius: 10px;
}

.modal-content::-webkit-scrollbar-thumb:hover {
  background-color: #555;
}

/* Floating Button */
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
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.3s ease, transform 0.3s ease;
}

.floating-btn:hover {
  background: #006666;
  transform: scale(1.1);
}

.plus-icon {
  font-size: 30px;
}

/* Optional - Responsive Adjustments */
@media (max-width: 768px) {
  .modal-info p {
    font-size: 14px;
  }

  .modal-actions {
    flex-direction: column;
    align-items: center;
  }

  .modal-actions button {
    width: 100%;
    margin-bottom: 10px;
  }
}

/* Edit Form Modal Styles */
.modal-form {
  display: flex;
  flex-direction: column;
  gap: 15px;
  width: 100%;
  max-width: 500px;
}

.modal-form .form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.modal-form label {
  font-size: 14px;
  font-weight: 600;
  color: #333;
}

.modal-form .input-field,
.modal-form select {
  padding: 12px;
  font-size: 16px;
  border: 1px solid #ddd;
  border-radius: 8px;
  outline: none;
  transition: border-color 0.3s ease;
}

.modal-form .input-field:focus,
.modal-form select:focus {
  border-color: #008080;
  box-shadow: 0 0 5px rgba(0, 128, 128, 0.2);
}

.modal-form .input-file {
  padding: 10px;
  font-size: 16px;
}

.modal-form .image-preview {
  margin-top: 15px;
  max-width: 100%;
  height: auto;
  border-radius: 8px;
  object-fit: cover;
}

.modal-actions {
  display: flex;
  justify-content: space-between;
  gap: 15px;
  margin-top: 20px;
}

/* Form Action Buttons */
.modal-actions button {
  padding: 12px 20px;
  font-size: 16px;
  font-weight: 600;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
}

/* Cancel Button Style */
.modal-actions .btn.secondary {
  background-color: #f0f0f0;
  color: #333;
  border: 1px solid #ddd;
}

.modal-actions .btn.secondary:hover {
  background-color: #ddd;
}

/* Save Button Style */
.modal-actions .btn.primary {
  background-color: #4e9fd1;
  color: white;
}

.modal-actions .btn.primary:hover {
  background-color: #347ea3;
  transform: translateY(-2px);
}

/* Delete Button Style */
.modal-actions .btn.danger {
  background-color: #e74c3c;
  color: white;
  border: none;
  padding: 12px 20px;
}

.modal-actions .btn.danger:hover {
  background-color: #c0392b;
  transform: translateY(-2px);
}

/* Optional - Responsive Design */
@media (max-width: 768px) {
  .modal-form .form-group {
    gap: 10px;
  }

  .modal-actions {
    flex-direction: column;
    gap: 10px;
  }

  .modal-actions button {
    width: 100%;
  }
}
</style>
