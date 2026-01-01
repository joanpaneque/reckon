<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import HabitForm from '@/Components/Habit/HabitForm.vue';
import HabitHeader from '@/Components/Habit/HabitHeader.vue';

const props = defineProps({
  habit: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <AppLayout title="Edit habit">
    <div class="space-y-6">
      <HabitHeader
        title="Edit habit"
        :back-route="route('habits.index')"
      />

      <Card>
        <HabitForm
          :habit="habit"
          :submit-route="route('habits.update', habit.id)"
          method="patch"
          submit-label="Update habit"
        />
      </Card>
    </div>
  </AppLayout>
</template>




