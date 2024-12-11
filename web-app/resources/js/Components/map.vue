<template>
    <div class="dashboard">
        <!-- Map container -->
        <div id="map" style="height: 500px; width: 100%;"></div>

        <!-- Modal for adding marker details -->
        <v-dialog v-model="showModal" persistent max-width="600px">
            <v-card class="modal-card">
                <v-card-title class="text-h5 font-weight-bold">
                    Add New Marker
                </v-card-title>
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
                            label="Enter Description"
                            v-model="markerDescription"
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
                            label="Enter Additional Information"
                            v-model="markerAdditionalInfo"
                            outlined
                            dense
                            rows="3"
                            class="input-field"
                        ></v-textarea>
                    </v-form>
                </v-card-text>
                <v-card-actions class="actions">
                    <v-btn color="secondary" text @click="closeModal">Cancel</v-btn>
                    <v-btn color="primary" class="white--text" @click="addMarker">Save</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
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
            showModal: false,
            markerTitle: "",
            markerDescription: "",
            markerUrl: "",
            markerImage: null,
            markerAdditionalInfo: "",
            selectedLatLng: null,
        };
    },
    mounted() {
        this.map = L.map("map");

        // Use geolocation to get the user's current position
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const { latitude, longitude } = position.coords;

                    // Set the view to the user's current position
                    this.map.setView([latitude, longitude], 15);

                    // Add a marker for the current location
                    L.marker([latitude, longitude])
                        .addTo(this.map)
                        .bindPopup("You are here!")
                        .openPopup();
                },
                (error) => {
                    alert("Error getting your location: " + error.message);
                }
            );
        } else {
            alert("Geolocation is not supported by this browser.");
        }

        // Add OpenStreetMap tiles
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        }).addTo(this.map);

        // Show modal when the user clicks on the map
        this.map.on("click", (e) => {
            this.selectedLatLng = e.latlng;
            this.showModal = true;
        });
    },
    methods: {
        addMarker() {
            if (this.markerTitle && this.markerUrl) {
                const popupContent = `
                    <div>
                        <strong>${this.markerTitle}</strong>
                        <p>${this.markerDescription}</p>
                        <a href="${this.markerUrl}" target="_blank">More Info</a>
                        ${
                            this.markerImage
                                ? `<br><img src="${URL.createObjectURL(
                                      this.markerImage
                                  )}" style="width:100%; max-height:100px; object-fit:cover; margin-top:5px;">`
                                : ""
                        }
                        <p>${this.markerAdditionalInfo}</p>
                    </div>
                `;

                // Add the marker with text and optional image
                L.marker([this.selectedLatLng.lat, this.selectedLatLng.lng])
                    .addTo(this.map)
                    .bindPopup(popupContent)
                    .openPopup();

                // Reset modal data
                this.resetModalData();
                this.closeModal();
            } else {
                alert("Please fill all required fields.");
            }
        },
        closeModal() {
            this.showModal = false;
        },
        resetModalData() {
            this.markerTitle = "";
            this.markerDescription = "";
            this.markerUrl = "";
            this.markerImage = null;
            this.markerAdditionalInfo = "";
        },
    },
};
</script>

<style>
#map {
    height: 500px;
    width: 100%;
}

.modal-card {
    border-radius: 12px;
    background-color: #f9f9f9;
    box-shadow: 0px 8px 24px rgba(0, 0, 0, 0.2);
}

.input-field {
    border-radius: 8px;
    background: #ffffff;
    box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
    padding: 8px;
}

.v-card-title {
    border-bottom: 1px solid #e0e0e0;
    padding-bottom: 10px;
    color: #333333;
}

.v-card-actions {
    border-top: 1px solid #e0e0e0;
    padding-top: 10px;
}

.v-btn {
    min-width: 80px;
    font-weight: 600;
}
</style>
