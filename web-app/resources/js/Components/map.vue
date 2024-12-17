<template>
  <div class="map-container">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <v-card-title class="text-h6 font-weight-bold mt-5">LostNoMore Map</v-card-title>
    <v-text-field
      v-model="searchQuery"
      label="Search for a location"
      outlined
      dense
      class="input-field"
      @input="searchLocation"
      :loading="loadingSearch"
    ></v-text-field>
    <div class="map-wrapper" :class="{ 'map-disabled': disabled }">
      <div id="map" ref="mapRef" class="custom-map"></div>
      <div v-if="isLoading" class="loading-overlay">
        <div class="loading-content">
          <v-progress-circular
            indeterminate
            color="primary"
            size="64"
          ></v-progress-circular>
          <span class="loading-text">Loading map...</span>
        </div>
      </div>
    </div>

    <!-- Snackbar for showing error messages -->
    <v-snackbar v-model="snackbar.visible" :color="snackbar.color" multi-line timeout="4000">
      {{ snackbar.message }}
    </v-snackbar>
  </div>
</template>

<script>
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import axios from 'axios';

export default {
  name: "Map",
  props: {
    disabled: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      map: null,
      searchQuery: "",
      currentMarker: null,
      selectedLatLng: null,
      isLoading: true,
      loadingSearch: false,
      snackbar: {
        visible: false,
        message: "",
        color: "error",
      },
      userLocationMarker: null,
      watchId: null,
      markerLayer: null
    };
  },
  mounted() {
    this.$nextTick(() => {
      this.initializeMap();
    });
  },
  methods: {
    initializeMap() {
      if (this.map) {
        this.map.remove();
      }

      const mapContainer = this.$refs.mapRef;
      if (!mapContainer) {
        this.isLoading = false;
        return;
      }

      this.isLoading = true;

      try {
        this.map = L.map(mapContainer, {
          zoomControl: true,
          zoomAnimation: true,
          dragging: true,
          touchZoom: true,
          doubleClickZoom: true,
          scrollWheelZoom: true,
          boxZoom: true,
          keyboard: true,
        });

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
          attribution: "OpenStreetMap contributors",
        }).addTo(this.map);

        // Create a layer group for markers
        this.markerLayer = L.layerGroup().addTo(this.map);

        // Set a default view immediately
        this.map.setView([14.5995, 120.9842], 13); // Manila coordinates

        // Add click handler for marking locations
        this.map.on("click", (e) => {
          if (this.disabled) return;

          const latlng = e.latlng;

          // Clear existing markers
          this.markerLayer.clearLayers();

          const pinIcon = L.divIcon({
            html: '<i class="fas fa-map-marker-alt"></i>',
            className: "custom-pin-marker",
            iconSize: [30, 30],
            iconAnchor: [15, 30],
            popupAnchor: [0, -30]
          });

          // Add new marker to the layer group
          this.currentMarker = L.marker(latlng, {
            icon: pinIcon,
            draggable: false,
          }).addTo(this.markerLayer);

          this.selectedLatLng = latlng;
          
          // Emit the selected location to the parent component
          this.$emit("location-selected", {
            lat: latlng.lat,
            lng: latlng.lng
          });
          
          // Show coordinates in a popup
          this.currentMarker.bindPopup(
            `<b>Selected Location</b><br>
             Lat: ${latlng.lat.toFixed(6)}<br>
             Lng: ${latlng.lng.toFixed(6)}`
          ).openPopup();
        });

        // Get current location
        if ("geolocation" in navigator) {
          navigator.geolocation.getCurrentPosition(
            (position) => {
              const { latitude, longitude } = position.coords;
              
              // Set map view to user location
              this.map.setView([latitude, longitude], 15);

              // Add user location marker
              const userIcon = L.divIcon({
                html: '<div class="user-location-dot"></div>',
                className: "user-location-marker",
                iconSize: [12, 12],
              });

              if (this.userLocationMarker) {
                this.markerLayer.removeLayer(this.userLocationMarker);
              }

              this.userLocationMarker = L.marker([latitude, longitude], {
                icon: userIcon,
                zIndexOffset: 1000,
              })
                .addTo(this.markerLayer)
                .bindPopup("Your location")
                .openPopup();

              // Watch for location updates
              this.watchId = navigator.geolocation.watchPosition(
                (newPosition) => {
                  const newLat = newPosition.coords.latitude;
                  const newLng = newPosition.coords.longitude;
                  if (this.userLocationMarker) {
                    this.userLocationMarker.setLatLng([newLat, newLng]);
                  }
                },
                null,
                { enableHighAccuracy: true }
              );
            },
            (error) => {
              console.error("Geolocation error:", error);
              this.showSnackbar("Unable to get your location. Please enable location services.", "error");
            }
          );
        }

        this.map.whenReady(() => {
          this.isLoading = false;
        });

        setTimeout(() => {
          this.map.invalidateSize();
        }, 250);

      } catch (error) {
        console.error('Error initializing map:', error);
        this.showSnackbar('Error initializing map. Please refresh the page.', 'error');
        this.isLoading = false;
      }
    },
    getCurrentLocation() {
      if (!navigator.geolocation) {
        this.showSnackbar("Geolocation is not supported by your browser", "error");
        return;
      }

      const options = {
        enableHighAccuracy: true,
        timeout: 5000, // Reduced timeout
        maximumAge: 0
      };

      navigator.geolocation.getCurrentPosition(
        (position) => {
          const { latitude, longitude } = position.coords;
          
          if (this.map) {
            // Set map view to user location
            this.map.setView([latitude, longitude], 15);

            // Create user location marker
            if (this.userLocationMarker) {
              this.markerLayer.removeLayer(this.userLocationMarker);
            }

            const userIcon = L.divIcon({
              html: '<div class="user-location-dot"></div>',
              className: "user-location-marker",
              iconSize: [12, 12],
            });

            this.userLocationMarker = L.marker([latitude, longitude], {
              icon: userIcon,
              zIndexOffset: 1000,
            })
              .addTo(this.markerLayer)
              .bindPopup("Your location")
              .openPopup();

            // Start watching position for updates
            if (this.watchId) {
              navigator.geolocation.clearWatch(this.watchId);
            }

            this.watchId = navigator.geolocation.watchPosition(
              (newPosition) => {
                const newLat = newPosition.coords.latitude;
                const newLng = newPosition.coords.longitude;
                if (this.userLocationMarker) {
                  this.userLocationMarker.setLatLng([newLat, newLng]);
                }
              },
              null,
              options
            );
          }
        },
        (error) => {
          let errorMessage = "Error getting your location. ";
          switch(error.code) {
            case error.PERMISSION_DENIED:
              errorMessage += "Please enable location services.";
              break;
            case error.POSITION_UNAVAILABLE:
              errorMessage += "Location information unavailable.";
              break;
            case error.TIMEOUT:
              errorMessage += "Request timed out.";
              break;
            default:
              errorMessage += "An unknown error occurred.";
          }
          console.error('Geolocation error:', error);
          this.showSnackbar(errorMessage, "error");
        },
        options
      );
    },
    showSnackbar(message, color) {
      this.snackbar.message = message;
      this.snackbar.color = color;
      this.snackbar.visible = true;
    },
    searchLocation() {
      if (!this.searchQuery) return this.showSnackbar("Please enter a valid location.", "error");

      this.loadingSearch = true;
      const apiUrl = `https://cors-anywhere.herokuapp.com/https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(
        this.searchQuery
      )}&limit=1`;

      fetch(apiUrl)
        .then((response) => response.json())
        .then((data) => {
          if (data && data.length > 0) {
            const { lat, lon } = data[0];
            this.map.setView([lat, lon], 15);
          } else {
            this.showSnackbar("Location not found.", "error");
          }
        })
        .catch((error) => {
          console.error("Error searching location:", error);
          this.showSnackbar("Error searching location. Please try again.", "error");
        })
        .finally(() => {
          this.loadingSearch = false;
        });
    },
  },
  beforeDestroy() {
    if (this.watchId !== null) {
      navigator.geolocation.clearWatch(this.watchId);
    }
    if (this.map) {
      if (this.currentMarker) {
        this.markerLayer.removeLayer(this.currentMarker);
      }
      if (this.userLocationMarker) {
        this.markerLayer.removeLayer(this.userLocationMarker);
      }
      this.map.remove();
    }
  },
};
</script>

