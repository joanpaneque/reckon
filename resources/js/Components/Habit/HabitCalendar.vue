<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import LinkButton from '@/Components/LinkButton.vue';
import SharedUsersIndicator from '@/Components/SharedUsersIndicator.vue';
import { ChevronLeft, ChevronRight, Check, Clock, X, Calendar } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
  habits: {
    type: Array,
    required: true,
  },
});

// Calendar state
const viewMode = ref('day'); // 'day', 'week', 'month'
const currentDate = ref(new Date());

// Countdown timer state
const currentTime = ref(new Date());
let countdownInterval = null;

// View mode buttons
const viewModes = [
  { value: 'day', label: 'Day' },
  { value: 'week', label: 'Week' },
  { value: 'month', label: 'Month' },
];

// Navigation
const goToPrevious = () => {
  const date = new Date(currentDate.value);
  if (viewMode.value === 'day') {
    date.setDate(date.getDate() - 1);
  } else if (viewMode.value === 'week') {
    date.setDate(date.getDate() - 7);
  } else if (viewMode.value === 'month') {
    date.setMonth(date.getMonth() - 1);
  }
  currentDate.value = date;
};

const goToNext = () => {
  const date = new Date(currentDate.value);
  if (viewMode.value === 'day') {
    date.setDate(date.getDate() + 1);
  } else if (viewMode.value === 'week') {
    date.setDate(date.getDate() + 7);
  } else if (viewMode.value === 'month') {
    date.setMonth(date.getMonth() + 1);
  }
  currentDate.value = date;
};

const goToToday = () => {
  currentDate.value = new Date();
};

// Date formatting
const formatDateHeader = computed(() => {
  const date = currentDate.value;
  if (viewMode.value === 'day') {
    return date.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
  } else if (viewMode.value === 'week') {
    const startOfWeek = getStartOfWeek(date);
    const endOfWeek = new Date(startOfWeek);
    endOfWeek.setDate(endOfWeek.getDate() + 6);
    return `${startOfWeek.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${endOfWeek.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}`;
  } else {
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long' });
  }
});

// Helper functions
const getStartOfWeek = (date) => {
  const d = new Date(date);
  const day = d.getDay();
  const diff = d.getDate() - day + (day === 0 ? -6 : 1); // Adjust when day is Sunday
  return new Date(d.setDate(diff));
};

const getStartOfMonth = (date) => {
  return new Date(date.getFullYear(), date.getMonth(), 1);
};

const getDaysInMonth = (date) => {
  return new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
};

// Check if habit should be shown on a specific date
const isHabitActiveOnDate = (habit, date) => {
  const habitStart = new Date(habit.start_date);
  const habitEnd = new Date(habit.end_date);
  const checkDate = new Date(date);
  
  // Normalize dates to compare only date part (ignore time)
  habitStart.setHours(0, 0, 0, 0);
  habitEnd.setHours(0, 0, 0, 0);
  checkDate.setHours(0, 0, 0, 0);
  
  // Check if date is within habit range
  if (checkDate < habitStart || checkDate > habitEnd) {
    return false;
  }
  
  // Check frequency
  const dayOfWeek = checkDate.getDay(); // 0 = Sunday, 6 = Saturday
  
  if (habit.frequency === 'everyday') {
    return true;
  } else if (habit.frequency === 'weekdays') {
    return dayOfWeek >= 1 && dayOfWeek <= 5; // Monday to Friday
  } else if (habit.frequency === 'weekends') {
    return dayOfWeek === 0 || dayOfWeek === 6; // Saturday or Sunday
  }
  
  return false;
};

// Get habits for a specific date
const getHabitsForDate = (date) => {
  return props.habits.filter(habit => isHabitActiveOnDate(habit, date));
};

// Day view
const dayViewHabits = computed(() => {
  return getHabitsForDate(currentDate.value);
});

