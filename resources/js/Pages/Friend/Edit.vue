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
        <h1 class="text-h2 font-bold text-text-primary tracking-wide-modern">Respond to friend request</h1>
        <LinkButton prefetch :href="route('friends.index')" variant="secondary">
          Back to list
        </LinkButton>
      </div>

      <Card>
        <div class="space-y-6">
          <div>
            <h3 class="text-lg font-semibold text-text-primary mb-2">Friend request</h3>
            <p class="text-text-secondary">
              <span class="font-medium text-text-primary">{{ friendName }}</span> wants to be your friend.
            </p>
            <div class="mt-2">
              <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-500/20 text-yellow-400">
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

          <div v-else class="text-sm text-text-secondary">
            This friend request has already been responded to.
          </div>
        </div>
      </Card>
    </div>
  </AppLayout>
</template>
