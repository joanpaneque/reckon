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
    enter-active-class="transition-opacity duration-modern"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition-opacity duration-modern"
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
        enter-active-class="transition-all duration-modern"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition-all duration-modern"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div
          v-if="show"
          class="card-modern rounded-modern shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
          @click.stop
        >
          <!-- Header -->
          <div class="flex items-center justify-between p-4 border-b border-dark-border">
            <h3 class="text-lg font-semibold text-text-primary">Upload Media</h3>
            <button
              @click="close"
              :disabled="uploading"
              class="p-1 hover:bg-dark-primary rounded-modern transition-colors duration-modern disabled:opacity-50"
            >
              <X :size="20" :stroke-width="2" />
            </button>
          </div>

          <!-- Body -->
          <div class="p-4 space-y-4">
            <!-- Media Preview -->
            <div class="rounded-modern overflow-hidden bg-dark-primary">
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
              <label for="caption" class="block text-sm font-medium text-text-secondary mb-2">
                Add a caption (optional)
              </label>
              <textarea
                id="caption"
                v-model="caption"
                rows="3"
                :disabled="uploading"
                class="input-modern w-full resize-none disabled:opacity-50"
                placeholder="Write something about this moment..."
              />
            </div>
          </div>

          <!-- Footer -->
          <div class="flex items-center justify-end gap-3 p-4 border-t border-dark-border">
            <button
              @click="close"
              :disabled="uploading"
              class="px-5 py-2 text-sm font-medium text-text-primary hover:bg-dark-primary rounded-modern transition-colors duration-modern disabled:opacity-50"
            >
              Cancel
            </button>
            <button
              @click="handleUpload"
              :disabled="uploading"
              class="btn-primary flex items-center gap-2 disabled:opacity-50"
            >
              <Loader v-if="uploading" :size="16" class="animate-spin" :stroke-width="2" />
              <Upload v-else :size="16" :stroke-width="2" />
              {{ uploading ? 'Uploading...' : 'Upload' }}
            </button>
          </div>
        </div>
      </Transition>
    </div>
  </Transition>
</template>