// Week view
const weekDays = computed(() => {
  const startOfWeek = getStartOfWeek(currentDate.value);
  const days = [];
  
  for (let i = 0; i < 7; i++) {
    const date = new Date(startOfWeek);
    date.setDate(date.getDate() + i);
    days.push({
      date: date,
      dayName: date.toLocaleDateString('en-US', { weekday: 'short' }),
      dayNumber: date.getDate(),
      isToday: isSameDay(date, new Date()),
      habits: getHabitsForDate(date),
    });
  }
  
  return days;
});

// Month view - Full calendar grid (7x6 = 42 days)
const monthDays = computed(() => {
  const year = currentDate.value.getFullYear();
  const month = currentDate.value.getMonth();
  const startOfMonth = new Date(year, month, 1);
  const firstDayOfWeek = startOfMonth.getDay();
  const startDay = firstDayOfWeek === 0 ? 6 : firstDayOfWeek - 1; // Monday = 0
  
  // Calculate the start date (may be from previous month)
  const startDate = new Date(startOfMonth);
  startDate.setDate(startDate.getDate() - startDay);
  
  const days = [];
  
  // Generate 42 days (6 weeks × 7 days) to fill the complete calendar grid
  for (let i = 0; i < 42; i++) {
    const date = new Date(startDate);
    date.setDate(date.getDate() + i);
    
    const isCurrentMonth = date.getMonth() === month;
    const isPreviousMonth = date.getMonth() === (month === 0 ? 11 : month - 1);
    const isNextMonth = date.getMonth() === (month === 11 ? 0 : month + 1);
    
    days.push({
      date: date,
      dayNumber: date.getDate(),
      isToday: isSameDay(date, new Date()),
      isCurrentMonth: isCurrentMonth,
      isPreviousMonth: isPreviousMonth,
      isNextMonth: isNextMonth,
      habits: getHabitsForDate(date),
    });
  }
  
  return days;
});

const isSameDay = (date1, date2) => {
  return date1.getDate() === date2.getDate() &&
         date1.getMonth() === date2.getMonth() &&
         date1.getFullYear() === date2.getFullYear();
};

const getHabitStyle = (habit) => {
  return {
    backgroundColor: habit.color || '#93C5FD',
    color: getContrastColor(habit.color || '#93C5FD'),
  };
};

const getContrastColor = (hexColor) => {
  // Convert hex to RGB
  const r = parseInt(hexColor.slice(1, 3), 16);
  const g = parseInt(hexColor.slice(3, 5), 16);
  const b = parseInt(hexColor.slice(5, 7), 16);
  
  // Calculate relative luminance
  const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
  
  // Return black or white based on luminance
  return luminance > 0.5 ? '#000000' : '#FFFFFF';
};

// Get completion status for a habit on a specific date
const getHabitCompletionStatus = (habit, date) => {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  
  const checkDate = new Date(date);
  checkDate.setHours(0, 0, 0, 0);
  
  
  // Check if there's a completion record for this date
  const completion = habit.user_habits?.find(uh => {
    if (!uh.completed_at) return false;
    const completedDate = new Date(uh.completed_at);
    completedDate.setHours(0, 0, 0, 0);
    return completedDate.getTime() === checkDate.getTime();
  });
  
  // If there's a completion record and it's marked as completed
  if (completion && completion.completed) {
    return 'completed'; // Verde
  }
  
  // If it's today and not completed (or no record exists)
  if (checkDate.getTime() === today.getTime()) {
    return 'today'; // Amarillo
  }
  
  // If it's a past date and not completed (or no record exists)
  if (checkDate < today) {
    return 'missed'; // Rojo
  }
  
  // Future dates
  return 'future'; // Gris
};

// Get status bar color
const getStatusBarColor = (status) => {
  const colors = {
    completed: '#10B981', // Verde
    today: '#F59E0B',     // Amarillo
    missed: '#EF4444',    // Rojo
    future: '#9CA3AF',    // Gris
  };
  return colors[status] || colors.future;
};

