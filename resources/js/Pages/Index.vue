<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const greeting = computed(() => {
  const now = new Date();
  const hour = now.getHours();

  if (hour >= 0 && hour < 6) {
    return 'Hello';
  } else if (hour >= 6 && hour < 12) {
    return 'Good morning';
  } else if (hour >= 12 && hour < 18) {
    return 'Good afternoon';
  } else if (hour >= 18 && hour < 21) {
    return 'Good evening';
  } else {
    return 'Good night';
  }
});

const userName = computed(() => {
  const user = page.props.auth.user;
  return user?.name || user?.email?.split('@')[0] || 'User';
});
</script>

<template>
  <AppLayout title="Dashboard">
    <div class="flex items-center justify-center min-h-[60vh]">
      <div class="text-center">
        <h1 class="text-h1 font-bold text-text-primary tracking-wide-modern mb-2">
          {{ greeting }}, {{ userName }}
        </h1>
        <p class="text-base text-text-secondary">
          Welcome back
        </p>
      </div>
    </div>
  </AppLayout>
</template>
