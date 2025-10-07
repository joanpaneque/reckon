<script setup>
import { ref, onMounted, computed } from 'vue';
import { X, Heart, ChevronLeft, ChevronRight, Send } from 'lucide-vue-next';
import axios from 'axios';

const motivations = ref([]);
const responses = ref([]);
const currentIndex = ref(0);
const showModal = ref(false);
const replyMessage = ref('');
const isSubmitting = ref(false);
const showImageViewer = ref(false);

// Combine motivations and responses
const allItems = computed(() => {
  const items = [];

  // Add motivations (received from others)
  motivations.value.forEach(m => {
    items.push({
      type: 'motivation',
      data: m,
    });
  });

  // Add responses (replies to motivations you sent)
  responses.value.forEach(r => {
    items.push({
      type: 'response',
      data: r,
    });
  });

  return items;
});

const currentItem = computed(() => {
  return allItems.value[currentIndex.value] || null;
});

const totalCount = computed(() => allItems.value.length);

const loadMotivations = async () => {
  try {
    const [motivationsRes, responsesRes] = await Promise.all([
      axios.get(route('motivations.unread')),
      axios.get(route('motivations.responses')),
    ]);

    motivations.value = motivationsRes.data;
    responses.value = responsesRes.data;

    if (allItems.value.length > 0) {
      showModal.value = true;
      currentIndex.value = 0;
    }
  } catch (error) {
    console.error('Error loading motivations:', error);
  }
};

const goToNext = () => {
  if (currentIndex.value < totalCount.value - 1) {
    currentIndex.value++;
    replyMessage.value = '';
    showImageViewer.value = false;
  }
};

const goToPrevious = () => {
  if (currentIndex.value > 0) {
    currentIndex.value--;
    replyMessage.value = '';
    showImageViewer.value = false;
  }
};

const closeWithoutReply = async () => {
  if (!currentItem.value) return;

  isSubmitting.value = true;

  try {
    if (currentItem.value.type === 'motivation') {
      // Close motivation without reply
      await axios.post(route('motivations.close', currentItem.value.data.id), {
        receiver_message: null,
      });

      // Remove from array
      motivations.value = motivations.value.filter(m => m.id !== currentItem.value.data.id);
    } else if (currentItem.value.type === 'response') {
      // Close response
      await axios.post(route('motivations.close-response', currentItem.value.data.id));

      // Remove from array
      responses.value = responses.value.filter(r => r.id !== currentItem.value.data.id);
    }

    // Move to next or close modal
    if (allItems.value.length > 0) {
      if (currentIndex.value >= allItems.value.length) {
        currentIndex.value = allItems.value.length - 1;
      }
    } else {
      showModal.value = false;
    }
  } catch (error) {
    console.error('Error closing:', error);
    alert('Error closing. Please try again.');
  } finally {
    isSubmitting.value = false;
  }
};

