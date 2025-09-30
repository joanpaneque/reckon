<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import LinkButton from '@/Components/LinkButton.vue';
import { computed } from 'vue';

const props = defineProps({
  friendship: {
    type: Object,
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
</script>

<template>
  <AppLayout title="Friendship details">
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-medium text-gray-900">Friendship details</h1>
        <LinkButton :href="route('friends.index')" variant="secondary">
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
            v-if="friendship.status === 'pending'"
            :href="route('friends.edit', friendship.id)"
            variant="primary"
          >
            Respond
          </LinkButton>
          <LinkButton
            :href="route('friends.index')"
            variant="secondary"
          >
            Back to list
          </LinkButton>
        </div>
      </Card>
    </div>
  </AppLayout>
</template>
