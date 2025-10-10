<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import LinkButton from '@/Components/LinkButton.vue';
import SharedUsersIndicator from '@/Components/SharedUsersIndicator.vue';
import HabitMediaModal from '@/Components/Habit/HabitMediaModal.vue';
import HabitMediaViewer from '@/Components/Habit/HabitMediaViewer.vue';
import MotivationSendModal from '@/Components/Habit/MotivationSendModal.vue';
import { ChevronLeft, ChevronRight, Check, Clock, X, Calendar, ChevronDown, ChevronUp, Upload, Trash2, Heart, Crown } from 'lucide-vue-next';
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

// State to track which habits have expanded users list
const expandedHabits = ref({});

// Media upload state
const showMediaModal = ref(false);
const selectedMediaFile = ref(null);
const mediaPreview = ref(null);
const mediaType = ref(null);
const selectedUserHabitId = ref(null);

// Motivation state
const showMotivationModal = ref(false);
const selectedMotivationUser = ref(null);

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

const getHabitGradientStyle = (habit) => {
  const habitColor = habit.color || '#93C5FD';
  // Crear un gradiente más suave y moderno con 3 stops
  return {
    background: `linear-gradient(90deg, #0F0F0F 0%, #1A1A1A 30%, ${habitColor} 100%)`,
    boxShadow: '0 1px 3px rgba(0, 0, 0, 0.2)', // Sombra sutil
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

  // Check if current user is a shared user and when they joined
  const page = usePage();
  const currentUserId = page.props.auth.user.id;
  const isOwner = habit.user?.id === currentUserId;

  if (!isOwner && habit.shared_with) {
    const sharedUser = habit.shared_with.find(u => u.id === currentUserId);
    if (sharedUser && sharedUser.joined_at) {
      const joinedDate = new Date(sharedUser.joined_at);
      joinedDate.setHours(0, 0, 0, 0);

      // If checking a date before the user joined, don't show it as missed
      if (checkDate < joinedDate) {
        return 'future'; // Treat as future (gray) - user hadn't joined yet
      }
    }
  }

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
    completed: '#10b981', // Verde esmeralda moderno
    today: '#f59e0b',     // Ámbar vibrante
    missed: '#ef4444',    // Rojo coral
    future: '#6b7280',    // Gris neutro
  };
  return colors[status] || colors.future;
};

// Get status gradient for modern look
const getStatusGradient = (status) => {
  const gradients = {
    completed: 'linear-gradient(135deg, #10b981 0%, #059669 100%)', // Verde esmeralda a verde bosque
    today: 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)',     // Ámbar a naranja profundo
    missed: 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)',    // Rojo coral a rojo intenso
    future: 'linear-gradient(135deg, #6b7280 0%, #4b5563 100%)',    // Gris claro a gris oscuro
  };
  return gradients[status] || gradients.future;
};

