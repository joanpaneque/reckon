<script setup>
defineProps({
  title: String,
});
</script>

<template>
  <div class="card-modern">
    <h2 v-if="title" class="text-lg font-semibold text-text-primary mb-4">{{ title }}</h2>
    <slot />
  </div>
</template>
