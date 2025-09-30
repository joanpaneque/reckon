<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import Button from '@/Components/Button.vue';
import LinkButton from '@/Components/LinkButton.vue';
import { computed } from 'vue';

const props = defineProps({
  friendship: {
    type: Object,
    required: true,
  },
});

const form = useForm({
  status: props.friendship.status,
});

const accept = () => {
  form.status = 'accepted';
  form.patch(route('friends.update', props.friendship.id));
};

const reject = () => {
  form.status = 'rejected';
  form.patch(route('friends.update', props.friendship.id));
};

// Helper to get the friend's name
const friendName = computed(() => {
  return props.friendship.sender?.name || props.friendship.sender?.email || 'Unknown';
});
</script>

<template>
  <AppLayout title="Respond to friend request">
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-medium text-gray-900">Respond to friend request</h1>
        <LinkButton :href="route('friends.index')" variant="secondary">
          Back to list
        </LinkButton>
      </div>

      <Card>
        <div class="space-y-6">
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Friend request</h3>
            <p class="text-gray-600">
              <span class="font-medium">{{ friendName }}</span> wants to be your friend.
            </p>
            <div class="mt-2">
              <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                {{ friendship.status }}
              </span>
            </div>
          </div>

          <div v-if="friendship.status === 'pending'" class="flex gap-3">
            <Button
              @click="accept"
              :disabled="form.processing"
              variant="primary"
            >
              Accept
            </Button>
            <Button
              @click="reject"
              :disabled="form.processing"
              variant="danger"
            >
              Reject
            </Button>
          </div>

          <div v-else class="text-sm text-gray-500">
            This friend request has already been responded to.
          </div>
        </div>
      </Card>
    </div>
  </AppLayout>
</template>
