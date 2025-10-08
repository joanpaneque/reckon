<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { X, Upload, Send } from 'lucide-vue-next';

const props = defineProps({
  show: {
    type: Boolean,
    required: true,
  },
  user: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['close']);

const message = ref('');
const selectedFile = ref(null);
const imagePreview = ref(null);
const isSubmitting = ref(false);
const showImageViewer = ref(false);

// Reset form when modal opens
watch(() => props.show, (newValue) => {
  if (newValue) {
    message.value = '';
    selectedFile.value = null;
    imagePreview.value = null;
    isSubmitting.value = false;
    showImageViewer.value = false;
  }
});

const handleFileSelect = (event) => {
  const file = event.target.files[0];
  if (!file) {
    selectedFile.value = null;
    imagePreview.value = null;
    return;
  }

  // Validate file type
  const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
  if (!validTypes.includes(file.type)) {
    alert('Please select a valid image (JPG, PNG, GIF).');
    return;
  }

  // Validate file size (10MB max)
  if (file.size > 10 * 1024 * 1024) {
    alert('File size must be less than 10MB.');
    return;
  }

  selectedFile.value = file;

  // Create preview
  const reader = new FileReader();
  reader.onload = (e) => {
    imagePreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
};

const removeImage = () => {
  selectedFile.value = null;
  imagePreview.value = null;
  showImageViewer.value = false;
};

const handleSubmit = () => {
  if (!message.value.trim()) {
    alert('Please enter a message.');
    return;
  }

  isSubmitting.value = true;

  const formData = new FormData();
  formData.append('sent_to', props.user.id);
  formData.append('message', message.value);
  if (selectedFile.value) {
    formData.append('image', selectedFile.value);
  }

  router.post(route('motivations.store'), formData, {
    preserveScroll: true,
    onSuccess: () => {
      emit('close');
    },
    onError: (errors) => {
      console.error('Error sending motivation:', errors);
      alert('Error sending motivation. Please try again.');
    },
    onFinish: () => {
      isSubmitting.value = false;
    },
  });
};

const close = () => {
  if (!isSubmitting.value) {
    emit('close');
  }
};
</script>

<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50"
    @click.self="close"
  >
    <div class="card-modern rounded-modern shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="flex items-center justify-between p-4 border-b border-dark-border">
        <h2 class="text-xl font-semibold text-text-primary">Motivate {{ user.name }}</h2>
        <button
          @click="close"
          :disabled="isSubmitting"
          class="p-2 hover:bg-dark-primary rounded-full transition-colors duration-modern disabled:opacity-50"
        >
          <X :size="20" :stroke-width="2" />
        </button>
      </div>

      <!-- Body -->
      <div class="p-4 space-y-4">
        <!-- Message input -->
        <div>
          <label class="block text-sm font-medium text-text-secondary mb-2">
            Motivation message *
          </label>
          <textarea
            v-model="message"
            rows="4"
            :disabled="isSubmitting"
            class="input-modern w-full resize-none disabled:opacity-50"
            placeholder="Write a motivational message..."
            maxlength="1000"
          ></textarea>
          <div class="mt-1 text-xs text-text-secondary text-right">
            {{ message.length }} / 1000
          </div>
        </div>

        <!-- Image upload -->
        <div>
          <label class="block text-sm font-medium text-text-secondary mb-2">
            Image (optional)
          </label>

          <!-- Upload button -->
          <label
            v-if="!imagePreview"
            class="flex items-center justify-center gap-2 px-4 py-3 border-2 border-dashed border-dark-input-border rounded-modern cursor-pointer hover:border-accent hover:bg-accent/10 transition-colors duration-modern"
            :class="{ 'opacity-50 cursor-not-allowed': isSubmitting }"
          >
            <Upload :size="20" :stroke-width="2" class="text-text-secondary" />
            <span class="text-sm text-text-secondary">Upload image</span>
            <input
              type="file"
              accept="image/*"
              class="hidden"
              :disabled="isSubmitting"
              @change="handleFileSelect"
            />
          </label>

          <!-- Image preview -->
          <div v-if="imagePreview" class="relative">
            <img
              :src="imagePreview"
              alt="Preview"
              class="w-full rounded-modern border border-dark-border cursor-pointer hover:opacity-90 transition-opacity duration-modern"
              style="max-height: 200px; object-fit: contain;"
              @click="showImageViewer = true"
            />
            <button
              @click="removeImage"
              :disabled="isSubmitting"
              class="absolute top-2 right-2 p-2 bg-red-600 text-text-primary rounded-full hover:bg-red-700 transition-colors duration-modern disabled:opacity-50 shadow-lg"
            >
              <X :size="16" :stroke-width="2" />
            </button>
            <div class="mt-1 text-xs text-text-secondary text-center">
              Click on image to enlarge
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex items-center justify-end gap-3 p-4 border-t border-dark-border">
        <button
          @click="close"
          :disabled="isSubmitting"
          class="px-4 py-2 text-sm font-medium text-text-primary bg-dark-primary hover:bg-dark-border rounded-modern transition-colors duration-modern disabled:opacity-50"
        >
          Cancel
        </button>
        <button
          @click="handleSubmit"
          :disabled="isSubmitting || !message.trim()"
          class="btn-primary disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
        >
          <Send :size="16" :stroke-width="2" />
          {{ isSubmitting ? 'Sending...' : 'Send motivation' }}
        </button>
      </div>
    </div>

    <!-- Image Viewer Modal -->
    <div
      v-if="showImageViewer && imagePreview"
      class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black bg-opacity-90"
      @click="showImageViewer = false"
    >
      <button
        @click="showImageViewer = false"
        class="absolute top-4 right-4 p-2 bg-accent hover:bg-accent-hover rounded-full transition-colors duration-modern"
      >
        <X :size="24" :stroke-width="2" class="text-dark-primary" />
      </button>
      <img
        :src="imagePreview"
        alt="Preview"
        class="max-w-full max-h-full rounded-modern"
        @click.stop
      />
    </div>
  </div>
</template>

