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

const tooltipText = computed(() => {
  const users = filteredUsers.value.slice(0, 3).map(u => u.email).join(', ');
  const ellipsis = filteredUsers.value.length > 3 ? '...' : '';
  return users + ellipsis;
});
</script>

<template>
  <div
    v-if="filteredUsers.length > 0"
    class="flex items-center gap-1 bg-gray-700 text-white px-1.5 py-0.5 cursor-default select-none"
    :title="tooltipText"
  >
    <Users :size="14" />
    <span class="text-xs">{{ filteredUsers.length }}</span>
  </div>
</template>
