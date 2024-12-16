<template>
  <div>
    <!-- Header bar -->
    <header-bar />

    <!-- Main dashboard content -->
    <main class="dashboard">
      <section class="dashboard-header">
        <h1>Lost and Found</h1>
        <div class="search-bar">
          <input type="text" v-model="searchQuery" placeholder="Search for lost items..." @input="filterPosts" />
        </div>
      </section>

      <!-- Maps -->
      <Map />

      <!-- Featured posts -->
      <section class="featured-posts">
        <br />
        <div v-for="post in filteredPosts" :key="post.id" class="card">
          <img v-if="post.image" :src="post.image" alt="Item Image" class="card-image clickable" @click="enlargeImage(post.image)" />
          <p class="post-date">{{ post.dateFound }}</p>
          <p class="post-info"><strong>Owner:</strong> {{ post.owner }}</p>
          <p class="post-info"><strong>Facebook Link:</strong> {{ post.facebookLink }}</p>
          <p class="post-info"><strong>Contact Number:</strong> {{ post.contactNumber }}</p>
          <button @click="deletePost(post.id)" class="delete-btn">
            <span class="delete-icon">&#10005;</span>
          </button>
        </div>
      </section>

      <!-- Modal for viewing post details -->
      <ItemModal v-if="viewingPost" :isVisible="viewingPost !== null" :post="viewingPost" @close="closePost" />

      <!-- Modal for Upload Form -->
      <div v-if="showUploadForm" class="modal-overlay" @click="closeUploadForm">
        <div class="modal-content" @click.stop>
          <form @submit.prevent="uploadItem" class="upload-form">
            <div class="form-group">
              <label for="itemName">Item Name</label>
              <input type="text" id="itemName" v-model="newItem.name" placeholder="Enter item name" required />
            </div>
            <div class="form-group">
              <label for="dateFound">Date of loss</label>
              <input type="date" id="dateFound" v-model="newItem.dateFound" required />
            </div>
            <div class="form-group">
              <label for="owner">Owner</label>
              <input type="text" id="owner" v-model="newItem.owner" placeholder="Enter owner's name" required />
            </div>
            <div class="form-group">
              <label for="facebookLink">Facebook Link</label>
              <input type="text" id="facebookLink" v-model="newItem.facebookLink" placeholder="Enter Facebook link" required />
            </div>
            <div class="form-group">
              <label for="contactNumber">Contact Number</label>
              <input type="tel" id="contactNumber" v-model="newItem.contactNumber" placeholder="Enter contact number" required />
            </div>
            <div class="form-group">
              <label for="itemImage">Image</label>
              <input type="file" id="itemImage" @change="handleImageUpload" accept="image/*" required />
              <img v-if="newItem.image" :src="newItem.image" alt="Item Image" class="uploaded-image" />
            </div>
            <button type="submit" class="primary">Submit</button>
          </form>
        </div>
      </div>
    </main>

    <!-- Floating button -->
    <button class="floating-upload-btn" @click="showUploadForm = !showUploadForm">
      <span class="icon">+</span>
    </button>

    <!-- Footer bar -->
    <footer-bar />

    <!-- Activity Log -->
    <section class="activity-log">
      <h3>Activity Log</h3>
      <ul>
        <li v-for="(log, index) in activityLogs" :key="index">{{ log }}</li>
      </ul>
    </section>
  </div>
</template>

<script>
import axios from 'axios'; // Import axios for HTTP requests
import HeaderBar from './HeaderBar.vue';
import FooterBar from './FooterBar.vue';
import ItemModal from './ItemModal.vue';
import Map from "./map.vue";

