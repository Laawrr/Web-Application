<template>
  <div class="dashboard">
    <!-- Map container with search bar -->
    <v-card class="map-container">
      <v-card-title class="text-h6 font-weight-bold">LostNoMore Map</v-card-title>

      <!-- Search Location Bar -->
      <v-text-field
        v-model="searchQuery"
        label="Search for a location"
        outlined
        dense
        class="input-field"
        @input="searchLocation"
        :loading="loadingSearch"
      ></v-text-field>

      <!-- Map element -->
      <div id="map" class="custom-map"></div>
    </v-card>

    <!-- Modal for adding marker details -->
    <v-dialog v-model="showModal" max-width="600px" @click:outside="closeModal">
      <v-card class="modal-card">
        <v-card-title class="text-h5 font-weight-bold">Add New Marker</v-card-title>
        <v-card-text>
          <v-form ref="form" class="d-flex flex-column gap-4">
            <v-text-field
              label="Enter Title"
              v-model="markerTitle"
              outlined
              dense
              required
              class="input-field"
            ></v-text-field>

            <v-text-field
              label="Enter Founder's name"
              v-model="markerFounder"
              outlined
              dense
              class="input-field"
            ></v-text-field>

            <v-text-field
              label="Enter URL"
              v-model="markerUrl"
              outlined
              dense
              required
              class="input-field"
            ></v-text-field>

            <v-file-input
              label="Upload Image"
              v-model="markerImage"
              accept="image/*"
              outlined
              dense
              class="input-field"
            ></v-file-input>

            <v-textarea
              label="Enter phone number"
              v-model="markerAdditionalInfo"
              outlined
              dense
              rows="1"
              class="input-field"
            ></v-textarea>
          </v-form>
        </v-card-text>

        <v-card-actions class="actions">
          <v-btn color="grey" text @click="closeModal">Cancel</v-btn>
          <v-btn color="primary" class="white--text" @click="addMarker">Save</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Snackbar for showing error messages -->
    <v-snackbar v-model="snackbar.visible" :color="snackbar.color" multi-line timeout="4000">
      {{ snackbar.message }}
    </v-snackbar>
  </div>
</template>

<script>
import L from "leaflet";
import "leaflet/dist/leaflet.css";

