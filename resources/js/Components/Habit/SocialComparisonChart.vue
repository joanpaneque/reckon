<script setup>
import { computed, ref, onMounted, watch } from 'vue';
import { Line } from 'vue-chartjs';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  CategoryScale,
  LinearScale,
  PointElement,
  Filler
} from 'chart.js';

// Register Chart.js components
ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  CategoryScale,
  LinearScale,
  PointElement,
  Filler
);

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
});

// State for showing friends
const showFriends = ref(false);

// Chart mode: 'net-score', 'daily', 'success-rate'
const chartMode = ref('net-score');

// Colors palette
const colors = [
  { primary: 'rgba(59, 130, 246, 1)', background: 'rgba(59, 130, 246, 0.1)' }, // blue
  { primary: 'rgba(16, 185, 129, 1)', background: 'rgba(16, 185, 129, 0.1)' }, // green
  { primary: 'rgba(245, 158, 11, 1)', background: 'rgba(245, 158, 11, 0.1)' }, // amber
  { primary: 'rgba(239, 68, 68, 1)', background: 'rgba(239, 68, 68, 0.1)' }, // red
  { primary: 'rgba(139, 92, 246, 1)', background: 'rgba(139, 92, 246, 0.1)' }, // purple
  { primary: 'rgba(236, 72, 153, 1)', background: 'rgba(236, 72, 153, 0.1)' }, // pink
];

// Format date for display
const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

// Calculate cumulative net score based on daily completion percentage
const calculateCumulativeNet = (statistics) => {
  let cumulative = 0;
  return statistics.map(day => {
    // Calculate daily completion percentage
    const total = day.habitsCompletedCount + day.habitsFailedCount;
    const percentage = total > 0 ? (day.habitsCompletedCount / total) * 100 : 0;

    // Add daily score: -9 + floor(percentage/10)
    const dailyScore = -9 + Math.floor(percentage / 10);
    cumulative += dailyScore;

    return cumulative;
  });
};

// Calculate daily values (not cumulative)
const calculateDaily = (statistics) => {
  return statistics.map(day => day.habitsCompletedCount);
};

// Calculate success rate percentage
const calculateSuccessRate = (statistics) => {
  return statistics.map(day => {
    const total = day.habitsCompletedCount + day.habitsFailedCount;
    return total > 0 ? Math.round((day.habitsCompletedCount / total) * 100) : 0;
  });
};

// Prepare chart data based on selected mode
const chartData = computed(() => {
  const labels = props.currentUserStatistics.map(d => formatDate(d.date));

  const datasets = [];

  // Calculate data based on selected mode
  let currentUserData;
  switch (chartMode.value) {
    case 'daily':
      currentUserData = calculateDaily(props.currentUserStatistics);
      break;
    case 'success-rate':
      currentUserData = calculateSuccessRate(props.currentUserStatistics);
      break;
    case 'net-score':
    default:
      currentUserData = calculateCumulativeNet(props.currentUserStatistics);
      break;
  }

  datasets.push({
    label: `${props.currentUser.name} (You)`,
    data: currentUserData,
    borderColor: 'rgba(147, 197, 253, 1)', // accent color
    backgroundColor: 'rgba(147, 197, 253, 0.2)',
    borderWidth: 3,
    tension: 0.4,
    fill: chartMode.value !== 'success-rate', // Don't fill for percentage
    pointRadius: 4,
    pointHoverRadius: 6,
    pointBackgroundColor: 'rgba(147, 197, 253, 1)',
    pointBorderColor: '#fff',
    pointBorderWidth: 2,
  });

  // Add friends datasets only if showFriends is true
  if (showFriends.value) {
    props.friendsStatistics.forEach((friend, index) => {
      const color = colors[index % colors.length];

      let friendData;
      switch (chartMode.value) {
        case 'daily':
          friendData = calculateDaily(friend.statistics);
          break;
        case 'success-rate':
          friendData = calculateSuccessRate(friend.statistics);
          break;
        case 'net-score':
        default:
          friendData = calculateCumulativeNet(friend.statistics);
          break;
      }

      datasets.push({
        label: friend.user.name,
        data: friendData,
        borderColor: color.primary,
        backgroundColor: color.background,
        borderWidth: 2,
        tension: 0.4,
        fill: chartMode.value !== 'success-rate',
        pointRadius: 3,
        pointHoverRadius: 5,
        pointBackgroundColor: color.primary,
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
      });
    });
  }

  return {
    labels,
    datasets,
  };
});

