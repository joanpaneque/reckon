<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import LinkButton from '@/Components/LinkButton.vue';
import Modal from '@/Components/Modal.vue';
import HabitCalendar from '@/Components/Habit/HabitCalendar.vue';
import SharedUsersIndicator from '@/Components/SharedUsersIndicator.vue';
import MotivationReceivePopup from '@/Components/Habit/MotivationReceivePopup.vue';
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
  habits: {
    type: Array,
    required: true,
  },
});

// Calendar state from HabitCalendar component
const selectedDate = ref(new Date());
const viewMode = ref('day');

// Handle date change from calendar
const handleDateChanged = (date) => {
  selectedDate.value = date instanceof Date ? date : new Date(date);
};

// Handle view mode change from calendar
const handleViewModeChanged = (mode) => {
  viewMode.value = mode;
};

// Check if a habit is active (not expired) for a specific date
const isHabitActiveForDate = (habit, date) => {
  const habitStart = new Date(habit.start_date);
  const habitEnd = new Date(habit.end_date);
  const checkDate = new Date(date);

  // Normalize dates to compare only date part (ignore time)
  habitStart.setHours(0, 0, 0, 0);
  habitEnd.setHours(0, 0, 0, 0);
  checkDate.setHours(0, 0, 0, 0);

  // Check if date is within habit range (not expired)
  return checkDate >= habitStart && checkDate <= habitEnd;
};

// Filter habits based on selected date when in day view
const filteredHabits = computed(() => {
  // If in day view, show all habits that haven't expired for the selected date
  if (viewMode.value === 'day') {
    return props.habits.filter(habit => isHabitActiveForDate(habit, selectedDate.value));
  }
  // For week and month views, show all habits
  return props.habits;
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

const formatFrequency = (habit) => {
  if (habit.frequency === 'custom' && habit.selected_days && habit.selected_days.length > 0) {
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    // Sort with Monday first (1-6, then 0)
    const sortedDays = habit.selected_days.sort((a, b) => {
      if (a === 0) return 1; // Sunday goes to end
      if (b === 0) return -1; // Sunday goes to end
      return a - b;
    });
    const selectedDayNames = sortedDays
      .map(day => dayNames[day])
      .join(', ');
    return `Custom (${selectedDayNames})`;
  }
  return habit.frequency.charAt(0).toUpperCase() + habit.frequency.slice(1);
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
          <HabitCalendar 
            :habits="habits" 
            @date-changed="handleDateChanged"
            @view-mode-changed="handleViewModeChanged"
          />
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

      <!-- List View - Mobile -->
      <Card title="All Habits" class="md:hidden">
        <div v-if="filteredHabits.length === 0" class="text-center py-8 text-text-secondary">
          No habits found. Create your first habit to get started!
        </div>
        <div v-else class="space-y-3">
          <div v-for="habit in filteredHabits" :key="habit.id" class="border border-dark-border rounded-modern p-3">
            <div class="flex items-start justify-between gap-2 mb-2">
              <LinkButton prefetch :href="route('habits.show', habit.id)" variant="secondary" class="flex-1 text-left">
                {{ habit.name }}
              </LinkButton>
              <div class="flex items-center gap-1 flex-shrink-0">
                <LinkButton prefetch :href="route('habits.edit', habit.id)" variant="secondary" size="sm">
                  Edit
                </LinkButton>
                <LinkButton @click="confirmDelete(habit.id, habit.name)" variant="danger" size="sm">
                  Delete
                </LinkButton>
              </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap mb-1">
              <span
                :style="getHabitStyle(habit)"
                class="px-2 py-1 text-xs font-medium rounded-full"
              >
                {{ formatFrequency(habit) }}
              </span>
              <span class="text-xs text-text-secondary">
                {{ formatDate(habit.start_date) }} - {{ formatDate(habit.end_date) }}
              </span>
              <SharedUsersIndicator v-if="habit.shared_with" :shared-with="habit.shared_with" />
            </div>
            <div v-if="habit.description" class="text-xs text-text-secondary mt-1">
              {{ habit.description }}
            </div>
          </div>
        </div>
      </Card>

      <!-- List View - Desktop -->
      <Card title="All Habits" class="hidden md:block">
        <div v-if="filteredHabits.length === 0" class="text-center py-8 text-text-secondary">
          No habits found. Create your first habit to get started!
        </div>
        <div v-else class="space-y-4">
          <div v-for="habit in filteredHabits" :key="habit.id" class="flex items-center justify-between py-3 border-b border-dark-border last:border-b-0">
            <div class="flex-1">
              <LinkButton prefetch :href="route('habits.show', habit.id)" variant="secondary">
                {{ habit.name }}
              </LinkButton>
              <div class="mt-1 flex items-center gap-3">
                <span
                  :style="getHabitStyle(habit)"
                  class="px-2 py-1 text-xs font-medium rounded-full"
                >
                  {{ formatFrequency(habit) }}
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
