<script setup>
defineProps({
  title: String,
});
</script>

<template>
  <div class="bg-white border border-gray-200 p-6">
    <h2 v-if="title" class="text-lg font-medium text-gray-900 mb-4">{{ title }}</h2>
    <slot />
  </div>
</template>