export default {
  name: 'DashboardPage',
  components: {
    HeaderBar,
    FooterBar,
    Map,
    ItemModal
  },
  data() {
    return {
      posts: [], // Array to store all posts from the database
      searchQuery: '', // Used for searching/filtering posts
      filteredPosts: [], // Filtered array to show posts based on search
      showUploadForm: false,
      viewingPost: null,
      enlargedImage: null, // For image previews
      newItem: {
        name: '',
        dateFound: '',
        owner: '',
        facebookLink: '',
        contactNumber: '',
        image: null
      },
      lostItems: [], // Array to hold fetched items (integrating the provided code)
      activityLogs: [] // Array to store activities
    };
  },
  methods: {
    // Enlarge image preview
    enlargeImage(imageSrc) {
      this.enlargedImage = imageSrc;
    },

    // Close the enlarged image view
    closeImage() {
      this.enlargedImage = null;
    },

    // View a post
    viewPost(id) {
      this.viewingPost = this.posts.find(post => post.id === id);
    },

    // Close the post view
    closePost() {
      this.viewingPost = null;
    },

    // Filter posts based on search query
    filterPosts() {
      this.filteredPosts = this.posts.filter(post =>
        post.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
        post.owner.toLowerCase().includes(this.searchQuery.toLowerCase())
      );
    },

    // Handle image file upload (for local display)
    handleImageUpload(event) {
      const file = event.target.files[0];
      if (file) {
        this.newItem.image = file; // Store the selected image file for upload
      }
    },

    // Upload a new item (post the item to the server)
    uploadItem() {
      const formData = new FormData();
      formData.append('name', this.newItem.name);
      formData.append('lost_date', this.newItem.dateFound);
      formData.append('owner', this.newItem.owner);
      formData.append('facebook_link', this.newItem.facebookLink);
      formData.append('contact_number', this.newItem.contactNumber);

      if (this.newItem.image) {
        formData.append('itemImage', this.newItem.image);
      }

      axios.post('/lost-items', formData)
        .then(response => {
          alert('Lost item uploaded successfully!');

          const newPost = {
            id: response.data.id,
            name: this.newItem.name,
            dateFound: this.newItem.dateFound,
            owner: this.newItem.owner,
            facebookLink: this.newItem.facebookLink,
            contactNumber: this.newItem.contactNumber,
            image: URL.createObjectURL(this.newItem.image)
          };

          // Add new item to posts
          this.posts.push(newPost);

          // Update filteredPosts based on search query
          this.filterPosts();

          // Add to activity log
          this.activityLogs.push(`Uploaded item: ${this.newItem.name}`);

          // Reset new item form
          this.newItem = {
            name: '',
            dateFound: '',
            owner: '',
            facebookLink: '',
            contactNumber: '',
            image: null
          };
        })
        .catch(error => {
          console.error('Error uploading the lost item:', error.response.data);
          alert('There was an error uploading the lost item. Check the console for details.');
        });
    },

    // Close the upload form
    closeUploadForm() {
      this.showUploadForm = false;
    },

    // Handle clicks outside the modal to close it
    handleOutsideClick(event) {
      const modalContent = this.$refs.modalContent;
      if (modalContent && !modalContent.contains(event.target)) {
        this.closeUploadForm();
      }
    },

    // Delete a post
    deletePost(postId) {
      axios.delete(`/lost-items/${postId}`) // Make sure you have an API endpoint for this
        .then(() => {
          this.posts = this.posts.filter(post => post.id !== postId);
          if (this.searchQuery) {
            this.filterPosts();
          } else {
            this.filteredPosts = [...this.posts];
          }

          // Add to activity log
          this.activityLogs.push(`Deleted item with ID: ${postId}`);
        })
        .catch(error => {
          console.error('Error deleting the lost item:', error.response.data);
          alert('There was an error deleting the lost item. Check the console for details.');
        });
    },

    // Fetch all lost items from the backend (integrating the provided script)
    fetchLostItems() {
      axios.get('/lost-items')
        .then((response) => {
          this.posts = response.data;
          this.filteredPosts = response.data;
        })
        .catch(error => {
          console.error('Error fetching lost items:', error);
        });
    },
  },
  mounted() {
    this.fetchLostItems();
  }
};
</script>





<style scoped>
/* Scoped styles for the Dashboard component */
/* Activity Log Section */
.activity-log {
  margin-top: 40px;
  padding: 20px;
  background-color: #f9f9f9;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.activity-log h3 {
  font-size: 1.5rem;
  margin-bottom: 20px;
}

.activity-log ul {
  list-style-type: none;
  padding: 0;
}

.activity-log li {
  padding: 10px;
  border-bottom: 1px solid #ddd;
}

.activity-log li:last-child {
  border-bottom: none;
}


/* Dashboard header */
/* Dashboard container */
.dashboard {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background-color: #030202;
  /* Light gray background */
}

