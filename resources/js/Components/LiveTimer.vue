<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  startedAt: {
    type: String,
    required: true,
  },
});

const elapsed = ref('0:00:00:00');
let interval = null;

const updateTimer = () => {
  const start = new Date(props.startedAt);
  const now = new Date();
  const diff = now - start;

  const days = Math.floor(diff / (1000 * 60 * 60 * 24));
  const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
  const seconds = Math.floor((diff % (1000 * 60)) / 1000);

  elapsed.value = `${days}:${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
};

onMounted(() => {
  updateTimer();
  interval = setInterval(updateTimer, 1000);
});

onUnmounted(() => {
  if (interval) {
    clearInterval(interval);
  }
});
</script>

<template>
  <span class="font-mono text-sm text-blue-600">{{ elapsed }}</span>
</template>
