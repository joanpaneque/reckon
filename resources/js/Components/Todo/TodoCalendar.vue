<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { ChevronLeft, ChevronRight, Check } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  todos: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['date-changed', 'view-mode-changed']);

// Local todos state
const localTodos = ref([...props.todos]);

// Loading state
const isLoading = ref(false);

// Calendar state
const currentDate = ref(new Date());

// Helper function to format date as YYYY-MM-DD
const formatDateLocal = (date) => {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
};

// Fetch todos for a specific date
const fetchTodosForDate = async (date = null, silent = false) => {
  if (!silent) {
    isLoading.value = true;
  }
  
  const targetDate = date || currentDate.value;
  const dateString = formatDateLocal(targetDate);
  
  try {
    const response = await axios.get(route('todos.date-range'), {
      params: {
        start_date: dateString,
        end_date: dateString,
      },
    });

    if (response.data.todos) {
      // Response is an object with date strings as keys
      if (response.data.todos[dateString]) {
        localTodos.value = response.data.todos[dateString];
      } else {
        localTodos.value = [];
      }
    } else {
      localTodos.value = [];
    }
    
    if (!silent) {
      currentDate.value = targetDate;
    }
  } catch (error) {
    console.error('Error fetching todos:', error);
  } finally {
    if (!silent) {
      isLoading.value = false;
    }
  }
};

// Navigation
const goToPrevious = async () => {
  if (isLoading.value) return;
  
  const date = new Date(currentDate.value);
  date.setDate(date.getDate() - 1);
  await fetchTodosForDate(date);
};

const goToNext = async () => {
  if (isLoading.value) return;
  
  const date = new Date(currentDate.value);
  date.setDate(date.getDate() + 1);
  await fetchTodosForDate(date);
};

const goToToday = async () => {
  if (isLoading.value) return;
  
  const today = new Date();
  await fetchTodosForDate(today);
};

// Date formatting
const formatDateHeader = computed(() => {
  const date = currentDate.value;
  return date.toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
});

// Helper functions
const isToday = (date) => {
  const today = new Date();
  const checkDate = new Date(date);
  return today.toDateString() === checkDate.toDateString();
};

const isPastDate = (date) => {
  const today = new Date();
  const checkDate = new Date(date);
  today.setHours(0, 0, 0, 0);
  checkDate.setHours(0, 0, 0, 0);
  return checkDate < today;
};

const isFutureDate = (date) => {
  const today = new Date();
  const checkDate = new Date(date);
  today.setHours(0, 0, 0, 0);
  checkDate.setHours(0, 0, 0, 0);
  return checkDate > today;
};

// Toggle todo completion
const toggleTodo = async (todo) => {
  try {
    const response = await axios.post(route('todos.toggle-complete', todo.id), {
      completed: !todo.completed,
    });

    if (response.data.success) {
      // Update local state
      const index = localTodos.value.findIndex(t => t.id === todo.id);
      if (index !== -1) {
        localTodos.value[index].completed = response.data.todo.completed;
        localTodos.value[index].completed_at = response.data.todo.completed_at;
      }
    }
  } catch (error) {
    console.error('Error toggling todo:', error);
  }
};

// Watch for date changes to emit to parent
watch(currentDate, (newDate) => {
  emit('date-changed', newDate);
}, { immediate: true });

// Lifecycle hooks
onMounted(() => {
  fetchTodosForDate();
  emit('view-mode-changed', 'day');
});
</script>

<template>
  <div class="space-y-3 md:space-y-4">
    <!-- Header with navigation -->
    <div class="px-4 md:px-0 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <div class="flex items-center gap-1 md:gap-2">
        <button
          @click="goToPrevious"
          :disabled="isLoading"
          :class="[
            'p-1.5 md:p-2 hover:bg-dark-secondary/50 transition-colors flex-shrink-0',
            isLoading ? 'opacity-50 cursor-not-allowed' : ''
          ]"
        >
          <ChevronLeft :size="20" />
        </button>
        <h2 class="text-sm md:text-lg font-medium text-text-primary text-center flex-1 md:min-w-[280px]">
          {{ formatDateHeader }}
        </h2>
        <button
          @click="goToNext"
          :disabled="isLoading"
          :class="[
            'p-1.5 md:p-2 hover:bg-dark-secondary/50 transition-colors flex-shrink-0',
            isLoading ? 'opacity-50 cursor-not-allowed' : ''
          ]"
        >
          <ChevronRight :size="20" />
        </button>
        <button
          @click="goToToday"
          :disabled="isLoading"
          :class="[
            'px-2 md:px-3 py-1 md:py-1.5 text-xs md:text-sm font-medium text-text-secondary bg-dark-secondary hover:bg-dark-input-border transition-colors flex-shrink-0',
            isLoading ? 'opacity-50 cursor-not-allowed' : ''
          ]"
        >
          Hoy
        </button>
      </div>

      <div class="flex flex-col md:flex-row items-center md:items-center gap-2">
        <!-- Date status message -->
        <div class="text-xs font-medium whitespace-nowrap text-center min-h-[20px] flex items-center justify-center">
          <span v-if="isPastDate(currentDate)" class="text-text-tertiary">
            Viendo un día del pasado
          </span>
          <span v-else-if="isFutureDate(currentDate)" class="text-text-tertiary">
            Viendo un día del futuro
          </span>
        </div>
      </div>
    </div>

    <!-- Day View -->
    <div class="px-4 md:px-0">
      <div v-if="isLoading" class="text-center py-8">
        <div class="inline-flex items-center gap-2 text-text-secondary">
          <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-accent"></div>
          <span class="text-sm">Cargando todos...</span>
        </div>
      </div>
      <div v-else-if="localTodos.length === 0" class="text-center py-8 text-text-secondary text-sm">
        No hay todos para este día
      </div>
      <div v-else class="flex flex-col gap-2 md:gap-3">
        <div
          v-for="todo in localTodos"
          :key="todo.id"
          :class="[
            'flex items-start gap-3 p-3 md:p-4 border-2 rounded-modern transition-all duration-modern cursor-pointer hover:border-accent/50',
            todo.completed ? 'border-green-500/60 bg-green-500/5' : 'border-white/10 bg-dark-secondary/50'
          ]"
          @click="toggleTodo(todo)"
        >
          <!-- Checkbox -->
          <div class="flex-shrink-0 mt-0.5">
            <div
              :class="[
                'w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-modern',
                todo.completed 
                  ? 'bg-green-500 border-green-400' 
                  : 'border-white/20 hover:border-white/40'
              ]"
            >
              <Check 
                v-if="todo.completed" 
                :size="16" 
                :stroke-width="3" 
                class="text-white" 
              />
            </div>
          </div>

          <!-- Todo content -->
          <div class="flex-1 min-w-0">
            <h3 
              :class="[
                'text-sm md:text-base font-semibold mb-1 transition-colors duration-modern',
                todo.completed ? 'text-text-tertiary line-through' : 'text-text-primary'
              ]"
            >
              {{ todo.title }}
            </h3>
            <p 
              v-if="todo.description" 
              :class="[
                'text-xs md:text-sm transition-colors duration-modern',
                todo.completed ? 'text-text-tertiary line-through' : 'text-text-secondary'
              ]"
            >
              {{ todo.description }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

