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
  hourPrice: {
    type: Number,
    required: true,
  },
});

const formattedTime = computed(() => {
  const days = Math.floor(props.totalSeconds / 86400);
  const hours = Math.floor((props.totalSeconds % 86400) / 3600);
  const minutes = Math.floor((props.totalSeconds % 3600) / 60);
  const seconds = Math.floor(props.totalSeconds % 60);

  return `${days}:${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
});

const formattedCost = computed(() => {
  return new Intl.NumberFormat('es-ES', {
    style: 'currency',
    currency: 'EUR',
  }).format(props.totalCost);
});
</script>

<template>
  <div class="card-modern">
    <h3 class="text-lg font-semibold text-text-primary mb-3">Project summary</h3>
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-text-secondary">Total time completed</label>
        <p class="mt-1 text-lg font-mono text-text-primary">{{ formattedTime }}</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-text-secondary">Total cost ({{ hourPrice }}€/hour)</label>
        <p class="mt-1 text-lg font-semibold text-accent">{{ formattedCost }}</p>
      </div>
    </div>
  </div>
</template>