/* Dashboard header */
.dashboard-header {
  display: flex;
  flex-direction: column;
  justify-content: center;
  /* Center the content vertically */
  align-items: center;
  /* Align content horizontally */
  background-image: url('C:\Users\angel\OneDrive\Desktop\With map\Web-Application\web-app\public\img\wall.png');
  background-size: cover;
  background-position: center center;
  padding: 80px 20px;
  /* Reduced padding for better alignment */
  position: relative;
  font-family: 'Roboto', sans-serif;
  color: white;
  text-align: center;
}

.dashboard-header::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  //background: rgba(13, 169, 161, 0.895);
  z-index: 1;
}

.dashboard-header h1 {
  font-size: 2.5rem;
  font-weight: 700;
  color: #fff;
  text-transform: uppercase;
  letter-spacing: 2px;
  background: linear-gradient(90deg, #ff7f50, #008080);
  -webkit-background-clip: text;
  background-clip: text;
  text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
  position: relative;
  z-index: 2;
  margin: 0 0 20px 0;
  /* Space between the header and the search bar */
}

.search-bar {
  position: relative;
  z-index: 2;
  display: inline-block;
  width: 1000%;
  max-width: 200px;
}

.search-bar input {
  width: 100%;
  padding: 10px;
  font-size: 1rem;
  border-radius: 5px;
  border: 2px solid #008080;
  box-sizing: border-box;
  outline: none;
  transition: border-color 0.3s ease;
  color: black;
}

.search-bar input:focus {
  border-color: #005f5f;
  box-shadow: 0 0 10px rgba(0, 128, 128, 0.4);
}

.search-bar input::placeholder {
  color: #777;
}

/* Responsive Design */
@media (max-width: 768px) {
  .dashboard-header {
    padding: 60px 20px;
    /* Less padding on smaller screens */
  }

  .dashboard-header h1 {
    font-size: 2rem;
    /* Smaller font size for mobile */
  }
}

@media (max-width: 480px) {
  .dashboard-header {
    padding: 50px 10px;
    /* Even less padding for very small screens */
  }

  .dashboard-header h1 {
    font-size: 1.5rem;
    /* Even smaller font size for very small screens */
  }
}

/* Featured posts section */
.featured-posts {
  background-image: url('C:\Users\angel\OneDrive\Desktop\With map\Web-Application\web-app\public\img\wall.png');
  padding: 2rem;
  text-align: center;
  display: flex;
  flex-wrap: wrap;
  /* Allow cards to wrap onto the next line */
  justify-content: center;
  /* Center the cards horizontally */
}

.card {
  background-color: #FFF;
  /* White for cards */
  padding: 1rem;
  border: 3px solid #ccc;
  /* Light border for box effect */
  box-shadow: none;
  /* No shadow for box-style look */
  margin: 1rem;
  /* Space between cards */
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  height: 350px;
  width: 290px;
  /* Fixed width for box-style */
  text-align: left;
  position: relative;

}

.card-image {
  width: 100%;
  /* Set the image width to 100% of its container */
  height: 150px;
  /* Set a fixed height for the image */
  object-fit: cover;
  /* Ensures image covers the box without distortion */
  border-radius: 5px;
  /* Optional: rounded corners for the image */
  margin-bottom: 1rem;
  /* Space between image and the rest of the content */
}

.card:hover {
  transform: translateY(-5px);
  /* Lift the card slightly */
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
  /* Add a shadow to elevate the card */
  background-color: #008080;
  transition: all 0.3s ease;
}

/* Image hover effect */
.card-image.clickable:hover {
  transform: scale(1.1);
  /* Slight zoom on image hover */
}

/* Optional: Adjust the card's width and margin based on the screen size */
@media (max-width: 768px) {
  .card {
    width: 200px;
    /* Adjust card width for smaller screens */
  }
}

@media (max-width: 480px) {
  .card {
    width: 100%;
    /* Full width for very small screens */
    margin: 0.5rem 0;
    /* Adjust margin for better spacing */
  }
}

/* Button styling */
/* Button styling */
button.primary {
  background-color: #008080;
  /* Teal for primary button */
  color: white;
  padding: 0.5rem 1rem;
  /* Smaller padding */
  font-size: 0.9rem;
  /* Slightly smaller font size */
  border: none;
  border-radius: 4px;
  /* Slightly rounded corners */
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.2s ease;
  /* Add smooth transitions */
}

button.primary:hover {
  background-color: #005f5f;
  /* Darker teal on hover */
  transform: scale(1.05);
  /* Slightly enlarge button on hover */
}

button.primary:active {
  background-color: #004949;
  /* Even darker teal on click */
  transform: scale(0.95);
  /* Shrink slightly on click */
}

/* Delete button */
.delete-btn {
  position: absolute;
  top: 8px;
  right: 8px;
  background-color: transparent;
  border: none;
  font-size: 20px;
  /* Adjust icon size */
  color: #ff4d4d;
  /* Soft red for delete icon */
  cursor: pointer;
  padding: 0.3rem;
  /* Smaller padding */
  border-radius: 50%;
  /* Circular button */
  transition: background-color 0.3s ease, transform 0.2s ease;
  /* Smooth transitions */
}

.delete-btn:hover {
  background-color: rgba(255, 77, 77, 0.2);
  /* Light red background on hover */
  transform: scale(1.1);
  /* Slightly enlarge on hover */
}

.delete-btn:active {
  transform: scale(0.9);
  /* Shrink slightly on click */
}


/* Upload section */
.upload-section {
  padding: 2rem;
  text-align: center;
}

/* Modal Overlay */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  /* Semi-transparent background */
  display: flex;
  justify-content: center;
  align-items: center;
  animation: fadeIn 0.3s ease-out;
}

