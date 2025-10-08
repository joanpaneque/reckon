<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import LinkButton from '@/Components/LinkButton.vue';
import Modal from '@/Components/Modal.vue';
import { router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
  incomingAndFriends: {
    type: Object,
    required: true,
  },
});

const page = usePage();
const authUserId = computed(() => page.props.auth.user?.id);

// Modal state
const showDeleteModal = ref(false);
const friendshipToDelete = ref(null);

const confirmDelete = (friendshipId, friendName) => {
  friendshipToDelete.value = { id: friendshipId, name: friendName };
  showDeleteModal.value = true;
};

const handleDeleteConfirm = () => {
  if (friendshipToDelete.value) {
    router.delete(route('friends.destroy', friendshipToDelete.value.id));
    friendshipToDelete.value = null;
  }
  showDeleteModal.value = false;
};

const handleDeleteCancel = () => {
  friendshipToDelete.value = null;
  showDeleteModal.value = false;
};

// Helper to get the friend's name based on current user
const getFriendInfo = (friendship) => {
  // Si el usuario autenticado es el sender, mostrar el receiver
  if (friendship.sender_id === authUserId.value) {
    return friendship.receiver?.name || friendship.receiver?.email || 'Unknown';
  }
  // Si el usuario autenticado es el receiver, mostrar el sender
  else {
    return friendship.sender?.name || friendship.sender?.email || 'Unknown';
  }
};

// Helper to get status badge class
const getStatusBadgeClass = (status) => {
  if (status === 'accepted') return 'bg-green-500/20 text-green-400';
  if (status === 'pending') return 'bg-yellow-500/20 text-yellow-400';
  if (status === 'rejected') return 'bg-red-500/20 text-red-400';
  return 'bg-text-tertiary/20 text-text-tertiary';
};

// Helper to get status text
const getStatusText = (status) => {
  if (status === 'accepted') return 'Friend';
  return status.charAt(0).toUpperCase() + status.slice(1);
};
</script>

<template>
  <AppLayout title="Friends">
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-h2 font-bold text-text-primary tracking-wide-modern">Friends</h1>
        <LinkButton prefetch :href="route('friends.create')" variant="primary">
          Add friend
        </LinkButton>
      </div>

      <Card>
        <div class="space-y-4">
          <div v-for="friendship in incomingAndFriends.data" :key="friendship.id" class="flex items-center justify-between py-3 border-b border-dark-border last:border-b-0">
            <div class="flex-1 flex items-center gap-3">
              <LinkButton prefetch :href="route('friends.show', friendship.id)" variant="secondary">
                {{ getFriendInfo(friendship) }}
              </LinkButton>
              <span
                class="px-2 py-1 text-xs font-medium rounded-full"
                :class="getStatusBadgeClass(friendship.status)"
              >
                {{ getStatusText(friendship.status) }}
              </span>
            </div>
            <div class="flex items-center gap-2">
              <LinkButton
                v-if="friendship.status === 'pending'"
                prefetch :href="route('friends.edit', friendship.id)"
                variant="primary"
                size="sm"
              >
                Respond
              </LinkButton>
              <LinkButton @click="confirmDelete(friendship.id, getFriendInfo(friendship))" variant="danger" size="sm">
                Delete
              </LinkButton>
            </div>
          </div>

          <div v-if="incomingAndFriends.data.length === 0" class="text-center py-8 text-text-secondary">
            No friends yet. Add some friends to get started!
          </div>
        </div>

        <div v-if="incomingAndFriends.links" class="flex items-center justify-center gap-1 mt-6 pt-4 border-t border-dark-border">
          <template v-for="link in incomingAndFriends.links" :key="link.label">
            <LinkButton
              v-if="link.url"
              :href="link.url"
              :variant="link.active ? 'primary' : 'secondary'"
              size="sm"
              v-html="link.label"
            />
            <span v-else class="px-3 py-1 text-sm text-text-tertiary" v-html="link.label" />
          </template>
        </div>
      </Card>
    </div>

    <!-- Delete Confirmation Modal -->
    <Modal
      :show="showDeleteModal"
      title="Delete Friendship"
      :message="`Are you sure you want to delete this friendship?${friendshipToDelete ? ' (' + friendshipToDelete.name + ')' : ''}`"
      confirm-text="Delete"
      cancel-text="Cancel"
      confirm-variant="danger"
      @confirm="handleDeleteConfirm"
      @cancel="handleDeleteCancel"
      @close="showDeleteModal = false"
    />
  </AppLayout>
</template>
