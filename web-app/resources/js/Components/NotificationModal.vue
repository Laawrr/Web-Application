<template>
  <div v-if="show && notification" class="fixed inset-0 z-[9999] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Background overlay -->
    <div class="fixed inset-0" @click="$emit('close')">
      <div class="absolute inset-0 bg-gray-500/75 backdrop-blur-md"></div>
    </div>

    <!-- Modal panel -->
    <div class="fixed inset-0 overflow-y-auto">
      <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
          <!-- Modal content -->
          <div class="bg-white">
            <div class="h-full max-h-[80vh] overflow-y-auto px-4 pb-4 pt-5 sm:p-6">
              <div class="w-full relative">
                <button 
                  @click="$emit('close')" 
                  class="absolute right-0 top-0 text-gray-400 hover:text-gray-500 focus:outline-none"
                >
                  <span class="sr-only">Close</span>
                  <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                  Notification Details
                </h3>

                <!-- Notification Details -->
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                  <div class="space-y-4">
                    <!-- User Info -->
                    <div class="flex items-center space-x-3">
                      <div class="w-12 h-12 rounded-full bg-teal-500 flex items-center justify-center text-white font-bold text-xl">
                        {{ notification?.user?.name?.charAt(0) || 'U' }}
                      </div>
                      <div>
                        <h4 class="text-lg font-semibold text-gray-800">{{ notification?.user?.name || 'Unknown User' }}</h4>
                        <p class="text-sm text-gray-500">{{ notification?.created_at }}</p>
                      </div>
                    </div>

                    <!-- Item Details -->
                    <div class="border-t border-gray-200 pt-4">
                      <h5 class="font-medium text-gray-700 mb-2">Item Details</h5>
                      
                      <!-- Item Image -->
                      <div v-if="notification?.itemImage" class="mb-4">
                        <img 
                          :src="notification.itemImage" 
                          :alt="notification?.itemName"
                          class="w-full h-48 object-cover rounded-lg shadow-sm"
                          @error="handleImageError"
                        />
                      </div>
                      
                      <div class="space-y-2">
                        <div class="flex items-center justify-between">
                          <span class="text-gray-600">Item Name:</span>
                          <span class="font-medium">{{ notification?.itemName || 'Unknown Item' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                          <span class="text-gray-600">Comment:</span>
                          <span class="font-medium">{{ notification?.comment || 'No comment' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                          <span class="text-gray-600">Status:</span>
                          <span class="font-medium">{{ notification?.read_at ? 'Read' : 'Unread' }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                  <button
                    type="button"
                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                    @click="$emit('close')"
                  >
                    Close
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: {
    type: Boolean,
    required: true,
    default: false
  },
  notification: {
    type: Object,
    required: true,
    default: null
  }
});

defineEmits(['close']);

const handleImageError = (event) => {
  event.target.style.display = 'none';
};
</script>

<style scoped>
.backdrop-blur-md {
  backdrop-filter: blur(8px);
}
</style>
