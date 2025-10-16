<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import HabitHeader from '@/Components/Habit/HabitHeader.vue';
import Card from '@/Components/Card.vue';
import SocialComparisonChart from '@/Components/Habit/SocialComparisonChart.vue';

const props = defineProps({
  currentUser: {
    type: Object,
    required: true,
  },
  currentUserStatistics: {
    type: Array,
    required: true,
  },
  friendsStatistics: {
    type: Array,
    required: true,
  },
  dateRange: {
    type: Object,
    required: true,
  },
});

// Calculate totals for current user
const currentUserTotals = computed(() => {
  const completed = props.currentUserStatistics.reduce((sum, day) => sum + day.habitsCompletedCount, 0);
  const failed = props.currentUserStatistics.reduce((sum, day) => sum + day.habitsFailedCount, 0);
  const total = completed + failed;
  const percentage = total > 0 ? Math.round((completed / total) * 100) : 0;

  return { completed, failed, total, percentage };
});

// Calculate totals for each friend
const friendsTotals = computed(() => {
  return props.friendsStatistics.map(friend => {
    const completed = friend.statistics.reduce((sum, day) => sum + day.habitsCompletedCount, 0);
    const failed = friend.statistics.reduce((sum, day) => sum + day.habitsFailedCount, 0);
    const total = completed + failed;
    const percentage = total > 0 ? Math.round((completed / total) * 100) : 0;

    return {
      user: friend.user,
      completed,
      failed,
      total,
      percentage,
    };
  });
});

// Sort friends by completion rate
const sortedFriends = computed(() => {
  return [...friendsTotals.value].sort((a, b) => b.percentage - a.percentage);
});

// Calculate chart data
const chartData = computed(() => {
  const maxValue = Math.max(
    ...props.currentUserStatistics.map(d => d.habitsCompletedCount),
    ...props.friendsStatistics.flatMap(f => f.statistics.map(d => d.habitsCompletedCount))
  );

  return {
    dates: props.currentUserStatistics.map(d => d.date),
    maxValue: maxValue || 1,
  };
});

// Get color for user/friend
const getUserColor = (index) => {
  const colors = [
    '#3B82F6', // blue
    '#10B981', // green
    '#F59E0B', // amber
    '#EF4444', // red
    '#8B5CF6', // purple
    '#EC4899', // pink
  ];
  return colors[index % colors.length];
};

// Format date for display
const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};
</script>