const closeWithReply = async () => {
  if (!currentItem.value || currentItem.value.type !== 'motivation') return;
  
  if (!replyMessage.value.trim()) {
    alert('Please write a reply message.');
    return;
  }
  
  isSubmitting.value = true;
  
  try {
    await axios.post(route('motivations.close', currentItem.value.data.id), {
      receiver_message: replyMessage.value,
    });
    
    // Remove from array
    motivations.value = motivations.value.filter(m => m.id !== currentItem.value.data.id);
    replyMessage.value = '';
    
    // Move to next or close modal
    if (allItems.value.length > 0) {
      if (currentIndex.value >= allItems.value.length) {
        currentIndex.value = allItems.value.length - 1;
      }
    } else {
      showModal.value = false;
    }
  } catch (error) {
    console.error('Error closing with reply:', error);
    alert('Error sending reply. Please try again.');
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(() => {
  loadMotivations();
});

defineExpose({
  loadMotivations,
});
</script>

<template>
  <div
    v-if="showModal && currentItem"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50"
  >
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="flex items-center justify-between p-4 border-b border-gray-200">
        <div class="flex items-center gap-2">
          <Heart :size="24" class="text-red-500" />
          <div>
            <h2 class="text-xl font-semibold text-gray-900">
              {{ currentItem.type === 'motivation' ? 'You have a motivation!' : 'You received a response!' }}
            </h2>
            <p class="text-sm text-gray-500">
              {{ currentIndex + 1 }} of {{ totalCount }}
            </p>
          </div>
        </div>
        <button
          @click="showModal = false"
          :disabled="isSubmitting"
          class="p-2 hover:bg-gray-100 rounded-full transition-colors disabled:opacity-50"
        >
          <X :size="20" />
        </button>
      </div>

      <!-- Body -->
      <div class="p-6 space-y-4">
        <!-- Motivation content -->
        <div v-if="currentItem.type === 'motivation'">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
              {{ currentItem.data.sender?.name?.charAt(0).toUpperCase() || '?' }}
            </div>
            <div>
              <p class="font-semibold text-gray-900">{{ currentItem.data.sender?.name || 'Unknown' }}</p>
              <p class="text-xs text-gray-500">{{ new Date(currentItem.data.created_at).toLocaleString('en-US') }}</p>
            </div>
          </div>

          <!-- Image if exists -->
          <div v-if="currentItem.data.image_path" class="mb-4">
            <img
              :src="`/storage/${currentItem.data.image_path}`"
              alt="Motivation image"
              class="w-full rounded-lg border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity"
              style="max-height: 250px; object-fit: contain;"
              @click="showImageViewer = true"
            />
            <div class="mt-1 text-xs text-gray-500 text-center">
              Click on image to enlarge
            </div>
          </div>

          <!-- Message -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-gray-800 whitespace-pre-wrap">{{ currentItem.data.message }}</p>
          </div>

          <!-- Reply section -->
          <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Reply (optional)
            </label>
            <textarea
              v-model="replyMessage"
              rows="3"
              :disabled="isSubmitting"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:bg-gray-50"
              placeholder="Write a thank you message..."
              maxlength="1000"
            ></textarea>
            <div class="mt-1 text-xs text-gray-500 text-right">
              {{ replyMessage.length }} / 1000
            </div>
          </div>
        </div>

        <!-- Response content (when someone replied to your motivation) -->
        <div v-else-if="currentItem.type === 'response'">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center text-white font-semibold">
              {{ currentItem.data.receiver?.name?.charAt(0).toUpperCase() || '?' }}
            </div>
            <div>
              <p class="font-semibold text-gray-900">{{ currentItem.data.receiver?.name || 'Unknown' }}</p>
              <p class="text-xs text-gray-500">replied to your motivation</p>
            </div>
          </div>

          <!-- Your original message -->
          <div class="mb-4">
            <p class="text-xs font-medium text-gray-500 mb-2">Your original message:</p>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
              <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ currentItem.data.message }}</p>
            </div>
          </div>

          <!-- Their response -->
          <div>
            <p class="text-xs font-medium text-gray-500 mb-2">Their response:</p>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
              <p class="text-gray-800 whitespace-pre-wrap">{{ currentItem.data.receiver_message }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex items-center justify-between p-4 border-t border-gray-200">
        <!-- Navigation -->
        <div class="flex items-center gap-2">
          <button
            @click="goToPrevious"
            :disabled="currentIndex === 0 || isSubmitting"
            class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <ChevronLeft :size="20" />
          </button>
          <button
            @click="goToNext"
            :disabled="currentIndex >= totalCount - 1 || isSubmitting"
            class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <ChevronRight :size="20" />
          </button>
        </div>

        <!-- Action buttons -->
        <div class="flex items-center gap-3">
          <button
            @click="closeWithoutReply"
            :disabled="isSubmitting"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors disabled:opacity-50 flex items-center gap-2"
          >
            <X :size="16" />
            {{ currentItem.type === 'response' ? 'Close' : 'Close without replying' }}
          </button>
          <button
            v-if="currentItem.type === 'motivation'"
            @click="closeWithReply"
            :disabled="isSubmitting || !replyMessage.trim()"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
          >
            <Send :size="16" />
            {{ isSubmitting ? 'Sending...' : 'Reply and close' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Image Viewer Modal -->
    <div
      v-if="showImageViewer && currentItem && currentItem.type === 'motivation' && currentItem.data.image_path"
      class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black bg-opacity-90"
      @click="showImageViewer = false"
    >
      <button
        @click="showImageViewer = false"
        class="absolute top-4 right-4 p-2 bg-white rounded-full hover:bg-gray-100 transition-colors z-10"
      >
        <X :size="24" />
      </button>
      <img
        :src="`/storage/${currentItem.data.image_path}`"
        alt="Motivation image"
        class="max-w-full max-h-full rounded-lg"
        @click.stop
      />
    </div>
  </div>
</template>