// Get completion status for a specific user on a habit and date
const getUserHabitCompletionStatus = (habit, userId, date) => {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  
  const checkDate = new Date(date);
  checkDate.setHours(0, 0, 0, 0);
  
  // Always use all_user_habits as the single source of truth
  // This ensures each user's completion status is independent
  const completion = habit.all_user_habits?.find(uh => {
    if (uh.user_id !== userId || !uh.completed_at) return false;
    const completedDate = new Date(uh.completed_at);
    completedDate.setHours(0, 0, 0, 0);
    return completedDate.getTime() === checkDate.getTime();
  });
  
  // If there's a completion record and it's marked as completed
  if (completion && completion.completed) {
    return 'completed'; // Verde
  }
  
  // If it's today and not completed (or no record exists)
  if (checkDate.getTime() === today.getTime()) {
    return 'today'; // Amarillo
  }
  
  // If it's a past date and not completed (or no record exists)
  if (checkDate < today) {
    return 'missed'; // Rojo
  }
  
  // Future dates
  return 'future'; // Gris
};

// Get tag style for shared user based on completion status
const getSharedUserTagStyle = (habit, userId, date) => {
  const status = getUserHabitCompletionStatus(habit, userId, date);
  const backgroundColor = getStatusBarColor(status);
  const textColor = getContrastColor(backgroundColor);
  
  return {
    backgroundColor,
    color: textColor,
  };
};

// Get icon component for completion status
const getStatusIcon = (status) => {
  const icons = {
    completed: Check,   // ✓ Verde
    today: Clock,       // ⏰ Amarillo  
    missed: X,          // ✗ Rojo
    future: Calendar,   // 📅 Gris
  };
  return icons[status] || icons.future;
};

// Get all users for a habit (owner + shared users, no duplicates)
const getAllHabitUsers = (habit) => {
  const users = [];
  
  // 1. Always add the owner first (now we have habit.user from backend)
  if (habit.user) {
    users.push(habit.user);
  }
  
  // 2. Add shared users, but avoid duplicates with owner
  if (habit.shared_with && habit.shared_with.length > 0) {
    habit.shared_with.forEach(sharedUser => {
      // Only add if not already in the list (avoid duplicates with owner)
      if (!users.some(u => u.id === sharedUser.id)) {
        users.push(sharedUser);
      }
    });
  }
  
  return users;
};

// Check if a date is today
const isToday = (date) => {
  const today = new Date();
  const checkDate = new Date(date);
  return today.getDate() === checkDate.getDate() &&
         today.getMonth() === checkDate.getMonth() &&
         today.getFullYear() === checkDate.getFullYear();
};

// Get completion status for checkbox (for current user)
const isHabitCompleted = (habit, date) => {
  const page = usePage();
  const currentUserId = page.props.auth.user.id;
  
  // Use the same logic as getUserHabitCompletionStatus but return boolean
  const status = getUserHabitCompletionStatus(habit, currentUserId, date);
  return status === 'completed';
};

