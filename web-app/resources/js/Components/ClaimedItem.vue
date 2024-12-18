<template>
    <div class="bg-white rounded-lg shadow-md p-4 mb-4">
        <div class="flex items-start space-x-4">
            <!-- Item Image -->
            <div class="w-24 h-24 flex-shrink-0">
                <img 
                    :src="item.image_url" 
                    :alt="item.name"
                    class="w-full h-full object-cover rounded-lg"
                />
            </div>

            <!-- Item Details -->
            <div class="flex-grow">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-semibold">{{ item.name }}</h3>
                    <span :class="[
                        'px-3 py-1 rounded-full text-sm font-medium',
                        {
                            'bg-yellow-100 text-yellow-800': status === 'pending',
                            'bg-green-100 text-green-800': status === 'approved',
                            'bg-red-100 text-red-800': status === 'rejected'
                        }
                    ]">
                        {{ status.charAt(0).toUpperCase() + status.slice(1) }}
                    </span>
                </div>

                <p class="text-gray-600 text-sm mb-2">{{ item.description }}</p>
                
                <div class="text-sm text-gray-500">
                    <p>Location: {{ item.location }}</p>
                    <p>Date: {{ formatDate }}</p>
                </div>

                <!-- Action Buttons -->
                <div v-if="status === 'pending'" class="mt-4 flex space-x-2">
                    <button class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 flex items-center text-sm">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Approve
                    </button>
                    <button class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center text-sm">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Reject
                    </button>
                </div>

                <!-- Proof Image Preview -->
                <div v-if="proofImage" class="mt-3">
                    <button 
                        @click="showProof = true"
                        class="inline-flex items-center text-sm text-teal-600 hover:text-teal-700"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        View Proof
                    </button>
                </div>
            </div>
        </div>

        <!-- Proof Modal -->
        <Proof 
            v-if="showProof" 
            :image="proofImage" 
            @close="showProof = false"
        />
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import Proof from './Proof.vue';

const props = defineProps({
    item: {
        type: Object,
        required: true
    },
    status: {
        type: String,
        default: 'pending'
    },
    proofImage: {
        type: String,
        default: ''
    }
});

const showProof = ref(false);

const formatDate = computed(() => {
    if (!props.item.created_at) return '';
    return new Date(props.item.created_at).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
});
</script>