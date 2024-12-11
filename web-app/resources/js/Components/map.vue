<template>
    <div class="dashboard">
        <!-- Other dashboard components -->
        <div id="map" style="height: 500px; width: 100%;"></div>
    </div>
</template>

<script>
import L from "leaflet";
import "leaflet/dist/leaflet.css";

export default {
    name: "Map",
    mounted() {
        const map = L.map("map");

        // Use geolocation to get current position
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const { latitude, longitude } = position.coords;

                    // Set the view to the user's current position with custom zoom level
                    map.setView([latitude, longitude], 15);

                    // Add a marker for the current location
                    const userMarker = L.marker([latitude, longitude]).addTo(map)
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
        }).addTo(map);

        // Add a marker where the user clicks and allow adding a custom text label
        map.on("click", (e) => {
            const { lat, lng } = e.latlng;

            // Prompt the user to enter custom text for the marker
            const userText = prompt("Enter text for the marker:", "Custom marker text");

            if (userText) {
                // Add a marker at the clicked location with the user's text as the popup content
                L.marker([lat, lng]).addTo(map)
                    .bindPopup(userText)
                    .openPopup();
            }
        });
    },
};
</script>

<style>
#map {
    height: 500px;
    width: 100%;
}
</style>