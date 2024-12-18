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
    <div class="map-wrapper" :class="{ 'map-disabled': disabled }" @touchstart="handleTouchStart" @touchmove="handleTouchMove" @touchend="handleTouchEnd">
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
      default: true,
    },
  },
  data() {
    return {
      map: null,
      searchQuery: "",
      loadingSearch: false,
      isLoading: true,
      snackbar: {
        visible: false,
        message: "",
        color: "error",
      },
      selectedLatLng: null,
      currentMarker: null,
      userLocationMarker: null,
      watchId: null,
      startX: null,
      endX: null,
      _lastPinchDist: null,
      initialLocationSet: false,
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
          tap: !L.Browser.mobile,
          bounceAtZoomLimits: true,
          maxZoom: 18,
          minZoom: 3
        });

        // Add zoom control with custom position
        L.control.zoom({
          position: 'bottomright'
        }).addTo(this.map);

        // Add scale control
        L.control.scale({
          imperial: false,
          position: 'bottomleft'
        }).addTo(this.map);

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
          attribution: "OpenStreetMap contributors",
          maxZoom: 18,
          tileSize: 512,
          zoomOffset: -1
        }).addTo(this.map);

        // Handle touch events directly on the map element
        const mapElement = this.$refs.mapRef;
        let startTouch = null;
        let currentTouch = null;

        mapElement.addEventListener('touchstart', (e) => {
          startTouch = e.touches[0];
        }, { passive: true });

        mapElement.addEventListener('touchmove', (e) => {
          if (!startTouch) return;
          currentTouch = e.touches[0];
          
          // Calculate the distance moved
          const deltaX = currentTouch.clientX - startTouch.clientX;
          const deltaY = currentTouch.clientY - startTouch.clientY;
          
          // Pan the map
          this.map.panBy([-deltaX, -deltaY]);
          
          // Update the start position
          startTouch = currentTouch;
        }, { passive: true });

        mapElement.addEventListener('touchend', () => {
          startTouch = null;
          currentTouch = null;
        }, { passive: true });

        // Handle pinch zoom using hammer.js if available
        if (typeof Hammer !== 'undefined') {
          const hammer = new Hammer(mapElement);
          hammer.get('pinch').set({ enable: true });
          
          hammer.on('pinch', (e) => {
            const delta = e.scale > 1 ? 1 : -1;
            this.map.setZoom(this.map.getZoom() + delta);
          });
        }

        // Set a default view immediately
        this.map.setView([14.5995, 120.9842], 13); // Manila coordinates

        // Add click handler for marking locations
        this.map.on("click", (e) => {
          if (this.disabled) return;

          const latlng = e.latlng;

          if (this.currentMarker) {
            this.map.removeLayer(this.currentMarker);
          }

          const pinIcon = L.divIcon({
            html: '<i class="fas fa-map-marker-alt"></i>',
            className: "custom-pin-marker",
            iconSize: [30, 30],
            iconAnchor: [15, 30],
          });

          this.currentMarker = L.marker(latlng, {
            icon: pinIcon,
          }).addTo(this.map);

          this.selectedLatLng = latlng;
          
          // Emit the selected location to the parent component
          this.$emit("location-selected", {
            lat: latlng.lat,
            lng: latlng.lng
          });
          
          // Show coordinates in a popup
          this.currentMarker.bindPopup(
            `<b>Selected Location</b>`
          ).openPopup();
        });

        // Get current location
        this.getCurrentLocation();

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
        this.showSnackbar("Geolocation is not supported by your browser.", "error");
        return;
      }

      const options = {
        enableHighAccuracy: true,
        timeout: 5000,
        maximumAge: 0
      };

      // Watch position instead of one-time get
      this.watchId = navigator.geolocation.watchPosition(
        (position) => {
          const { latitude, longitude } = position.coords;
          
          // Update or create user location marker
          if (this.userLocationMarker) {
            this.userLocationMarker.setLatLng([latitude, longitude]);
          } else {
            // Create a custom icon for user location
            const userIcon = L.divIcon({
              className: 'user-location-marker',
              html: '<div class="marker-dot"></div><div class="marker-pulse"></div>',
              iconSize: [20, 20],
              iconAnchor: [10, 10]
            });

            this.userLocationMarker = L.marker([latitude, longitude], {
              icon: userIcon,
              zIndexOffset: 1000, // Keep marker on top
              interactive: false // Prevent marker from being clickable
            }).addTo(this.map);
          }

          // Only set view on initial load or when location changes significantly
          if (!this.initialLocationSet) {
            this.map.setView([latitude, longitude], 15);
            this.initialLocationSet = true;
          }
        },
        (error) => {
          let message = "Unable to retrieve your location.";
          switch (error.code) {
            case error.PERMISSION_DENIED:
              message = "Location access was denied.";
              break;
            case error.POSITION_UNAVAILABLE:
              message = "Location information is unavailable.";
              break;
            case error.TIMEOUT:
              message = "Location request timed out.";
              break;
          }
          this.showSnackbar(message, "error");
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
    handleTouchStart(event) {
      this.startX = event.touches[0].clientX;
    },
    handleTouchMove(event) {
      this.endX = event.touches[0].clientX;
    },
    handleTouchEnd() {
      const deltaX = this.endX - this.startX;
      if (deltaX > 50) {
        // Swipe right
        this.swipeRight();
      } else if (deltaX < -50) {
        // Swipe left
        this.swipeLeft();
      }
    },
    swipeRight() {
      // Logic to shift map view right
      console.log('Swiped right');
      // Add your logic here to change the map view
    },
    swipeLeft() {
      // Logic to shift map view left
      console.log('Swiped left');
      // Add your logic here to change the map view
    },
  },
  beforeDestroy() {
    if (this.watchId !== null) {
      navigator.geolocation.clearWatch(this.watchId);
    }
    if (this.map) {
      if (this.currentMarker) {
        this.map.removeLayer(this.currentMarker);
      }
      if (this.userLocationMarker) {
        this.map.removeLayer(this.userLocationMarker);
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
  touch-action: pan-x pan-y;
}

.map-wrapper {
  position: relative;
  width: 100%;
  height: 600px;
  border-radius: 8px;
  overflow: hidden;
  touch-action: none;
}

.custom-map {
  width: 100%;
  height: 100%;
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

/* Add smooth zoom animation */
.leaflet-zoom-animated {
  transition: transform 0.25s cubic-bezier(0,0,0.25,1);
}

/* Improve touch feedback */
.leaflet-marker-icon {
  transition: transform 0.2s ease-out;
}

.leaflet-marker-icon:active {
  transform: scale(1.1);
}

/* Add touch ripple effect */
.leaflet-control-zoom a {
  position: relative;
  overflow: hidden;
}

.leaflet-control-zoom a::after {
  content: '';
  position: absolute;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  background: rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  transform: scale(0);
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.3s, transform 0.3s;
}

.leaflet-control-zoom a:active::after {
  transform: scale(2);
  opacity: 1;
  transition: 0s;
}

/* Custom user location marker styles */
.user-location-marker {
  position: relative;
}

.marker-dot {
  width: 12px;
  height: 12px;
  background-color: #4CAF50;
  border: 2px solid white;
  border-radius: 50%;
  box-shadow: 0 0 4px rgba(0,0,0,0.3);
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.marker-pulse {
  width: 24px;
  height: 24px;
  background-color: rgba(76, 175, 80, 0.3);
  border-radius: 50%;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% {
    transform: translate(-50%, -50%) scale(0.5);
    opacity: 1;
  }
  100% {
    transform: translate(-50%, -50%) scale(2);
    opacity: 0;
  }
}
</style>
