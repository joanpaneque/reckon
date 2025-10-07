<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import LinkButton from '@/Components/LinkButton.vue';
import { computed, ref } from 'vue';
import { Heart, MessageSquare, X } from 'lucide-vue-next';

const props = defineProps({
  friendship: {
    type: Object,
    required: true,
  },
  motivationsReceived: {
    type: Array,
    default: () => [],
  },
  motivationsSent: {
    type: Array,
    default: () => [],
  },
  friendId: {
    type: Number,
    required: true,
  },
});

// Helper to get status badge class
const getStatusBadgeClass = (status) => {
  if (status === 'accepted') return 'bg-green-100 text-green-800';
  if (status === 'pending') return 'bg-yellow-100 text-yellow-800';
  if (status === 'rejected') return 'bg-red-100 text-red-800';
  return 'bg-gray-100 text-gray-800';
};

const senderName = computed(() => {
  return props.friendship.sender?.name || props.friendship.sender?.email || 'Unknown';
});

const receiverName = computed(() => {
  return props.friendship.receiver?.name || props.friendship.receiver?.email || 'Unknown';
});

const friendName = computed(() => {
  const friend = props.friendship.sender_id === props.friendId 
    ? props.friendship.sender 
    : props.friendship.receiver;
  return friend?.name || friend?.email || 'Unknown';
});

// Image viewer state
const showImageViewer = ref(false);
const viewerImage = ref(null);

const openImageViewer = (imagePath) => {
  viewerImage.value = imagePath;
  showImageViewer.value = true;
};

const closeImageViewer = () => {
  showImageViewer.value = false;
  viewerImage.value = null;
};
</script>