// Handle checkbox change
const handleHabitToggle = (habit, date, completed) => {
  const page = usePage();
  const currentUserId = page.props.auth.user.id;
  
  router.post(route('habits.completion', habit.id), {
    completed: completed,
    date: date.toISOString().split('T')[0], // Format as YYYY-MM-DD
  }, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      // Update both user_habits and all_user_habits for immediate UI feedback
      const checkDate = new Date(date);
      checkDate.setHours(0, 0, 0, 0);
      
      // Initialize arrays if they don't exist
      if (!habit.user_habits) {
        habit.user_habits = [];
      }
      if (!habit.all_user_habits) {
        habit.all_user_habits = [];
      }
      
      // Update user_habits (current user only)
      const existingUserHabitIndex = habit.user_habits.findIndex(uh => {
        if (!uh.completed_at) return false;
        const completedDate = new Date(uh.completed_at);
        completedDate.setHours(0, 0, 0, 0);
        return completedDate.getTime() === checkDate.getTime();
      });
      
      // Update all_user_habits (all users)
      const existingAllUserHabitIndex = habit.all_user_habits.findIndex(uh => {
        if (uh.user_id !== currentUserId || !uh.completed_at) return false;
        const completedDate = new Date(uh.completed_at);
        completedDate.setHours(0, 0, 0, 0);
        return completedDate.getTime() === checkDate.getTime();
      });
      
      const newRecord = {
        user_id: currentUserId,
        habit_id: habit.id,
        completed: completed,
        completed_at: completed ? date.toISOString() : null,
        user: page.props.auth.user // Include user info for consistency
      };
      
      // Update user_habits
      if (existingUserHabitIndex >= 0) {
        habit.user_habits[existingUserHabitIndex] = { ...newRecord };
      } else if (completed) {
        habit.user_habits.push({ ...newRecord });
      }
      
      // Update all_user_habits
      if (existingAllUserHabitIndex >= 0) {
        habit.all_user_habits[existingAllUserHabitIndex] = { ...newRecord };
      } else if (completed) {
        habit.all_user_habits.push({ ...newRecord });
      }
    },
    onError: (errors) => {
      console.error('Error updating habit completion:', errors);
    }
  });
};

// Countdown timer functions
const startCountdown = () => {
  countdownInterval = setInterval(() => {
    currentTime.value = new Date();
  }, 1000);
};

const stopCountdown = () => {
  if (countdownInterval) {
    clearInterval(countdownInterval);
    countdownInterval = null;
  }
};

// Check if there are incomplete habits for today
const hasIncompleteHabitsToday = computed(() => {
  if (viewMode.value !== 'day' || !isToday(currentDate.value)) {
    return false;
  }
  
  const todayHabits = getHabitsForDate(currentDate.value);
  return todayHabits.some(habit => !isHabitCompleted(habit, currentDate.value));
});

// Calculate time remaining until end of day
const timeRemainingToday = computed(() => {
  if (!hasIncompleteHabitsToday.value) {
    return null;
  }
  
  const now = currentTime.value;
  const endOfDay = new Date(now);
  endOfDay.setHours(23, 59, 59, 999);
  
  const diff = endOfDay.getTime() - now.getTime();
  
  if (diff <= 0) {
    return { hours: 0, minutes: 0, seconds: 0 };
  }
  
  const hours = Math.floor(diff / (1000 * 60 * 60));
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
  const seconds = Math.floor((diff % (1000 * 60)) / 1000);
  
  return { hours, minutes, seconds };
});

// Format countdown display
const formatCountdown = computed(() => {
  const time = timeRemainingToday.value;
  if (!time) return '';
  
  const { hours, minutes, seconds } = time;
  
  if (hours > 0) {
    return `${hours}h ${minutes}m ${seconds}s`;
  } else if (minutes > 0) {
    return `${minutes}m ${seconds}s`;
  } else {
    return `${seconds}s`;
  }
});

// Lifecycle hooks
onMounted(() => {
  startCountdown();
});

onUnmounted(() => {
  stopCountdown();
});
</script>