/* Modal Content */
.modal-content {
  background-color: white;
  padding: 2rem;
  border-radius: 5px;
  width: 400px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
  animation: slideIn 0.5s ease-out;
}

/* Upload form */
.upload-form {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.modal-content {
  background-color: white;
  padding: 2rem;
  border-radius: 5px;
  width: 400px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
  max-height: 80vh;
  /* Limit height of the modal */
  overflow-y: auto;
  /* Enable vertical scrolling if content exceeds max height */
}

.upload-form .form-group {
  margin-bottom: 1rem;
  width: 100%;
}

.upload-form input {
  padding: 0.75rem;
  width: 100%;
  max-width: 300px;
  border-radius: 5px;
  border: 1px solid #ccc;
}

.upload-form label {
  margin-bottom: 0.5rem;
  font-weight: bold;
}

/* Style for the delete button */
.delete-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  background-color: transparent;
  border: none;
  font-size: 24px;
  color: #ff0000;
  /* Red color for the delete icon */
  cursor: pointer;
}

.delete-btn:hover {
  color: #d80000;
  /* Darker red on hover */
  transform: scale(1.1);
  /* Slightly enlarge on hover */

}

.delete-btn:active {
  transform: scale(0.9);
  /* Shrink slightly on click */
}


.delete-icon {
  font-size: 24px;
}

/* Card Text Styling */
.post-date,
.post-info {
  text-align: center;
  /* Center the date */
  font-size: 0.85rem;
  color: #333;
  margin-bottom: 0.5rem;
}

/* Clickable image styling */
.card-image.clickable {
  cursor: pointer;
  transition: transform 0.3s ease;
  /* Smooth scaling effect */
}

.card-image.clickable:hover {
  transform: scale(1.05);
  /* Slight zoom effect on hover */
}

/* Modal overlay for enlarged image */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.8);
  /* Dark semi-transparent background */
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

/* Enlarged image styling */
.modal-image {
  max-width: 90%;
  max-height: 90%;
  border-radius: 8px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
}

.floating-upload-btn {
  position: fixed;
  bottom: 20px;
  right: 20px;
  background-color: #008080;
  /* Teal background */
  color: white;
  border: none;
  border-radius: 50%;
  width: 60px;
  height: 60px;
  font-size: 30px;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
  transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.2s ease;
}

.floating-upload-btn:hover {
  background-color: #005f5f;
  /* Darker teal on hover */
  transform: scale(1.1);
  /* Slightly enlarge on hover */
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
  /* Add shadow on hover */
}

.floating-upload-btn:active {
  background-color: #004949;
  /* Even darker teal on click */
  transform: scale(0.95);
  /* Shrink slightly on click */
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
  /* Smaller shadow on active click */
}

/* Icon styling */
.floating-upload-btn .icon {
  font-weight: bold;
  font-size: 30px;
}

/* Ensure that the modal content doesn't get cut off */
.modal-content {
  max-height: 80vh;
  /* Limit height of the modal */
  overflow-y: auto;
  /* Enable vertical scrolling if content exceeds max height */
}

/* Other styles */
.modal-overlay {
  /* Same styles as before for modals */
}

.upload-form {
  display: flex;
  flex-direction: column;
  align-items: center;
}
</style>