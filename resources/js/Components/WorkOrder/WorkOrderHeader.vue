<script setup>
import LinkButton from '@/Components/LinkButton.vue';

defineProps({
  title: {
    type: String,
    required: true,
  },
  backRoute: {
    type: String,
    default: null,
  },
  backLabel: {
    type: String,
    default: 'Back to list',
  },
});
</script>

<template>
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-medium text-gray-900">{{ title }}</h1>
    <LinkButton prefetch v-if="backRoute" :href="backRoute" variant="secondary">
      {{ backLabel }}
    </LinkButton>
  </div>
</template>
