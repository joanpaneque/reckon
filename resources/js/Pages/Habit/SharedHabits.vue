<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import Button from '@/Components/Button.vue';
import HabitHeader from '@/Components/Habit/HabitHeader.vue';

const props = defineProps({
  pendingInvitations: {
    type: Array,
    required: true,
  },
  acceptedHabits: {
    type: Array,
    required: true,
  },
});

const acceptInvitation = (habitId) => {
  const form = useForm({});
  form.post(route('shared-habits.accept', habitId));
};

const refuseInvitation = (habitId) => {
  const form = useForm({});
  form.post(route('shared-habits.refuse', habitId));
};

const abandonHabit = (habitId) => {
  const form = useForm({});
  form.post(route('shared-habits.abandon', habitId));
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
  <AppLayout title="Shared Habits">
    <div class="space-y-6">
      <HabitHeader
        title="Shared Habits"
        :back-route="route('habits.index')"
      />

      <!-- Pending Invitations -->
      <Card>
        <div class="p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Pending Invitations</h2>
          
          <div v-if="pendingInvitations.length === 0" class="text-gray-500 text-center py-8">
            No pending invitations
          </div>
          
          <div v-else class="space-y-4">
            <div
              v-for="habit in pendingInvitations"
              :key="habit.id"
              class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-gray-300 transition-colors"
            >
              <div class="flex-1">
                <div class="flex items-center gap-3">
                  <div
                    :style="{ backgroundColor: habit.color }"
                    class="w-4 h-4 rounded-full border border-gray-300"
                  />
                  <div>
                    <h3 class="font-medium text-gray-900">{{ habit.name }}</h3>
                    <p class="text-sm text-gray-500">
                      Shared by {{ habit.user.name }} ({{ habit.user.email }})
                    </p>
                    <p v-if="habit.description" class="text-sm text-gray-600 mt-1">
                      {{ habit.description }}
                    </p>
                    <div class="text-xs text-gray-500 mt-1">
                      {{ habit.start_date }} to {{ habit.end_date }} • {{ formatFrequency(habit) }}
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="flex gap-2">
                <Button
                  @click="acceptInvitation(habit.id)"
                  class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 text-sm"
                >
                  Accept
                </Button>
                <Button
                  @click="refuseInvitation(habit.id)"
                  class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 text-sm"
                >
                  Refuse
                </Button>
              </div>
            </div>
          </div>
        </div>
      </Card>

      <!-- Accepted Shared Habits -->
      <Card>
        <div class="p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Accepted Shared Habits</h2>
          
          <div v-if="acceptedHabits.length === 0" class="text-gray-500 text-center py-8">
            No accepted shared habits
          </div>
          
          <div v-else class="space-y-4">
            <div
              v-for="habit in acceptedHabits"
              :key="habit.id"
              class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-gray-300 transition-colors"
            >
              <div class="flex-1">
                <div class="flex items-center gap-3">
                  <div
                    :style="{ backgroundColor: habit.color }"
                    class="w-4 h-4 rounded-full border border-gray-300"
                  />
                  <div>
                    <h3 class="font-medium text-gray-900">{{ habit.name }}</h3>
                    <p class="text-sm text-gray-500">
                      Shared by {{ habit.user.name }} ({{ habit.user.email }})
                    </p>
                    <p v-if="habit.description" class="text-sm text-gray-600 mt-1">
                      {{ habit.description }}
                    </p>
                    <div class="text-xs text-gray-500 mt-1">
                      {{ habit.start_date }} to {{ habit.end_date }} • {{ formatFrequency(habit) }}
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="flex gap-2">
                <Button
                  @click="abandonHabit(habit.id)"
                  class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 text-sm"
                >
                  Leave
                </Button>
              </div>
            </div>
          </div>
        </div>
      </Card>
    </div>
  </AppLayout>
</template>
