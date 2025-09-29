<script setup>
import { computed } from 'vue';

const props = defineProps({
  startedAt: {
    type: String,
    required: true,
  },
  endedAt: {
    type: String,
    required: true,
  },
});

const duration = computed(() => {
  const start = new Date(props.startedAt);
  const end = new Date(props.endedAt);
  const diff = end - start;

  const days = Math.floor(diff / (1000 * 60 * 60 * 24));
  const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
  const seconds = Math.floor((diff % (1000 * 60)) / 1000);

  return `${days}:${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
});
</script>

<template>
  <span class="font-mono text-sm text-gray-900">{{ duration }}</span>
</template>
