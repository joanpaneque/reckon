<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import LinkButton from '@/Components/LinkButton.vue';
import Modal from '@/Components/Modal.vue';
import HabitCalendar from '@/Components/Habit/HabitCalendar.vue';
import SharedUsersIndicator from '@/Components/SharedUsersIndicator.vue';
import MotivationReceivePopup from '@/Components/Habit/MotivationReceivePopup.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  habits: {
    type: Array,
    required: true,
  },
});

// Modal state
const showDeleteModal = ref(false);
const habitToDelete = ref(null);

const confirmDelete = (habitId, habitName) => {
  habitToDelete.value = { id: habitId, name: habitName };
  showDeleteModal.value = true;
};

const handleDeleteConfirm = () => {
  if (habitToDelete.value) {
    router.delete(route('habits.destroy', habitToDelete.value.id));
    habitToDelete.value = null;
  }
  showDeleteModal.value = false;
};

const handleDeleteCancel = () => {
  habitToDelete.value = null;
  showDeleteModal.value = false;
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

const getHabitStyle = (habit) => {
  return {
    backgroundColor: habit.color || '#93C5FD',
    color: getContrastColor(habit.color || '#93C5FD'),
  };
};

const getContrastColor = (hexColor) => {
  const r = parseInt(hexColor.slice(1, 3), 16);
  const g = parseInt(hexColor.slice(3, 5), 16);
  const b = parseInt(hexColor.slice(5, 7), 16);
  const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
  return luminance > 0.5 ? '#000000' : '#FFFFFF';
};
</script>

<template>
  <AppLayout title="Habits">
    <div class="space-y-4 md:space-y-6">
      <!-- Header - Desktop only -->
      <div class="hidden md:flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <h1 class="text-xl md:text-h2 font-bold text-text-primary tracking-wide-modern">Habits</h1>
        <div class="flex gap-2 md:gap-3">
          <LinkButton prefetch :href="route('habits.statistics')" variant="secondary" size="sm" class="flex-1 md:flex-none">
            <span class="hidden md:inline">Statistics</span>
          </LinkButton>
          <LinkButton prefetch :href="route('shared-habits.index')" variant="secondary" size="sm" class="flex-1 md:flex-none">
            <span class="hidden md:inline">Shared Habits</span>
          </LinkButton>
          <LinkButton prefetch :href="route('habits.create')" variant="primary" size="sm" class="flex-1 md:flex-none">
            <span class="hidden md:inline">Create habit</span>
          </LinkButton>
        </div>
      </div>

      <!-- Calendar View -->
      <div class="-mx-4 md:mx-0">
        <Card class="!p-0 md:!p-6 md:rounded-modern rounded-none md:border border-none md:shadow-modern shadow-none">
          <HabitCalendar :habits="habits" />
        </Card>
      </div>

      <!-- Mobile buttons - Below calendar, mobile only -->
      <div class="md:hidden px-4 flex gap-2">
        <LinkButton prefetch :href="route('habits.statistics')" variant="secondary" size="sm" class="flex-1">
          Stats
        </LinkButton>
        <LinkButton prefetch :href="route('shared-habits.index')" variant="secondary" size="sm" class="flex-1">
          Shared
        </LinkButton>
        <LinkButton prefetch :href="route('habits.create')" variant="primary" size="sm" class="flex-1">
          Create
        </LinkButton>
      </div>

      <!-- List View - Only show on desktop -->
      <Card title="All Habits" class="hidden md:block">
        <div v-if="habits.length === 0" class="text-center py-8 text-text-secondary">
          No habits found. Create your first habit to get started!
        </div>
        <div v-else class="space-y-4">
          <div v-for="habit in habits" :key="habit.id" class="flex items-center justify-between py-3 border-b border-dark-border last:border-b-0">
            <div class="flex-1">
              <LinkButton prefetch :href="route('habits.show', habit.id)" variant="secondary">
                {{ habit.name }}
              </LinkButton>
              <div class="mt-1 flex items-center gap-3">
                <span
                  :style="getHabitStyle(habit)"
                  class="px-2 py-1 text-xs font-medium rounded-full"
                >
                  {{ habit.frequency }}
                </span>
                <span class="text-sm text-text-secondary">
                  {{ formatDate(habit.start_date) }} - {{ formatDate(habit.end_date) }}
                </span>
                <SharedUsersIndicator v-if="habit.shared_with" :shared-with="habit.shared_with" />
              </div>
              <div v-if="habit.description" class="mt-1 text-sm text-text-secondary">
                {{ habit.description }}
              </div>
            </div>
            <div class="flex items-center gap-2">
              <LinkButton prefetch :href="route('habits.edit', habit.id)" variant="secondary" size="sm">
                Edit
              </LinkButton>
              <LinkButton @click="confirmDelete(habit.id, habit.name)" variant="danger" size="sm">
                Delete
              </LinkButton>
            </div>
          </div>
        </div>
      </Card>
    </div>

    <!-- Delete Confirmation Modal -->
    <Modal
      :show="showDeleteModal"
      title="Delete Habit"
      :message="`Are you sure you want to delete this habit?${habitToDelete ? ' (' + habitToDelete.name + ')' : ''}`"
      confirm-text="Delete"
      cancel-text="Cancel"
      confirm-variant="danger"
      @confirm="handleDeleteConfirm"
      @cancel="handleDeleteCancel"
      @close="showDeleteModal = false"
    />

    <!-- Motivation Receive Popup -->
    <MotivationReceivePopup />
  </AppLayout>
</template>
