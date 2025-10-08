<script setup>
import { computed } from 'vue';

const props = defineProps({
  totalSeconds: {
    type: Number,
    required: true,
  },
  totalCost: {
    type: Number,
    required: true,
  },
  compact: {
    type: Boolean,
    default: false,
  },
});

const formattedTime = computed(() => {
  if (props.totalSeconds === 0) return '0:00:00:00';

  const days = Math.floor(props.totalSeconds / 86400);
  const hours = Math.floor((props.totalSeconds % 86400) / 3600);
  const minutes = Math.floor((props.totalSeconds % 3600) / 60);
  const seconds = Math.floor(props.totalSeconds % 60);

  return `${days}:${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
});

const formattedCost = computed(() => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'EUR',
  }).format(props.totalCost);
});
</script>

<template>
  <div v-if="compact" class="text-sm text-text-secondary">
    <span class="font-mono">{{ formattedTime }}</span> • {{ formattedCost }}
  </div>
  <div v-else class="space-y-2">
    <div>
      <span class="text-sm text-text-secondary">Time: </span>
      <span class="font-mono text-text-primary">{{ formattedTime }}</span>
    </div>
    <div>
      <span class="text-sm text-text-secondary">Cost: </span>
      <span class="font-semibold text-text-primary">{{ formattedCost }}</span>
    </div>
  </div>
</template>



