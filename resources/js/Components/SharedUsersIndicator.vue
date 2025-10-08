<script setup>
import { Users } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const currentUserId = computed(() => page.props.auth.user.id);

const props = defineProps({
  sharedWith: {
    type: Array,
    required: true,
  },
  excludeCurrentUser: {
    type: Boolean,
    default: false,
  },
});

const filteredUsers = computed(() => {
  if (props.excludeCurrentUser) {
    return props.sharedWith.filter(u => u.id !== currentUserId.value);
  }
  return props.sharedWith;
});

// Total count includes owner + shared users
const totalUserCount = computed(() => {
  return filteredUsers.value.length + 1; // +1 for the owner
});

const tooltipText = computed(() => {
  const users = filteredUsers.value.slice(0, 3).map(u => u.email).join(', ');
  const ellipsis = filteredUsers.value.length > 3 ? '...' : '';
  return users + ellipsis;
});
</script>

<template>
  <div
    v-if="filteredUsers.length > 0"
    class="flex items-center gap-1 bg-dark-secondary border border-dark-border text-text-primary px-2 py-1 rounded-modern cursor-default select-none"
    :title="tooltipText"
  >
    <Users :size="16" :stroke-width="2" />
    <span class="text-xs">{{ totalUserCount }}</span>
  </div>
</template>