<template>
  <AppLayout title="Friendship details">
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-medium text-gray-900">Friendship details</h1>
        <LinkButton prefetch :href="route('friends.index')" variant="secondary">
          Back to list
        </LinkButton>
      </div>

      <Card>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <span
              class="inline-block px-3 py-1 text-sm font-medium rounded-full"
              :class="getStatusBadgeClass(friendship.status)"
            >
              {{ friendship.status }}
            </span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
            <p class="text-gray-900">{{ senderName }}</p>
            <p class="text-sm text-gray-500">{{ friendship.sender?.email }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
            <p class="text-gray-900">{{ receiverName }}</p>
            <p class="text-sm text-gray-500">{{ friendship.receiver?.email }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Created</label>
            <p class="text-gray-900">{{ new Date(friendship.created_at).toLocaleString() }}</p>
          </div>

          <div v-if="friendship.updated_at !== friendship.created_at">
            <label class="block text-sm font-medium text-gray-700 mb-1">Last Updated</label>
            <p class="text-gray-900">{{ new Date(friendship.updated_at).toLocaleString() }}</p>
          </div>
        </div>

        <div class="flex gap-2 mt-6 pt-4 border-t border-gray-100">
          <LinkButton
            prefetch
            v-if="friendship.status === 'pending'"
            :href="route('friends.edit', friendship.id)"
            variant="primary"
          >
            Respond
          </LinkButton>
          <LinkButton
            prefetch
            :href="route('friends.index')"
            variant="secondary"
          >
            Back to list
          </LinkButton>
        </div>
      </Card>

      <!-- Motivations Received -->
      <Card>
        <template #header>
          <div class="flex items-center gap-2">
            <Heart :size="20" class="text-red-500" />
            <h2 class="text-lg font-semibold text-gray-900">
              Motivations received from {{ friendName }}
            </h2>
          </div>
        </template>

        <div v-if="motivationsReceived.length === 0" class="text-center py-8 text-gray-500">
          You haven't received any motivations from this friend yet
        </div>

        <div v-else class="space-y-4">
          <div
            v-for="motivation in motivationsReceived"
            :key="motivation.id"
            class="p-4 border border-gray-200 rounded-lg hover:border-gray-300 transition-colors"
          >
            <div class="flex items-start justify-between mb-3">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-gradient-to-br from-red-400 to-red-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                  {{ motivation.sender?.name?.charAt(0).toUpperCase() || '?' }}
                </div>
            <div>
              <p class="font-medium text-gray-900">{{ motivation.sender?.name || 'Unknown' }}</p>
              <p class="text-xs text-gray-500">{{ new Date(motivation.created_at).toLocaleString('en-US') }}</p>
            </div>
              </div>
              <span
                v-if="motivation.receiver_closed"
                class="px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-800"
              >
                Read
              </span>
            </div>

            <!-- Image if exists -->
            <div v-if="motivation.image_path" class="mb-3">
              <img
                :src="`/storage/${motivation.image_path}`"
                alt="Motivation image"
                class="w-full rounded-lg border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity"
                style="max-height: 200px; object-fit: contain;"
                @click="openImageViewer(motivation.image_path)"
              />
            </div>

            <!-- Message -->
            <div class="bg-red-50 border border-red-200 rounded-lg p-3">
              <p class="text-gray-800 whitespace-pre-wrap">{{ motivation.message }}</p>
            </div>

            <!-- Your response if exists -->
            <div v-if="motivation.receiver_message" class="mt-3">
              <div class="flex items-center gap-1 mb-2">
                <MessageSquare :size="14" class="text-blue-500" />
                <p class="text-xs font-medium text-gray-600">Your response:</p>
              </div>
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ motivation.receiver_message }}</p>
              </div>
            </div>
          </div>
        </div>
      </Card>

      <!-- Motivations Sent -->
      <Card>
        <template #header>
          <div class="flex items-center gap-2">
            <Heart :size="20" class="text-blue-500" />
            <h2 class="text-lg font-semibold text-gray-900">
              Motivations sent to {{ friendName }}
            </h2>
          </div>
        </template>

        <div v-if="motivationsSent.length === 0" class="text-center py-8 text-gray-500">
          You haven't sent any motivations to this friend yet
        </div>

        <div v-else class="space-y-4">
          <div
            v-for="motivation in motivationsSent"
            :key="motivation.id"
            class="p-4 border border-gray-200 rounded-lg hover:border-gray-300 transition-colors"
          >
            <div class="flex items-start justify-between mb-3">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                  {{ motivation.receiver?.name?.charAt(0).toUpperCase() || '?' }}
                </div>
                <div>
                  <p class="font-medium text-gray-900">To: {{ motivation.receiver?.name || 'Unknown' }}</p>
                  <p class="text-xs text-gray-500">{{ new Date(motivation.created_at).toLocaleString('en-US') }}</p>
                </div>
              </div>
              <span
                v-if="motivation.receiver_closed"
                class="px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-800"
              >
                Read
              </span>
            </div>

            <!-- Image if exists -->
            <div v-if="motivation.image_path" class="mb-3">
              <img
                :src="`/storage/${motivation.image_path}`"
                alt="Motivation image"
                class="w-full rounded-lg border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity"
                style="max-height: 200px; object-fit: contain;"
                @click="openImageViewer(motivation.image_path)"
              />
            </div>

            <!-- Message -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
              <p class="text-gray-800 whitespace-pre-wrap">{{ motivation.message }}</p>
            </div>

            <!-- Their response if exists -->
            <div v-if="motivation.receiver_message" class="mt-3">
              <div class="flex items-center gap-1 mb-2">
                <MessageSquare :size="14" class="text-green-500" />
                <p class="text-xs font-medium text-gray-600">{{ friendName }}'s response:</p>
              </div>
              <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ motivation.receiver_message }}</p>
              </div>
            </div>
          </div>
        </div>
      </Card>
    </div>

    <!-- Image Viewer Modal -->
    <div
      v-if="showImageViewer && viewerImage"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-90"
      @click="closeImageViewer"
    >
      <button
        @click="closeImageViewer"
        class="absolute top-4 right-4 p-2 bg-white rounded-full hover:bg-gray-100 transition-colors z-10"
      >
        <X :size="24" />
      </button>
      <img
        :src="`/storage/${viewerImage}`"
        alt="Motivation image"
        class="max-w-full max-h-full rounded-lg"
        @click.stop
      />
    </div>
  </AppLayout>
</template>
