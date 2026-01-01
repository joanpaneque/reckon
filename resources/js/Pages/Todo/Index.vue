<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import LinkButton from '@/Components/LinkButton.vue';
import TodoCalendar from '@/Components/Todo/TodoCalendar.vue';
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
  todos: {
    type: Array,
    default: () => [],
  },
});

// Calendar state from TodoCalendar component
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

// Get the target date for adding todos (computed for reactivity)
const targetDateForAdd = computed(() => {
  const now = new Date();
  const currentHour = now.getHours();
  
  // Calculate if it's before 2 AM (strictly before 02:00:00)
  const isBefore2AM = currentHour < 2;
  
  // Get today's date at midnight
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
  
  // Get selected date at midnight
  const selected = new Date(selectedDate.value);
  const selectedDay = new Date(selected.getFullYear(), selected.getMonth(), selected.getDate());
  
  // Check if viewing today
  const isViewingToday = selectedDay.getTime() === today.getTime();
  
  // Check if viewing a future date
  const isViewingFuture = selectedDay > today;
  
  // If viewing today and it's before 2 AM, allow adding for today
  if (isViewingToday && isBefore2AM) {
    const todayString = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
    return todayString;
  }
  
  // If viewing a future date, we can add todos for that same date
  // (since it hasn't reached 2 AM of that day yet)
  if (isViewingFuture) {
    const selectedDayString = `${selectedDay.getFullYear()}-${String(selectedDay.getMonth() + 1).padStart(2, '0')}-${String(selectedDay.getDate()).padStart(2, '0')}`;
    return selectedDayString;
  }
  
  // If viewing today after 2 AM, add for tomorrow
  const tomorrow = new Date(today);
  tomorrow.setDate(tomorrow.getDate() + 1);
  const tomorrowString = `${tomorrow.getFullYear()}-${String(tomorrow.getMonth() + 1).padStart(2, '0')}-${String(tomorrow.getDate()).padStart(2, '0')}`;
  return tomorrowString;
});

// Check if we can add todos for the target date
// The button should always show when viewing a day (except past dates that can't be edited)
const canAddTodosForTargetDate = computed(() => {
  try {
    const targetDateString = targetDateForAdd.value;
    if (!targetDateString) return false;
    
    const targetDate = new Date(targetDateString + 'T00:00:00');
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    
    // If target date is in the future, always allow
    if (targetDate > today) {
      return true;
    }
    
    // If target date is today, check if it's before 2 AM
    if (targetDate.getTime() === today.getTime()) {
      return now.getHours() < 2;
    }
    
    // If target date is in the past, don't allow
    return false;
  } catch (e) {
    console.error('Error in canAddTodosForTargetDate:', e);
    return false;
  }
});

// Get button label for adding todos
const getAddButtonLabel = computed(() => {
  const targetDateString = targetDateForAdd.value;
  
  // Parse the date string correctly (format: YYYY-MM-DD)
  const [year, month, day] = targetDateString.split('-').map(Number);
  const targetDate = new Date(year, month - 1, day);
  
  const now = new Date();
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
  
  const tomorrow = new Date(today);
  tomorrow.setDate(tomorrow.getDate() + 1);
  
  // Normalize all dates to start of day for comparison
  targetDate.setHours(0, 0, 0, 0);
  today.setHours(0, 0, 0, 0);
  tomorrow.setHours(0, 0, 0, 0);
  
  // Compare dates correctly
  // Always compare against "today" (current day) to determine if it's "hoy" or "mañana"
  if (targetDate.getTime() === today.getTime()) {
    return "Añadir todo's para hoy";
  }
  
  if (targetDate.getTime() === tomorrow.getTime()) {
    return "Añadir todo's para mañana";
  }
  
  // For any other future date, show the specific date
  return "Añadir todo's para " + targetDate.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
});

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-ES');
};
</script>

<template>
  <AppLayout title="To do's">
    <div class="space-y-4 md:space-y-6">
      <!-- Header - Desktop only -->
      <div class="hidden md:flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <h1 class="text-xl md:text-h2 font-bold text-text-primary tracking-wide-modern">To do's</h1>
      </div>

      <!-- Calendar View -->
      <div class="-mx-4 md:mx-0">
        <Card class="!p-0 md:!p-6 md:rounded-modern rounded-none md:border border-none md:shadow-modern shadow-none">
          <TodoCalendar :todos="todos" @date-changed="handleDateChanged" @view-mode-changed="handleViewModeChanged" />
        </Card>
      </div>

      <!-- Add Todos Button - Show when in day view -->
      <!-- Always show the button; backend will validate if todos can be added for that date -->
      <div v-if="viewMode === 'day'" class="px-4 md:px-0">
        <LinkButton 
          :href="route('todos.create', { date: targetDateForAdd })" 
          variant="primary" 
          class="w-full md:w-auto"
        >
          {{ getAddButtonLabel }}
        </LinkButton>
      </div>
    </div>
  </AppLayout>
</template>