// Chart options
const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    mode: 'index',
    intersect: false,
  },
  plugins: {
    legend: {
      display: true,
      position: 'top',
      labels: {
        color: '#9CA3AF',
        padding: 15,
        font: {
          size: 12,
          family: "'Inter', sans-serif",
        },
        usePointStyle: true,
        pointStyle: 'circle',
      },
    },
    tooltip: {
      backgroundColor: 'rgba(17, 24, 39, 0.95)',
      titleColor: '#F3F4F6',
      bodyColor: '#D1D5DB',
      borderColor: '#374151',
      borderWidth: 1,
      padding: 12,
      displayColors: true,
      callbacks: {
        label: function(context) {
          let label = context.dataset.label || '';
          if (label) {
            label += ': ';
          }
          const value = context.parsed.y;

          // Format based on mode
          if (chartMode.value === 'success-rate') {
            label += value + '%';
          } else if (chartMode.value === 'daily') {
            label += value + ' habits';
          } else {
            const sign = value >= 0 ? '+' : '';
            label += sign + value + ' net score';
          }
          return label;
        },
      },
    },
  },
  scales: {
    x: {
      grid: {
        color: 'rgba(55, 65, 81, 0.3)',
        drawBorder: false,
      },
      ticks: {
        color: '#9CA3AF',
        font: {
          size: 11,
        },
        maxRotation: 45,
        minRotation: 0,
      },
    },
    y: {
      min: chartMode.value === 'success-rate' ? 0 : undefined,
      max: chartMode.value === 'success-rate' ? 100 : undefined,
      grid: {
        color: function(context) {
          // Make the zero line more visible for net-score mode
          if (chartMode.value === 'net-score' && context.tick.value === 0) {
            return 'rgba(156, 163, 175, 0.5)';
          }
          return 'rgba(55, 65, 81, 0.3)';
        },
        drawBorder: false,
        lineWidth: function(context) {
          // Make the zero line thicker for net-score mode
          if (chartMode.value === 'net-score' && context.tick.value === 0) {
            return 2;
          }
          return 1;
        },
      },
      ticks: {
        color: '#9CA3AF',
        font: {
          size: 11,
        },
        stepSize: chartMode.value === 'success-rate' ? 10 : 1,
        callback: function(value) {
          if (chartMode.value === 'success-rate') {
            return value + '%';
          }
          return value;
        },
      },
    },
  },
}));
</script>

<template>
  <div class="w-full space-y-4">
    <!-- Controls row -->
    <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
      <!-- Mode buttons -->
      <div class="flex gap-2">
        <button
          @click="chartMode = 'net-score'"
          :class="[
            'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200',
            chartMode === 'net-score'
              ? 'bg-accent text-white shadow-lg'
              : 'bg-dark-secondary text-text-secondary hover:bg-dark-border hover:text-text-primary'
          ]"
        >
          Net Score
        </button>
        <button
          @click="chartMode = 'daily'"
          :class="[
            'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200',
            chartMode === 'daily'
              ? 'bg-accent text-white shadow-lg'
              : 'bg-dark-secondary text-text-secondary hover:bg-dark-border hover:text-text-primary'
          ]"
        >
          Daily
        </button>
        <button
          @click="chartMode = 'success-rate'"
          :class="[
            'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200',
            chartMode === 'success-rate'
              ? 'bg-accent text-white shadow-lg'
              : 'bg-dark-secondary text-text-secondary hover:bg-dark-border hover:text-text-primary'
          ]"
        >
          Success %
        </button>
      </div>

      <!-- Show friends checkbox -->
      <label
        v-if="friendsStatistics.length > 0"
        class="flex items-center gap-2 cursor-pointer group"
      >
        <input
          type="checkbox"
          v-model="showFriends"
          class="w-4 h-4 rounded border-dark-border bg-dark-secondary text-accent focus:ring-accent focus:ring-2 cursor-pointer"
        />
        <span class="text-sm text-text-secondary group-hover:text-text-primary transition-colors">
          Show friends
        </span>
      </label>
    </div>

    <!-- Mode description -->
    <div class="text-sm text-text-secondary">
      <span v-if="chartMode === 'net-score'">
        📊 Cumulative score: each day adds -9 + floor(completion%/10)
      </span>
      <span v-else-if="chartMode === 'daily'">
        📅 Daily habits completed (not cumulative)
      </span>
      <span v-else-if="chartMode === 'success-rate'">
        📈 Success rate: percentage of habits completed each day
      </span>
    </div>

    <!-- Chart -->
    <div style="height: 400px;">
      <Line :data="chartData" :options="chartOptions" />
    </div>
  </div>
</template>