<template>
  <AppLayout title="Habit Statistics">
    <div class="space-y-6">
      <HabitHeader
        title="Statistics"
        :back-route="route('habits.index')"
      />

      <!-- Social Comparison Chart -->
      <Card title="Performance Comparison - Last 30 Days">
        <SocialComparisonChart
          :current-user="currentUser"
          :current-user-statistics="currentUserStatistics"
          :friends-statistics="friendsStatistics"
        />
      </Card>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <Card>
          <div class="text-center">
            <div class="text-4xl font-bold text-green-500">
              {{ currentUserTotals.completed }}
            </div>
            <div class="text-sm text-text-secondary mt-1">Habits Completed</div>
          </div>
        </Card>

        <Card>
          <div class="text-center">
            <div class="text-4xl font-bold text-red-500">
              {{ currentUserTotals.failed }}
            </div>
            <div class="text-sm text-text-secondary mt-1">Habits Failed</div>
          </div>
        </Card>

        <Card>
          <div class="text-center">
            <div class="text-4xl font-bold text-accent">
              {{ currentUserTotals.percentage }}%
            </div>
            <div class="text-sm text-text-secondary mt-1">Success Rate</div>
          </div>
        </Card>
      </div>

      <!-- Social Comparison -->
      <Card title="Social Comparison - Last 30 Days">
        <div class="space-y-4">
          <!-- Current User -->
          <div class="flex items-center justify-between p-4 bg-dark-secondary rounded-lg border-2 border-accent">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-accent flex items-center justify-center text-white font-bold">
                {{ currentUser.name[0].toUpperCase() }}
              </div>
              <div>
                <div class="font-medium text-text-primary">{{ currentUser.name }} (You)</div>
                <div class="text-sm text-text-secondary">
                  {{ currentUserTotals.completed }}/{{ currentUserTotals.total }} completed
                </div>
              </div>
            </div>
            <div class="flex items-center gap-4">
              <div class="w-32 bg-dark-border rounded-full h-3 overflow-hidden">
                <div
                  class="h-full bg-accent transition-all duration-300"
                  :style="{ width: currentUserTotals.percentage + '%' }"
                ></div>
              </div>
              <div class="text-xl font-bold text-accent w-12 text-right">
                {{ currentUserTotals.percentage }}%
              </div>
            </div>
          </div>

          <!-- Friends -->
          <div
            v-for="(friend, index) in sortedFriends"
            :key="friend.user.id"
            class="flex items-center justify-between p-4 bg-dark-secondary rounded-lg"
          >
            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold"
                :style="{ backgroundColor: getUserColor(index) }"
              >
                {{ friend.user.name[0].toUpperCase() }}
              </div>
              <div>
                <div class="font-medium text-text-primary">{{ friend.user.name }}</div>
                <div class="text-sm text-text-secondary">
                  {{ friend.completed }}/{{ friend.total }} completed
                </div>
              </div>
            </div>
            <div class="flex items-center gap-4">
              <div class="w-32 bg-dark-border rounded-full h-3 overflow-hidden">
                <div
                  class="h-full transition-all duration-300"
                  :style="{
                    width: friend.percentage + '%',
                    backgroundColor: getUserColor(index)
                  }"
                ></div>
              </div>
              <div
                class="text-xl font-bold w-12 text-right"
                :style="{ color: getUserColor(index) }"
              >
                {{ friend.percentage }}%
              </div>
            </div>
          </div>

          <!-- No friends message -->
          <div v-if="sortedFriends.length === 0" class="text-center py-8 text-text-secondary">
            No friends to compare with. Add friends to see social comparisons!
          </div>
        </div>
      </Card>

      <!-- Daily Breakdown Chart -->
      <Card title="Daily Progress">
        <div class="overflow-x-auto">
          <div class="min-w-[600px]">
            <!-- Chart -->
            <div class="relative h-64 mb-4">
              <!-- Y-axis labels -->
              <div class="absolute left-0 top-0 bottom-0 w-8 flex flex-col justify-between text-xs text-text-secondary">
                <span>{{ chartData.maxValue }}</span>
                <span>{{ Math.floor(chartData.maxValue / 2) }}</span>
                <span>0</span>
              </div>

              <!-- Chart area -->
              <div class="ml-10 h-full flex items-end gap-1">
                <div
                  v-for="(day, index) in currentUserStatistics"
                  :key="index"
                  class="flex-1 flex flex-col gap-1 items-center group relative"
                >
                  <!-- Completed bar -->
                  <div
                    class="w-full bg-green-500 rounded-t transition-all duration-300 hover:opacity-80"
                    :style="{
                      height: chartData.maxValue > 0
                        ? (day.habitsCompletedCount / chartData.maxValue * 100) + '%'
                        : '0%'
                    }"
                  ></div>

                  <!-- Tooltip -->
                  <div class="absolute bottom-full mb-2 hidden group-hover:block bg-dark-primary border border-dark-border rounded px-2 py-1 text-xs whitespace-nowrap z-10">
                    <div>{{ formatDate(day.date) }}</div>
                    <div class="text-green-500">✓ {{ day.habitsCompletedCount }}</div>
                    <div class="text-red-500">✗ {{ day.habitsFailedCount }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- X-axis labels -->
            <div class="ml-10 flex justify-between text-xs text-text-secondary">
              <span>{{ formatDate(dateRange.minDate) }}</span>
              <span>{{ formatDate(dateRange.maxDate) }}</span>
            </div>
          </div>
        </div>

        <!-- Legend -->
        <div class="mt-4 flex items-center justify-center gap-4 text-sm">
          <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-green-500 rounded"></div>
            <span class="text-text-secondary">Completed</span>
          </div>
        </div>
      </Card>
    </div>
  </AppLayout>
</template>