<style>
.map-container {
  position: relative;
  width: 100%;
  height: 100%;
  min-height: 500px;
  padding: 16px;
}

.map-wrapper {
  position: relative;
  width: 100%;
  height: 100%;
  min-height: 400px;
  border-radius: 8px;
  overflow: hidden;
  transition: filter 0.3s ease;
}

.map-disabled {
  filter: blur(3px);
  pointer-events: none;
  user-select: none;
}

.custom-map {
  width: 100%;
  height: 100%;
  min-height: 400px;
  z-index: 1;
}

.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.loading-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.loading-text {
  color: #1976d2;
  font-weight: 500;
}

.user-location-dot {
  width: 12px;
  height: 12px;
  background: #1976d2;
  border-radius: 50%;
  border: 2px solid white;
  box-shadow: 0 0 0 2px #1976d2;
  animation: pulse 2s infinite;
}

.custom-pin-marker {
  display: flex;
  justify-content: center;
  align-items: center;
}

.custom-pin-marker i {
  color: #f44336;
  font-size: 30px;
  filter: drop-shadow(0 2px 2px rgba(0, 0, 0, 0.3));
}

@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(25, 118, 210, 0.4);
  }
  70% {
    box-shadow: 0 0 0 10px rgba(25, 118, 210, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(25, 118, 210, 0);
  }
}

.input-field {
  margin-bottom: 16px;
}
</style>
