<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="$emit('close')"></div>

      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="sm:flex sm:items-start">
            <div class="mt-3 text-left w-full">
              <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                Comments
              </h3>

              <!-- Comments List -->
              <div class="max-h-96 overflow-y-auto">
                <div v-for="comment in sortedComments" :key="comment.id" class="bg-gray-50 p-4 rounded-lg mb-3">
                  <div class="space-y-1 text-left">
                    <div class="font-medium text-gray-900">{{ comment.userName }} commented:</div>
                    <p class="text-gray-700">{{ comment.text }}</p>
                    <div class="text-xs text-gray-500">
                      {{ formatDate(comment.created_at) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
          <!-- Comment Input -->
          <div class="mb-4 px-4">
                <div class="flex items-center space-x-4 w-full">
                  <input 
                    v-model="newComment" 
                    type="text" 
                    placeholder="Add a comment..."
                    class="flex-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500"
                    @keyup.enter="submitComment"
                  />
                  <button 
                    @click="submitComment"
                    :disabled="!newComment?.trim()"
                    class="bg-teal-500 text-white px-4 py-2 rounded-lg disabled:opacity-50 hover:bg-teal-600 transition-colors"
                  >
                    Post
                  </button>
                </div>
              </div>

        <!-- Modal footer -->
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <button 
            type="button" 
            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            @click="$emit('close')"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';

export default {
  name: 'CommentModal',
  props: {
    show: {
      type: Boolean,
      required: true
    },
    comments: {
      type: Array,
      required: true
    },
    itemId: {
      type: [Number, String],
      required: true
    },
    itemType: {
      type: String,
      required: true
    }
  },
  emits: ['close', 'submit-comment'],
  setup(props, { emit }) {
    const newComment = ref('');

    // Sort comments by date, newest first
    const sortedComments = computed(() => {
      return [...props.comments].sort((a, b) => {
        return new Date(b.created_at) - new Date(a.created_at);
      });
    });

    const submitComment = () => {
      if (newComment.value.trim()) {
        emit('submit-comment', {
          itemId: props.itemId,
          text: newComment.value,
          itemType: props.itemType
        });
        newComment.value = '';
      }
    };

    const formatDate = (date) => {
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    };

    return {
      newComment,
      submitComment,
      formatDate,
      sortedComments
    };
  }
};
</script>