<template>
  <div class="space-y-4">
    <!-- Header with view controls -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <button
          @click="goToPrevious"
          class="p-2 hover:bg-gray-100 transition-colors"
        >
          <ChevronLeft :size="20" />
        </button>
        <h2 class="text-lg font-medium text-gray-900 min-w-[280px] text-center">
          {{ formatDateHeader }}
        </h2>
        <button
          @click="goToNext"
          class="p-2 hover:bg-gray-100 transition-colors"
        >
          <ChevronRight :size="20" />
        </button>
        <button
          @click="goToToday"
          class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors"
        >
          Today
        </button>
      </div>
      
      <div class="flex gap-1">
        <button
          v-for="mode in viewModes"
          :key="mode.value"
          @click="viewMode = mode.value"
          :class="[
            'px-3 py-1.5 text-sm font-medium transition-colors',
            viewMode === mode.value
              ? 'bg-blue-600 text-white'
              : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
          ]"
        >
          {{ mode.label }}
        </button>
      </div>
    </div>

    <!-- Day View -->
    <div v-if="viewMode === 'day'" class="border border-gray-200">
      <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="font-medium text-gray-900">Habits for this day</h3>
          <!-- Countdown timer for incomplete habits -->
          <div v-if="hasIncompleteHabitsToday" class="text-sm text-orange-600 font-medium">
            You have <span class="font-mono font-bold text-orange-800">{{ formatCountdown }}</span> to complete your daily habits
          </div>
        </div>
      </div>
      <div class="p-4">
        <div v-if="dayViewHabits.length === 0" class="text-center py-8 text-gray-500">
          No habits scheduled for this day
        </div>
        <div v-else style="display: flex; flex-direction: column; gap: 1px;">
          <div
            v-for="habit in dayViewHabits"
            :key="habit.id"
            class="flex items-stretch border border-gray-200 hover:border-gray-300 transition-colors"
          >
            <div class="flex-1 p-3">
              <div class="flex items-center gap-3">
                <!-- Checkbox only for today -->
                <div v-if="isToday(currentDate)" class="flex-shrink-0">
                  <input
                    type="checkbox"
                    :checked="isHabitCompleted(habit, currentDate)"
                    @change="handleHabitToggle(habit, currentDate, $event.target.checked)"
                    class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                  />
                </div>
                <div class="flex-1">
                  <LinkButton prefetch :href="route('habits.show', habit.id)" variant="secondary">
                    {{ habit.name }}
                  </LinkButton>
                  <div class="mt-1 flex items-center gap-2">
                    <span 
                      :style="getHabitStyle(habit)"
                      class="px-2 py-0.5 text-xs font-medium rounded-full"
                    >
                      {{ habit.frequency }}
                    </span>
                    <!-- Shared users indicator (total count) -->
                    <SharedUsersIndicator v-if="habit.shared_with" :shared-with="habit.shared_with" />
                    
                    <!-- User tags with completion status (only if shared with someone) -->
                    <div v-if="habit.shared_with && habit.shared_with.length > 0" class="flex items-center gap-1 flex-wrap">
                      <span
                        v-for="user in getAllHabitUsers(habit)"
                        :key="user.id"
                        :style="getSharedUserTagStyle(habit, user.id, currentDate)"
                        class="px-2 py-0.5 text-xs font-medium rounded-full flex items-center gap-1"
                        :title="`${user.name} (${user.email})`"
                      >
                        <component 
                          :is="getStatusIcon(getUserHabitCompletionStatus(habit, user.id, currentDate))" 
                          :size="12" 
                        />
                        {{ user.name }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Status indicator on the right -->
            <div 
              :style="{ 
                backgroundColor: getStatusBarColor(getHabitCompletionStatus(habit, currentDate)),
                width: '45px',
                borderLeft: '2px solid white'
              }"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Week View -->
    <div v-if="viewMode === 'week'" class="w-full">
      <div class="border border-gray-200">
        <div style="display: grid; grid-template-columns: repeat(7, minmax(0, 1fr));">
          <div
            v-for="day in weekDays"
            :key="day.date.toISOString()"
            class="border-r border-gray-200 last:border-r-0"
            style="min-width: 0;"
          >
            <div
              :class="[
                'px-2 py-2 text-center border-b border-gray-200',
                day.isToday ? 'bg-blue-50' : 'bg-gray-50'
              ]"
            >
              <div class="text-xs font-medium text-gray-600">{{ day.dayName }}</div>
              <div
                :class="[
                  'text-lg font-semibold',
                  day.isToday ? 'text-blue-600' : 'text-gray-900'
                ]"
              >
                {{ day.dayNumber }}
              </div>
            </div>
            <div class="p-2 min-h-[120px]">
              <div v-if="day.habits.length === 0" class="text-xs text-gray-400 text-center mt-4">
                No habits
              </div>
              <div v-else style="display: flex; flex-direction: column; gap: 1px;">
              <div
                v-for="habit in day.habits"
                :key="habit.id"
                class="text-xs font-medium rounded cursor-pointer hover:opacity-80 transition-opacity flex items-stretch"
                @click="$inertia.visit(route('habits.show', habit.id))"
              >
                <div class="flex-1 px-2 py-1 flex items-center gap-1" :style="getHabitStyle(habit)">
                  <span class="flex-1">{{ habit.name }}</span>
                  <SharedUsersIndicator v-if="habit.shared_with" :shared-with="habit.shared_with" />
                </div>
                <!-- Status indicator on the right -->
                <div 
                  :style="{ 
                    backgroundColor: getStatusBarColor(getHabitCompletionStatus(habit, day.date)),
                    width: '20px',
                    borderLeft: '1px solid white'
                  }"
                />
              </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Month View -->
    <div v-if="viewMode === 'month'" class="border border-gray-200">
      <!-- Day headers -->
      <div style="display: grid; grid-template-columns: repeat(7, minmax(0, 1fr));" class="bg-gray-50 border-b border-gray-200">
        <div v-for="day in ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']" :key="day" class="px-2 py-2 text-center text-xs font-medium text-gray-600 border-r border-gray-200 last:border-r-0">
          {{ day }}
        </div>
      </div>
      
      <!-- Calendar grid - 7 columns x 6 rows (42 days) -->
      <div style="display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); grid-template-rows: repeat(6, minmax(100px, auto));">
        <div
          v-for="(day, index) in monthDays"
          :key="index"
          :class="[
            'border-r border-b border-gray-200 last:border-r-0 p-2 relative',
            !day.isCurrentMonth ? 'bg-gray-50' : '',
            day.isToday ? 'bg-blue-50' : '',
            day.isPreviousMonth || day.isNextMonth ? 'text-gray-400' : ''
          ]"
          style="min-height: 100px;"
        >
          <div>
            <div
              :class="[
                'text-sm font-semibold mb-1',
                day.isToday ? 'text-blue-600' : day.isCurrentMonth ? 'text-gray-900' : 'text-gray-400'
              ]"
            >
              {{ day.dayNumber }}
            </div>
            <div style="display: flex; flex-direction: column; gap: 1px;">
              <div
                v-for="habit in day.habits.slice(0, 2)"
                :key="habit.id"
                :class="[
                  'text-xs font-medium rounded cursor-pointer hover:opacity-80 transition-opacity truncate flex items-stretch',
                  !day.isCurrentMonth ? 'opacity-50' : ''
                ]"
                @click="$inertia.visit(route('habits.show', habit.id))"
              >
                <div class="flex-1 px-1.5 py-0.5 truncate flex items-center gap-1" :style="getHabitStyle(habit)">
                  <span class="flex-1 truncate">{{ habit.name }}</span>
                  <SharedUsersIndicator v-if="habit.shared_with" :shared-with="habit.shared_with" />
                </div>
                <!-- Status indicator on the right -->
                <div 
                  class="flex-shrink-0"
                  :style="{ 
                    backgroundColor: getStatusBarColor(getHabitCompletionStatus(habit, day.date)),
                    width: '15px',
                    borderLeft: '1px solid white'
                  }"
                />
              </div>
              <div
                v-if="day.habits.length > 2"
                :class="[
                  'px-1.5 py-0.5 text-xs cursor-pointer hover:text-gray-700 truncate',
                  day.isCurrentMonth ? 'text-gray-500' : 'text-gray-400'
                ]"
              >
                +{{ day.habits.length - 2 }} more
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
