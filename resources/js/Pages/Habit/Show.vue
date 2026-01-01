<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import LinkButton from '@/Components/LinkButton.vue';
import Modal from '@/Components/Modal.vue';
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
  habit: {
    type: Object,
    required: true,
  },
});

// Modal state
const showDeleteModal = ref(false);

const confirmDelete = () => {
  showDeleteModal.value = true;
};

const handleDeleteConfirm = () => {
  router.delete(route('habits.destroy', props.habit.id));
  showDeleteModal.value = false;
};

const handleDeleteCancel = () => {
  showDeleteModal.value = false;
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

const getFrequencyBadgeColor = (frequency) => {
  const colors = {
    everyday: 'bg-green-500/20 text-green-400',
    weekdays: 'bg-accent/20 text-accent',
    weekends: 'bg-purple-500/20 text-purple-400',
    custom: 'bg-blue-500/20 text-blue-400',
  };
  return colors[frequency] || 'bg-text-tertiary/20 text-text-tertiary';
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

const daysSinceStart = computed(() => {
  if (!props.habit.start_date) return 0;
  const start = new Date(props.habit.start_date);
  const now = new Date();
  const diffTime = Math.abs(now - start);
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
});

const isActive = computed(() => {
  const now = new Date();
  const start = new Date(props.habit.start_date);
  const end = new Date(props.habit.end_date);

  return now >= start && now <= end;
});
</script>

<template>
  <AppLayout :title="habit.name">
    <div class="space-y-4 md:space-y-6">
      <!-- Mobile Header -->
      <div class="md:hidden space-y-3">
        <div class="flex items-center justify-between">
          <h1 class="text-xl font-bold text-text-primary tracking-wide-modern">
            {{ habit.name }}
          </h1>
          <LinkButton prefetch :href="route('habits.index')" variant="secondary" size="sm">
            Back
          </LinkButton>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
          <span :class="getFrequencyBadgeColor(habit.frequency)" class="px-2 py-1 text-xs font-medium rounded-full">
            {{ formatFrequency(habit) }}
          </span>
          <span v-if="isActive" class="px-2 py-1 text-xs font-medium rounded-full bg-green-500/20 text-green-400">
            Active
          </span>
          <span v-else class="px-2 py-1 text-xs font-medium rounded-full bg-text-tertiary/20 text-text-tertiary">
            Inactive
          </span>
        </div>
        <!-- Mobile Action Buttons -->
        <div class="flex gap-2 pt-2 border-t border-dark-border">
          <LinkButton prefetch :href="route('habits.edit', habit.id)" variant="primary" size="sm" class="flex-1">
            Edit Habit
          </LinkButton>
          <LinkButton @click="confirmDelete" variant="danger" size="sm" class="flex-1">
            Delete
          </LinkButton>
        </div>
      </div>

      <!-- Desktop Header -->
      <div class="hidden md:flex items-center justify-between">
        <div>
          <h1 class="text-h2 font-bold text-text-primary tracking-wide-modern flex items-center gap-3">
            {{ habit.name }}
            <span :class="getFrequencyBadgeColor(habit.frequency)" class="px-3 py-1 text-sm font-medium rounded-full">
              {{ formatFrequency(habit) }}
            </span>
            <span v-if="isActive" class="px-3 py-1 text-sm font-medium rounded-full bg-green-500/20 text-green-400">
              Active
            </span>
            <span v-else class="px-3 py-1 text-sm font-medium rounded-full bg-text-tertiary/20 text-text-tertiary">
              Inactive
            </span>
          </h1>
        </div>
        <LinkButton prefetch :href="route('habits.index')" variant="secondary">
          Back to list
        </LinkButton>
      </div>

      <Card title="Habit Details">
        <div class="space-y-4">
          <div v-if="habit.description">
            <h3 class="text-sm font-medium text-gray-700 mb-1">Description</h3>
            <p class="text-gray-900">{{ habit.description }}</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <h3 class="text-sm font-medium text-gray-700 mb-1">Start Date</h3>
              <p class="text-gray-900">{{ formatDate(habit.start_date) }}</p>
            </div>

            <div>
              <h3 class="text-sm font-medium text-gray-700 mb-1">End Date</h3>
              <p class="text-gray-900">{{ formatDate(habit.end_date) }}</p>
            </div>

            <div>
              <h3 class="text-sm font-medium text-gray-700 mb-1">Frequency</h3>
              <p class="text-gray-900">{{ formatFrequency(habit) }}</p>
            </div>

            <div>
              <h3 class="text-sm font-medium text-gray-700 mb-1">Days Since Start</h3>
              <p class="text-gray-900">{{ daysSinceStart }} days</p>
            </div>
          </div>

          <div class="hidden md:flex gap-2 pt-4 border-t">
            <LinkButton prefetch :href="route('habits.edit', habit.id)" variant="primary" size="sm">
              Edit Habit
            </LinkButton>
            <LinkButton @click="confirmDelete" variant="danger" size="sm">
              Delete Habit
            </LinkButton>
          </div>
        </div>
      </Card>
    </div>

    <!-- Delete Confirmation Modal -->
    <Modal
      :show="showDeleteModal"
      title="Delete Habit"
      :message="`Are you sure you want to delete this habit? (${habit.name})`"
      confirm-text="Delete"
      cancel-text="Cancel"
      confirm-variant="danger"
      @confirm="handleDeleteConfirm"
      @cancel="handleDeleteCancel"
      @close="showDeleteModal = false"
    />
  </AppLayout>
</template>
