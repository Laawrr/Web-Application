<template>
    <div>
        <!-- Combined Found Items and Claims Section -->
        <div v-if="combinedItems.length" class="mb-8">
            <div v-for="item in combinedItems" :key="item.id" class="bg-white rounded-lg shadow-md p-4 mb-4">
                <div class="flex items-start space-x-4">
                    <!-- Item Image -->
                    <div class="w-24 h-24 flex-shrink-0">
                        <img :src="item.image_url" :alt="item.item_name" class="w-full h-full object-cover rounded-lg" />
                    </div>

                    <!-- Item Details -->
                    <div class="flex-grow">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-semibold">{{ item.item_name }}</h3>
                            <span :class="[ 'px-3 py-1 rounded-full text-sm font-medium', {
                                    'bg-yellow-100 text-yellow-800': item.claim_status === 'pending',
                                    'bg-green-100 text-green-800': item.claim_status === 'approved',
                                    'bg-red-100 text-red-800': item.claim_status === 'rejected'
                                }]">
                                {{ item.claim_status ? item.claim_status.charAt(0).toUpperCase() + item.claim_status.slice(1) : 'Unknown' }}
                            </span>
                        </div>

                        <p class="text-gray-600 text-sm mb-2">{{ item.description }}</p>

                        <div class="text-sm text-gray-500 mb-2">
                            <p>Category: {{ item.category }}</p>
                            <p v-if="item.found_date">Found Date: {{ formatDate(item.found_date) }}</p>
                            <p v-if="item.submission_date">Claim Submission Date: {{ formatDate(item.submission_date) }}</p>
                        </div>

                        <div class="text-sm text-gray-500 mb-2">
                            <p>Submitted By: {{ item.user_name }}</p>
                        </div>

                        <div class="text-sm text-gray-500">
                            <p v-if="item.contact_number">Contact Number: {{ item.contact_number }}</p>
                            <p v-if="item.proof_of_ownership">
                                Proof of Ownership:
                                <button @click="viewProof(item.proof_of_ownership)" class="text-teal-600 hover:underline text-sm">
                                    View Proof Image
                                </button>
                            </p>
                        </div>

                        <div class="mt-4 flex space-x-2">
                            <button @click="updateClaimStatus(item.id, 'Approved')" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 flex items-center text-sm">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Approve
                            </button>
                            <button @click="updateClaimStatus(item.id, 'Rejected')" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center text-sm">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Reject
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

// State for combined items
const combinedItems = ref([]);

// Fetch items from the API
const fetchItems = async () => {
    try {
        // Fetch found-items
        const foundItemsResponse = await fetch('/found-items');
        const foundItemsData = await foundItemsResponse.json();
        
        // Fetch claims
        const claimsResponse = await fetch('/claim-items');
        const claimsData = await claimsResponse.json();

        // Fetch users
        const usersResponse = await fetch('/users');
        const usersData = await usersResponse.json();
        
        // Map user data by user_id for quick lookup
        const usersMap = usersData.reduce((map, user) => {
            map[user.id] = user.name;
            return map;
        }, {});

        // Combine found items with claims based on item_id
        const items = foundItemsData.map(foundItem => {
            // Find the corresponding claim for the found item
            const claim = claimsData.find(c => c.item_id === foundItem.id);
            
            // If a claim exists for this found item, combine the data
            if (claim) {
                return {
                    ...foundItem,
                    claim_status: claim.claim_status,
                    proof_of_ownership: claim.proof_of_ownership,
                    submission_date: claim.submission_date,
                    user_name: usersMap[claim.user_id] || 'Unknown',  // Get user name from usersMap
                };
            } else {
                // If no claim exists for this found item, return the found item with a default claim status
                return {
                    ...foundItem,
                    claim_status: 'No Claim',
                    user_name: usersMap[foundItem.user_id] || 'N/A',  // Get user name from usersMap
                };
            }
        });

        // Set the combined items
        combinedItems.value = items;

        console.log('Fetched Combined Items:', combinedItems.value);
    } catch (error) {
        console.error('Error fetching items:', error);
    }
};

// Format date
const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
};

// Show proof image in modal
const viewProof = (proofUrl) => {
    // You can use a modal to display the image. Here, we just print the URL for simplicity.
    console.log('View Proof Image:', proofUrl);
};

// Update the claim status (approve or reject)
const updateClaimStatus = async (itemId, status) => {
    try {
        console.log('Attempting to update claim status...');

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        console.log('CSRF Token:', csrfToken);

        const response = await fetch(`/claims/${itemId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken, // Include CSRF token
            },
            body: JSON.stringify({ claim_status: status }),
        });

        console.log('Response status:', response.status);

        if (response.ok) {
            const responseData = await response.json();  // Assuming the server sends confirmation data
            console.log('Server response data:', responseData); // Log the server response
            
            if (responseData.success) {
                console.log('Claim status updated in the database successfully.');
                // Update the status in the combined items list
                combinedItems.value = combinedItems.value.map(item => {
                    if (item.id === itemId) {
                        return { ...item, claim_status: status };
                    }
                    return item;
                });
                alert('Claim status updated successfully!');
            } else {
                console.log('Claim status update failed at the database level.');
                alert(responseData.message || 'Failed to update claim status.');
            }
        } else {
            const errorData = await response.json();
            console.error('Error response:', errorData);
            alert(errorData.message || 'An error occurred while updating the claim status.');
        }
    } catch (error) {
        console.error('Error updating claim status:', error);
        alert('An error occurred while updating the claim status.');
    }
};






onMounted(() => {
    fetchItems();
});
</script>
