<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { X, Upload, Loader } from 'lucide-vue-next';

const props = defineProps({
  show: Boolean,
  mediaFile: File,
  mediaPreview: String,
  mediaType: String,
  userHabitId: Number,
});

const emit = defineEmits(['close', 'uploaded']);

const caption = ref('');
const uploading = ref(false);

const handleUpload = () => {
  if (!props.userHabitId || !props.mediaFile) {
    return;
  }

  uploading.value = true;

  const formData = new FormData();
  formData.append('media', props.mediaFile);
  formData.append('caption', caption.value);

  router.post(route('habits.media.upload', props.userHabitId), formData, {
    preserveScroll: true,
    onSuccess: () => {
      uploading.value = false;
      caption.value = '';
      emit('uploaded');
      emit('close');
    },
    onError: (errors) => {
      uploading.value = false;
      console.error('Upload error:', errors);
      alert('Error uploading media. Please try again.');
    },
  });
};

const close = () => {
  if (!uploading.value) {
    caption.value = '';
    emit('close');
  }
};
</script>

<template>
  <!-- Modal Backdrop -->
  <Transition
    enter-active-class="transition-opacity duration-200"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition-opacity duration-200"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="show"
      class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
      @click.self="close"
    >
      <!-- Modal Content -->
      <Transition
        enter-active-class="transition-all duration-200"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition-all duration-200"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div
          v-if="show"
          class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
          @click.stop
        >
          <!-- Header -->
          <div class="flex items-center justify-between p-4 border-b">
            <h3 class="text-lg font-semibold">Upload Media</h3>
            <button
              @click="close"
              :disabled="uploading"
              class="p-1 hover:bg-gray-100 rounded transition-colors disabled:opacity-50"
            >
              <X :size="20" />
            </button>
          </div>

          <!-- Body -->
          <div class="p-4 space-y-4">
            <!-- Media Preview -->
            <div class="rounded-lg overflow-hidden bg-gray-100">
              <img
                v-if="mediaType === 'image'"
                :src="mediaPreview"
                alt="Preview"
                class="w-full h-auto max-h-96 object-contain"
              />
              <video
                v-else-if="mediaType === 'video'"
                :src="mediaPreview"
                controls
                class="w-full h-auto max-h-96"
              />
            </div>

            <!-- Caption Input -->
            <div>
              <label for="caption" class="block text-sm font-medium text-gray-700 mb-2">
                Add a caption (optional)
              </label>
              <textarea
                id="caption"
                v-model="caption"
                rows="3"
                :disabled="uploading"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-50 disabled:text-gray-500"
                placeholder="Write something about this moment..."
              />
            </div>
          </div>

          <!-- Footer -->
          <div class="flex items-center justify-end gap-3 p-4 border-t bg-gray-50">
            <button
              @click="close"
              :disabled="uploading"
              class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-colors disabled:opacity-50"
            >
              Cancel
            </button>
            <button
              @click="handleUpload"
              :disabled="uploading"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors disabled:opacity-50 flex items-center gap-2"
            >
              <Loader v-if="uploading" :size="16" class="animate-spin" />
              <Upload v-else :size="16" />
              {{ uploading ? 'Uploading...' : 'Upload' }}
            </button>
          </div>
        </div>
      </Transition>
    </div>
  </Transition>
</template>

