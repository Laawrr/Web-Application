<template>
    <div>
        <div v-for="item in items" :key="item.id" class="bg-white rounded-lg shadow-md p-4 mb-4">
            <div class="flex items-start space-x-4">
                <!-- Item Image -->
                <div class="w-24 h-24 flex-shrink-0">
                    <img :src="item.image_url" :alt="item.item_name" class="w-full h-full object-cover rounded-lg" />
                </div>

                <!-- Item Details -->
                <div class="flex-grow">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-semibold">{{ item.item_name }}</h3>
                        <span :class="[
                            'px-3 py-1 rounded-full text-sm font-medium',
                            {
                                'bg-yellow-100 text-yellow-800': item.status === 'pending',
                                'bg-green-100 text-green-800': item.status === 'approved',
                                'bg-red-100 text-red-800': item.status === 'rejected'
                            }
                        ]">
                            {{ item.status ? item.status.charAt(0).toUpperCase() + item.status.slice(1) : 'Unknown' }}
                        </span>
                    </div>

                    <p class="text-gray-600 text-sm mb-2">{{ item.description }}</p>

                    <div class="text-sm text-gray-500 mb-2">
                        <p>Category: {{ item.category }}</p>
                        <p>Location: {{ formatLocation(item.location) }}</p>
                        <p>Found Date: {{ formatDate(item.found_date) }}</p>
                    </div>

                    <div class="text-sm text-gray-500">
                        <p>Contact Number: {{ item.contact_number }}</p>
                        <p>
                            Facebook:
                            <a :href="item.facebook_link" class="text-teal-600 hover:underline" target="_blank">
                                View Profile
                            </a>
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div v-if="item.status === 'pending'" class="mt-4 flex space-x-2">
                        <button
                            class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 flex items-center text-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Approve
                        </button>
                        <button
                            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center text-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Reject
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

// State for items
const items = ref([]);

// Fetch items from the API
const fetchItems = async () => {
    try {
        const response = await fetch('/found-items');
        const data = await response.json();

        // Log the fetched data
        console.log('Fetched Items:', data);

        items.value = data;
    } catch (error) {
        console.error('Error fetching found items:', error);
    }
};

// Format date
const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

// Format location
const formatLocation = (location) => {
    const [latitude, longitude] = location.split(',');
    return `Lat: ${latitude.trim()}, Long: ${longitude.trim()}`;
};

// Fetch data on component mount
onMounted(() => {
    fetchItems();
});
</script>