// Get completion status for a specific user on a habit and date
const getUserHabitCompletionStatus = (habit, userId, date) => {
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  const checkDate = new Date(date);
  checkDate.setHours(0, 0, 0, 0);

  // Check if this user is a shared user and when they joined
  const page = usePage();
  const isOwner = habit.user?.id === userId;

  if (!isOwner && habit.shared_with) {
    const sharedUser = habit.shared_with.find(u => u.id === userId);

    if (sharedUser && sharedUser.joined_at) {
      const joinedDate = new Date(sharedUser.joined_at);
      joinedDate.setHours(0, 0, 0, 0);

      // If checking a date before the user joined, don't show it as missed
      if (checkDate < joinedDate) {
        return 'future'; // Treat as future (gray) - user hadn't joined yet
      }
    }
  }

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
  const gradient = getStatusGradient(status);

  return {
    background: gradient,
    color: '#FFFFFF', // Siempre texto blanco para mejor contraste con gradientes
    boxShadow: '0 2px 4px rgba(0, 0, 0, 0.1)', // Sombra sutil para profundidad
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

// Get all users for a habit on a specific date (owner + shared users who have joined by that date, no duplicates)
const getAllHabitUsers = (habit, date) => {
  const users = [];

  const checkDate = new Date(date);
  checkDate.setHours(0, 0, 0, 0);

  // 1. Always add the owner first (now we have habit.user from backend)
  if (habit.user) {
    users.push(habit.user);
  }

  // 2. Add shared users, but only if they had joined by the given date
  if (habit.shared_with && habit.shared_with.length > 0) {
    habit.shared_with.forEach(sharedUser => {
      // Check if user had joined by this date
      if (sharedUser.joined_at) {
        const joinedDate = new Date(sharedUser.joined_at);
        joinedDate.setHours(0, 0, 0, 0);

        // Only add if user joined on or before the check date, and not already in list (avoid duplicates with owner)
        if (checkDate >= joinedDate && !users.some(u => u.id === sharedUser.id)) {
          users.push(sharedUser);
        }
      } else {
        // If no joined_at, add them (backwards compatibility or owner shared)
        if (!users.some(u => u.id === sharedUser.id)) {
          users.push(sharedUser);
        }
      }
    });
  }

  // Get current user ID
  const page = usePage();
  const currentUserId = page.props.auth.user.id;

  // Sort users: current user first, then alphabetically by name
  users.sort((a, b) => {
    // Current user always comes first
    if (a.id === currentUserId) return -1;
    if (b.id === currentUserId) return 1;
    // Then sort alphabetically
    return a.name.localeCompare(b.name);
  });

  return users;
};

// Get user initials (e.g., "Joan Paneque Domingo" -> "J.P.D")
const getUserInitials = (name) => {
  if (!name) return '';
  return name
    .split(' ')
    .map(word => word.charAt(0).toUpperCase())
    .join('.');
};

// Check if a date is today
const isToday = (date) => {
  const today = new Date();
  const checkDate = new Date(date);
  return today.getDate() === checkDate.getDate() &&
         today.getMonth() === checkDate.getMonth() &&
         today.getFullYear() === checkDate.getFullYear();
};

// Check if date is in the past
const isPastDate = (date) => {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const checkDate = new Date(date);
  checkDate.setHours(0, 0, 0, 0);
  return checkDate < today;
};

// Check if date is in the future
const isFutureDate = (date) => {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const checkDate = new Date(date);
  checkDate.setHours(0, 0, 0, 0);
  return checkDate > today;
};

// Get completion status for checkbox (for current user)
const isHabitCompleted = (habit, date) => {
  const page = usePage();
  const currentUserId = page.props.auth.user.id;

  // Use the same logic as getUserHabitCompletionStatus but return boolean
  const status = getUserHabitCompletionStatus(habit, currentUserId, date);
  return status === 'completed';
};

// Toggle expanded state for habit users
const toggleHabitUsers = (habitId) => {
  expandedHabits.value[habitId] = !expandedHabits.value[habitId];
};

// Check if habit has media for today
const hasMediaForToday = (habit, date) => {
  const page = usePage();
  const currentUserId = page.props.auth.user.id;

  // Normalize the date to YYYY-MM-DD format
  const checkDate = new Date(date);
  checkDate.setHours(0, 0, 0, 0);
  const checkDateStr = checkDate.toISOString().split('T')[0];

  const userHabit = habit.all_user_habits?.find(uh => {
    if (!uh) return false;
    if (uh.user_id !== currentUserId) return false;
    if (!uh.completed) return false;
    if (!uh.completed_at) return false;

    // Compare dates (normalized to YYYY-MM-DD)
    const completedDateStr = uh.completed_at.split('T')[0];
    return completedDateStr === checkDateStr;
  });

  return userHabit && userHabit.media_path;
};

// Handle media deletion
const handleDeleteMedia = (habit, date) => {
  if (!confirm('Are you sure you want to delete this media?')) {
    return;
  }

  const page = usePage();
  const currentUserId = page.props.auth.user.id;

  // Normalize the date to YYYY-MM-DD format
  const checkDate = new Date(date);
  checkDate.setHours(0, 0, 0, 0);
  const checkDateStr = checkDate.toISOString().split('T')[0];

  const userHabit = habit.all_user_habits?.find(uh => {
    if (!uh) return false;
    if (uh.user_id !== currentUserId) return false;
    if (!uh.completed) return false;
    if (!uh.completed_at) return false;

    // Compare dates (normalized to YYYY-MM-DD)
    const completedDateStr = uh.completed_at.split('T')[0];
    return completedDateStr === checkDateStr;
  });

  if (!userHabit || !userHabit.id) {
    alert('Error: Could not find habit completion. Please refresh the page and try again.');
    return;
  }

  // Send delete request
  router.delete(route('habits.media.delete', userHabit.id), {
    preserveScroll: true,
    onSuccess: () => {
      router.reload({ only: ['habits'] });
    },
    onError: () => {
      alert('Error deleting media. Please try again.');
    }
  });
};

// Handle media file selection
const handleMediaFileSelect = (event, habit, date) => {
  const file = event.target.files[0];

  if (!file) {
    return;
  }

  // Validate file type
  const validImageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
  const validVideoTypes = ['video/mp4', 'video/quicktime', 'video/x-msvideo'];

  if (![...validImageTypes, ...validVideoTypes].includes(file.type)) {
    alert('Please select a valid image (JPG, PNG, GIF) or video (MP4, MOV, AVI) file.');
    return;
  }

  // Validate file size (50MB max)
  if (file.size > 50 * 1024 * 1024) {
    alert('File size must be less than 50MB.');
    return;
  }

  // Find the user habit ID for this completion
  const page = usePage();
  const currentUserId = page.props.auth.user.id;

  // Normalize the date to YYYY-MM-DD format
  const checkDate = new Date(date);
  checkDate.setHours(0, 0, 0, 0);
  const checkDateStr = checkDate.toISOString().split('T')[0];

  const userHabit = habit.all_user_habits?.find(uh => {
    if (!uh) return false;
    if (uh.user_id !== currentUserId) return false;
    if (!uh.completed) return false;
    if (!uh.completed_at) return false;

    // Compare dates (normalized to YYYY-MM-DD)
    const completedDateStr = uh.completed_at.split('T')[0];
    return completedDateStr === checkDateStr;
  });

  if (!userHabit) {
    alert('Error: Could not find habit completion. Please refresh the page and try again.');
    return;
  }

  if (!userHabit.id) {
    alert('Error: Habit completion is missing an ID. Please refresh the page and try again.');
    return;
  }

  // Set media preview
  selectedMediaFile.value = file;
  selectedUserHabitId.value = userHabit.id;
  mediaType.value = validImageTypes.includes(file.type) ? 'image' : 'video';

  const reader = new FileReader();
  reader.onload = (e) => {
    mediaPreview.value = e.target.result;
    showMediaModal.value = true;
  };
  reader.readAsDataURL(file);
};

const closeMediaModal = () => {
  showMediaModal.value = false;
  selectedMediaFile.value = null;
  mediaPreview.value = null;
  mediaType.value = null;
  selectedUserHabitId.value = null;
};

const handleMediaUploaded = () => {
  // Reload the page data to show the new media
  router.reload({ only: ['habits'] });
};

// Handle motivation modal
const openMotivationModal = (user) => {
  selectedMotivationUser.value = user;
  showMotivationModal.value = true;
};

const closeMotivationModal = () => {
  showMotivationModal.value = false;
  selectedMotivationUser.value = null;
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
    onSuccess: (response) => {
      // Reload habits data to get the updated user_habit with ID
      router.reload({ only: ['habits'] });
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
  <div class="space-y-3 md:space-y-4">
    <!-- Header with view controls -->
    <div class="px-4 md:px-0 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <div class="flex items-center gap-1 md:gap-2">
        <button
          @click="goToPrevious"
          class="p-1.5 md:p-2 hover:bg-dark-secondary/50 transition-colors flex-shrink-0"
        >
          <ChevronLeft :size="20" />
        </button>
        <h2 class="text-sm md:text-lg font-medium text-text-primary text-center flex-1 md:min-w-[280px]">
          {{ formatDateHeader }}
        </h2>
        <button
          @click="goToNext"
          class="p-1.5 md:p-2 hover:bg-dark-secondary/50 transition-colors flex-shrink-0"
        >
          <ChevronRight :size="20" />
        </button>
        <button
          @click="goToToday"
          class="px-2 md:px-3 py-1 md:py-1.5 text-xs md:text-sm font-medium text-text-secondary bg-dark-secondary hover:bg-dark-input-border transition-colors flex-shrink-0"
        >
          Today
        </button>
      </div>

      <div class="flex flex-col md:flex-row items-center md:items-center gap-2">
        <!-- Date status message (always reserves space to avoid CLS) -->
        <div class="text-xs font-medium whitespace-nowrap text-center min-h-[20px] flex items-center justify-center">
          <!-- Today with incomplete habits: show countdown -->
          <span v-if="viewMode === 'day' && isToday(currentDate) && hasIncompleteHabitsToday" class="bg-gradient-to-r from-amber-500 to-orange-600 bg-clip-text text-transparent">
            <span class="font-mono font-bold bg-gradient-to-r from-orange-500 to-red-500 bg-clip-text text-transparent">{{ formatCountdown }}</span> left
          </span>
          <!-- Past date -->
          <span v-else-if="viewMode === 'day' && isPastDate(currentDate)" class="text-text-tertiary">
            Viewing a day from the past
          </span>
          <!-- Future date -->
          <span v-else-if="viewMode === 'day' && isFutureDate(currentDate)" class="text-text-tertiary">
            Viewing a day from the future
          </span>
        </div>

        <div class="hidden md:flex gap-1">
          <button
            v-for="mode in viewModes"
            :key="mode.value"
            @click="viewMode = mode.value"
            :class="[
              'flex-1 md:flex-none px-3 py-1.5 text-xs md:text-sm font-medium transition-colors rounded-modern',
              viewMode === mode.value
                ? 'bg-accent text-dark-primary'
                : 'bg-dark-secondary text-text-secondary hover:bg-dark-input-border'
            ]"
          >
            {{ mode.label }}
          </button>
        </div>
      </div>
    </div>

    <!-- Day View -->
    <div v-if="viewMode === 'day'" class="space-y-2">
      <div class="px-4 md:px-0">
        <div v-if="dayViewHabits.length === 0" class="text-center py-8 text-text-secondary text-sm">
          No habits scheduled for this day
        </div>
        <div v-else class="flex flex-col gap-2 md:gap-3">
          <div
            v-for="habit in dayViewHabits"
            :key="habit.id"
            :class="[
              'flex flex-col gap-0 border-2 rounded-modern overflow-hidden transition-all duration-modern',
              isHabitCompleted(habit, currentDate) ? 'border-green-500/60' : 'border-white/10'
            ]"
          >
            <!-- Row 1: Habit container -->
            <div
              class="group transition-all duration-modern p-2 md:p-3 relative shadow-[inset_0_0_0_1px_rgba(255,255,255,0.05)]"
              :style="getHabitGradientStyle(habit)"
            >
              <!-- Clickable area (right 50% only) -->
              <div
                v-if="isToday(currentDate)"
                class="absolute top-0 right-0 w-1/2 h-full cursor-pointer hover:bg-white/5 transition-all duration-modern z-10"
                @click="handleHabitToggle(habit, currentDate, !isHabitCompleted(habit, currentDate))"
              />

              <!-- Visual indicator for clickability -->
              <div v-if="isToday(currentDate)" class="absolute top-2 right-2 md:top-3 md:right-3 transition-all duration-modern pointer-events-none z-20">
                <div
                  v-if="isHabitCompleted(habit, currentDate)"
                  class="w-7 h-7 md:w-8 md:h-8 rounded-full bg-gradient-to-br from-green-600 to-green-700 border-2 border-green-400 flex items-center justify-center shadow-lg shadow-green-500/50"
                >
                  <Check :size="18" :stroke-width="3.5" class="text-white drop-shadow-lg" />
                </div>
                <div
                  v-else
                  class="w-6 h-6 rounded-full border-2 border-white/20 group-hover:border-white/40 group-hover:bg-white/5 transition-all duration-modern"
                />
              </div>

              <div class="flex items-start gap-2 md:gap-3 pr-8 relative">
                <div class="flex-1 min-w-0">
                  <h3 class="text-sm md:text-base font-semibold text-text-primary mb-1 transition-colors duration-modern">
                    {{ habit.name }}
                  </h3>
                  <div class="mt-1 flex items-center gap-1.5 md:gap-2 flex-wrap">
                    <!-- Shared users indicator (total count) -->
                    <SharedUsersIndicator v-if="habit.shared_with" :shared-with="habit.shared_with" />

                    <!-- Upload media button (only if completed and today) -->
                    <label
                      v-if="isToday(currentDate) && isHabitCompleted(habit, currentDate) && !hasMediaForToday(habit, currentDate)"
                      class="flex items-center gap-1.5 bg-white/5 border border-white/20 text-text-secondary hover:bg-white/10 hover:border-white/30 px-2.5 py-1.5 rounded-modern cursor-pointer transition-all duration-modern hover:scale-105 hover:shadow-lg relative z-30"
                      @click.stop
                    >
                      <Upload :size="16" :stroke-width="2" />
                      <span class="text-xs font-medium hidden sm:inline">Add media</span>
                      <input
                        type="file"
                        accept="image/*,video/*"
                        class="hidden"
                        @change="handleMediaFileSelect($event, habit, currentDate)"
                      />
                    </label>

                    <!-- Delete media button (only if media exists) -->
                    <button
                      v-if="isToday(currentDate) && isHabitCompleted(habit, currentDate) && hasMediaForToday(habit, currentDate)"
                      @click.stop="handleDeleteMedia(habit, currentDate)"
                      class="flex items-center gap-1.5 bg-red-600/10 border border-red-600/30 text-red-400 hover:bg-red-600/20 hover:border-red-600/50 px-2.5 py-1.5 rounded-modern cursor-pointer transition-all duration-modern hover:scale-105 hover:shadow-lg relative z-30"
                    >
                      <Trash2 :size="16" :stroke-width="2" />
                      <span class="text-xs font-medium hidden sm:inline">Delete media</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Row 2: Users list (always visible, horizontal) -->
            <div
              v-if="habit.shared_with && habit.shared_with.length > 0"
              class="bg-dark-secondary px-2 md:px-3 py-2 flex items-center gap-2 flex-wrap border-t border-dark-border"
              @click.stop
            >
              <div
                v-for="user in getAllHabitUsers(habit, currentDate)"
                :key="user.id"
                :style="getSharedUserTagStyle(habit, user.id, currentDate)"
                :class="user.id !== $page.props.auth.user.id ? 'cursor-pointer hover:scale-105 hover:shadow-lg' : ''"
                class="h-7 px-2 text-xs font-medium rounded flex items-center gap-1.5 w-fit transition-all duration-modern"
                @click="user.id !== $page.props.auth.user.id ? openMotivationModal(user) : null"
                :title="user.id !== $page.props.auth.user.id ? `Motivate ${user.name}` : user.name"
              >
                <component
                  :is="getStatusIcon(getUserHabitCompletionStatus(habit, user.id, currentDate))"
                  :size="14"
                  :stroke-width="2"
                />
                <!-- Crown icon (only for habit owner) -->
                <Crown
                  v-if="habit.user && user.id === habit.user.id"
                  :size="12"
                  :stroke-width="2"
                  class="text-yellow-400"
                />
                <span class="md:hidden">{{ getUserInitials(user.name) }}</span>
                <span class="hidden md:inline">{{ user.name }}</span>
                <!-- Heart icon (only for other users, not yourself) -->
                <Heart
                  v-if="user.id !== $page.props.auth.user.id"
                  :size="12"
                  :stroke-width="2"
                  class="ml-1"
                />
              </div>
            </div>

            <!-- Row 3: Shared moments (media viewer) -->
            <HabitMediaViewer
              :habit="habit"
              :date="currentDate"
              :users="getAllHabitUsers(habit, currentDate)"
              class="border-t border-dark-border"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Week View -->
    <div v-if="viewMode === 'week'" class="w-full overflow-x-auto">
      <div class="border border-dark-border min-w-[640px]">
        <div style="display: grid; grid-template-columns: repeat(7, minmax(0, 1fr));">
          <div
            v-for="day in weekDays"
            :key="day.date.toISOString()"
            class="border-r border-dark-border last:border-r-0"
            style="min-width: 0;"
          >
            <div
              :class="[
                'px-1 md:px-2 py-1.5 md:py-2 text-center border-b border-dark-border',
                day.isToday ? 'bg-accent/10' : 'bg-dark-secondary'
              ]"
            >
              <div class="text-xs font-medium text-text-secondary">{{ day.dayName }}</div>
              <div
                :class="[
                  'text-base md:text-lg font-semibold',
                  day.isToday ? 'text-accent' : 'text-text-primary'
                ]"
              >
                {{ day.dayNumber }}
              </div>
            </div>
            <div class="p-1 md:p-2 min-h-[100px] md:min-h-[120px]">
              <div v-if="day.habits.length === 0" class="text-xs text-text-tertiary text-center mt-2 md:mt-4">
                No habits
              </div>
              <div v-else style="display: flex; flex-direction: column; gap: 1px;">
              <div
                v-for="habit in day.habits"
                :key="habit.id"
                class="text-xs font-medium rounded cursor-pointer hover:opacity-80 transition-opacity flex items-stretch"
                @click="$inertia.visit(route('habits.show', habit.id))"
              >
                <div class="flex-1 px-1.5 md:px-2 py-1 flex items-center gap-1" :style="getHabitStyle(habit)">
                  <span class="flex-1 truncate">{{ habit.name }}</span>
                  <SharedUsersIndicator v-if="habit.shared_with" :shared-with="habit.shared_with" class="hidden md:flex" />
                </div>
                <!-- Status indicator on the right - grid for multiple users -->
                <div class="flex flex-col w-[15px] md:w-[20px] border-l border-dark-border">
                  <div
                    v-for="user in getAllHabitUsers(habit, day.date)"
                    :key="user.id"
                    :style="{
                      background: getStatusGradient(getUserHabitCompletionStatus(habit, user.id, day.date)),
                      flex: 1
                    }"
                    :title="`${user.name}: ${getUserHabitCompletionStatus(habit, user.id, day.date)}`"
                  />
                </div>
              </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Month View -->
    <div v-if="viewMode === 'month'" class="w-full overflow-x-auto">
      <div class="border border-dark-border min-w-[640px]">
        <!-- Day headers -->
        <div style="display: grid; grid-template-columns: repeat(7, minmax(0, 1fr));" class="bg-dark-secondary border-b border-dark-border">
          <div v-for="day in ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']" :key="day" class="px-1 md:px-2 py-1.5 md:py-2 text-center text-xs font-medium text-text-secondary border-r border-dark-border last:border-r-0">
            <span class="hidden md:inline">{{ day }}</span>
            <span class="md:hidden">{{ day.charAt(0) }}</span>
          </div>
        </div>

        <!-- Calendar grid - 7 columns x 6 rows (42 days) -->
        <div style="display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); grid-template-rows: repeat(6, minmax(80px, auto));">
          <div
            v-for="(day, index) in monthDays"
            :key="index"
            :class="[
              'border-r border-b border-dark-border last:border-r-0 p-1 md:p-2 relative',
              !day.isCurrentMonth ? 'bg-dark-secondary' : '',
              day.isToday ? 'bg-accent/10' : '',
              day.isPreviousMonth || day.isNextMonth ? 'text-text-tertiary' : ''
            ]"
            style="min-height: 80px;"
          >
            <div>
              <div
                :class="[
                  'text-xs md:text-sm font-semibold mb-0.5 md:mb-1',
                  day.isToday ? 'text-accent' : day.isCurrentMonth ? 'text-text-primary' : 'text-text-tertiary'
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
                  <div class="flex-1 px-1 md:px-1.5 py-0.5 truncate flex items-center gap-1" :style="getHabitStyle(habit)">
                    <span class="flex-1 truncate">{{ habit.name }}</span>
                    <SharedUsersIndicator v-if="habit.shared_with" :shared-with="habit.shared_with" class="hidden md:flex" />
                  </div>
                  <!-- Status indicator on the right - grid for multiple users -->
                  <div class="flex flex-col w-[12px] flex-shrink-0 border-l border-dark-border">
                    <div
                      v-for="user in getAllHabitUsers(habit, day.date)"
                      :key="user.id"
                      :style="{
                        background: getStatusGradient(getUserHabitCompletionStatus(habit, user.id, day.date)),
                        flex: 1
                      }"
                      :title="`${user.name}: ${getUserHabitCompletionStatus(habit, user.id, day.date)}`"
                    />
                  </div>
                </div>
                <div
                  v-if="day.habits.length > 2"
                  :class="[
                    'px-1 md:px-1.5 py-0.5 text-xs cursor-pointer hover:text-text-secondary truncate',
                    day.isCurrentMonth ? 'text-text-secondary' : 'text-text-tertiary'
                  ]"
                >
                  +{{ day.habits.length - 2 }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Media Upload Modal -->
    <HabitMediaModal
      :show="showMediaModal"
      :media-file="selectedMediaFile"
      :media-preview="mediaPreview"
      :media-type="mediaType"
      :user-habit-id="selectedUserHabitId"
      @close="closeMediaModal"
      @uploaded="handleMediaUploaded"
    />

    <!-- Motivation Send Modal -->
    <MotivationSendModal
      v-if="selectedMotivationUser"
      :show="showMotivationModal"
      :user="selectedMotivationUser"
      @close="closeMotivationModal"
    />
  </div>
</template>
