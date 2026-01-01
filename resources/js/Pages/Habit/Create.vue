<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import HabitForm from '@/Components/Habit/HabitForm.vue';
import HabitHeader from '@/Components/Habit/HabitHeader.vue';
</script>

<template>
  <AppLayout title="Create habit">
    <div class="space-y-6">
      <HabitHeader
        title="Create habit"
        :back-route="route('habits.index')"
      />

      <Card>
        <HabitForm
          :submit-route="route('habits.store')"
          method="post"
          submit-label="Create habit"
        />
      </Card>
    </div>
  </AppLayout>
</template>