export default {
  name: "Map",
  data() {
    return {
      map: null,
      searchQuery: "",
      showModal: false,
      loadingSearch: false,
      snackbar: {
        visible: false,
        message: "",
        color: "error",
      },
      markerTitle: "",
      markerFounder: "",
      markerUrl: "",
      markerImage: null,
      markerAdditionalInfo: "",
      selectedLatLng: null,
      markers: [],
      searchMarker: null,
    };
  },
  mounted() {
    this.map = L.map("map", { zoomControl: true, zoomAnimation: true });

    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          const { latitude, longitude } = position.coords;
          this.map.setView([latitude, longitude], 15);

          L.marker([latitude, longitude])
            .addTo(this.map)
            .bindPopup("You are here!")
            .openPopup();
        },
        (error) => {
          this.showSnackbar("Error getting your location: " + error.message, "error");
        }
      );
    }

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png").addTo(this.map);

    this.map.on("click", (e) => {
      this.selectedLatLng = e.latlng;
      this.openAddMarkerModal();
    });

    this.map.on("zoomend", this.adjustMarkerSize);
    this.loadMarkers();
  },
  methods: {
    showSnackbar(message, color) {
      this.snackbar.message = message;
      this.snackbar.color = color;
      this.snackbar.visible = true;
    },

    searchLocation() {
      if (!this.searchQuery) return this.showSnackbar("Please enter a valid location.", "error");

      this.loadingSearch = true;
      const apiUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.searchQuery)}&limit=1`;

      fetch(apiUrl)
        .then((response) => response.json())
        .then((data) => {
          this.loadingSearch = false;
          if (data.length) {
            const { lat, lon } = data[0];
            this.map.setView([lat, lon], 15);
            if (this.searchMarker) this.map.removeLayer(this.searchMarker);
            this.searchMarker = L.marker([lat, lon]).addTo(this.map).bindPopup("Location found!").openPopup();
          } else {
            this.showSnackbar("Location not found.", "error");
          }
        })
        .catch((error) => {
          this.loadingSearch = false;
          this.showSnackbar("Error fetching location: " + error.message, "error");
        });
    },

    createMarkerIcon() {
      const iconSize = this.getIconSizeForZoom();
      return L.icon({
        iconUrl: this.markerImage ? URL.createObjectURL(this.markerImage) : "default_marker.png",
        iconSize,
        iconAnchor: [iconSize[0] / 2, iconSize[1]],
        popupAnchor: [0, -iconSize[1]],
      });
    },

    getIconSizeForZoom() {
      const zoom = this.map.getZoom();
      return [40 * (zoom > 10 ? 1.5 : 1), 40 * (zoom > 10 ? 1.5 : 1)];
    },

    adjustMarkerSize() {
      this.map.eachLayer((layer) => {
        if (layer instanceof L.Marker) layer.setIcon(this.createMarkerIcon());
      });
    },

    closeModal() {
      this.showModal = false;
      this.resetModalData();
    },

    resetModalData() {
      this.markerTitle = "";
      this.markerFounder = "";
      this.markerUrl = "";
      this.markerImage = null;
      this.markerAdditionalInfo = "";
    },

    loadMarkers() {
  const savedMarkers = localStorage.getItem("markers");
  if (savedMarkers) {
    this.markers = JSON.parse(savedMarkers);
    this.markers.forEach((marker) => {
      const markerIcon = L.icon({
        iconUrl: marker.image || "default_marker.png", // Set marker icon
        iconSize: this.getIconSizeForZoom(),
        iconAnchor: [20, 40],
        popupAnchor: [0, -40],
      });

      L.marker([marker.lat, marker.lng], { icon: markerIcon })
        .addTo(this.map)
        .bindPopup(`
          <div class="marker-popup">
            <div class="popup-header">
              <strong class="popup-title">${marker.title}</strong>
              <p class="popup-subtitle">Founded by: ${marker.founder}</p>
            </div>
            <div class="popup-body">
              <!-- Conditionally show the image if it exists -->
              ${marker.image ? `<img src="${marker.image}" alt="Marker Image" class="popup-image" />` : ''}
              <p class="popup-info">${marker.additionalInfo || "No additional info"}</p>
              <a href="${marker.url}" target="_blank" class="popup-link">More Info</a>
            </div>
          </div>
        `, { maxWidth: 400 });
    });
  }
}
,

    openAddMarkerModal() {
      this.showModal = true;
    },

    addMarker() {
      if (!this.$refs.form.validate()) return this.showSnackbar("Fill out required fields.", "error");
      const markerData = {
        lat: this.selectedLatLng.lat,
        lng: this.selectedLatLng.lng,
        title: this.markerTitle,
        founder: this.markerFounder,
        url: this.markerUrl,
        image: this.markerImage ? URL.createObjectURL(this.markerImage) : null,
        additionalInfo: this.markerAdditionalInfo,
      };

      L.marker([markerData.lat, markerData.lng], { icon: this.createMarkerIcon() }).addTo(this.map);
      this.markers.push(markerData);
      localStorage.setItem("markers", JSON.stringify(this.markers));
      this.closeModal();
    },
  },
};
</script>
  
  <style>
  /* Style for the map container */
  .map-container {
    border-radius: 8px;
    padding: 16px;
    background-color: #f5f5f5;
  }
  
  .custom-map {
    height: 500px;
  }
  .modal-card {
      border-radius: 12px;
      padding: 16px;
      background-color: #fff;
      box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.1);
  }
  
  /* Title Styling */
  .v-card-title {
      font-weight: bold;
      font-size: 1.25rem;
      color: #333;
      margin-bottom: 20px;
  }
  
  /* Form Fields */
  .input-field {
      margin-bottom: 16px;
  }
  
  /* Buttons Styling */
  .actions {
      display: flex;
      justify-content: flex-end;
      padding-top: 12px;
  }
  
  .v-btn {
      text-transform: none;
  }
  
  .v-btn.primary {
      background-color: #6200ea; /* Purple for the 'Save' button */
      color: #fff;
  }
  
  .v-btn.grey {
      background-color: #f1f1f1; /* Light grey for the 'Cancel' button */
      color: #333;
  }
  
  /* Snackbar */
  .v-snackbar {
      max-width: 400px;
      background-color: #6200ea;
      color: white;
  }
  </style>
  