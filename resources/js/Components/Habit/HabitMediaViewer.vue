<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Image, Video, X, ChevronLeft, ChevronRight } from 'lucide-vue-next';

const props = defineProps({
  habit: Object,
  date: Date,
  users: Array,
});

const showLightbox = ref(false);
const selectedMedia = ref(null);
const selectedUserIndex = ref(0);

// Get all media for this date from all users
const mediaItems = computed(() => {
  const checkDate = new Date(props.date);
  checkDate.setHours(0, 0, 0, 0);

  const items = [];

  console.log('🎬 HabitMediaViewer - Looking for media:', {
    habitName: props.habit.name,
    date: checkDate.toLocaleDateString(),
    users: props.users.map(u => u.name),
    allUserHabits: props.habit.all_user_habits
  });

  props.users.forEach(user => {
    const userHabit = props.habit.all_user_habits?.find(uh => {
      if (uh.user_id !== user.id || !uh.completed_at) return false;

      // Compare only the date part (YYYY-MM-DD) to avoid timezone issues
      const completedDateStr = uh.completed_at.split('T')[0];
      const checkDateStr = checkDate.toISOString().split('T')[0];

      return completedDateStr === checkDateStr;
    });

    console.log(`  👤 ${user.name}:`, {
      found: !!userHabit,
      hasMedia: !!userHabit?.media_path,
      completedAt: userHabit?.completed_at,
      checkingFor: checkDate.toISOString().split('T')[0],
      userHabit
    });

    if (userHabit && userHabit.media_path) {
      items.push({
        user: user,
        userHabit: userHabit,
      });
    }
  });

  console.log('✅ Total media items found:', items.length);

  return items;
});

const openLightbox = (index) => {
  selectedUserIndex.value = index;
  selectedMedia.value = mediaItems.value[index];
  showLightbox.value = true;
};

const closeLightbox = () => {
  showLightbox.value = false;
  selectedMedia.value = null;
};

const previousMedia = () => {
  if (selectedUserIndex.value > 0) {
    selectedUserIndex.value--;
    selectedMedia.value = mediaItems.value[selectedUserIndex.value];
  }
};

const nextMedia = () => {
  if (selectedUserIndex.value < mediaItems.value.length - 1) {
    selectedUserIndex.value++;
    selectedMedia.value = mediaItems.value[selectedUserIndex.value];
  }
};

// Keyboard navigation
const handleKeyDown = (event) => {
  if (!showLightbox.value) return;

  switch (event.key) {
    case 'Escape':
      closeLightbox();
      break;
    case 'ArrowLeft':
      previousMedia();
      break;
    case 'ArrowRight':
      nextMedia();
      break;
  }
};

// Watch for lightbox state to manage keyboard listener
watch(showLightbox, (newVal) => {
  if (newVal) {
    window.addEventListener('keydown', handleKeyDown);
    // Prevent body scroll when lightbox is open
    document.body.style.overflow = 'hidden';
  } else {
    window.removeEventListener('keydown', handleKeyDown);
    // Restore body scroll
    document.body.style.overflow = '';
  }
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
  document.body.style.overflow = '';
});
</script>

<template>
  <!-- Media Gallery - Enhanced Design -->
  <div v-if="mediaItems.length > 0" class="mt-3 pt-3 border-t-2 border-blue-100 bg-gradient-to-r from-blue-50 to-purple-50 p-3">
    <!-- Header with counter badge -->
    <div class="flex items-center gap-2 mb-3">
      <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center">
        <Image :size="16" class="text-white" />
      </div>
      <div>
        <div class="text-sm font-bold text-gray-800">Shared Moments</div>
        <div class="text-xs text-gray-600">{{ mediaItems.length }} {{ mediaItems.length === 1 ? 'photo' : 'photos' }} today</div>
      </div>
    </div>

    <!-- Gallery Grid -->
    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-2">
      <div
        v-for="(item, index) in mediaItems"
        :key="item.userHabit.id"
        @click="openLightbox(index)"
        class="group relative aspect-square rounded-xl overflow-hidden cursor-pointer transform transition-all duration-300 hover:scale-105 hover:shadow-xl hover:z-10"
      >
        <!-- Thumbnail with overlay effect -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

        <img
          v-if="item.userHabit.media_type === 'image'"
          :src="`/storage/${item.userHabit.media_path}`"
          :alt="`${item.user.name}'s media`"
          class="w-full h-full object-cover"
        />
        <div
          v-else
          class="w-full h-full bg-gradient-to-br from-purple-900 to-blue-900 flex items-center justify-center"
        >
          <Video :size="32" class="text-white/80" />
        </div>

        <!-- User avatar badge -->
        <div class="absolute top-2 left-2 w-6 h-6 rounded-full bg-white shadow-md flex items-center justify-center text-xs font-bold text-blue-600 border-2 border-blue-200">
          {{ item.user.name.charAt(0).toUpperCase() }}
        </div>

        <!-- Media type badge -->
        <div class="absolute top-2 right-2 bg-black/70 backdrop-blur-sm rounded-full p-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
          <Image v-if="item.userHabit.media_type === 'image'" :size="12" class="text-white" />
          <Video v-else :size="12" class="text-white" />
        </div>

        <!-- User name on hover -->
        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-2 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
          <div class="text-xs font-semibold text-white truncate">
            {{ item.user.name }}
          </div>
          <div v-if="item.userHabit.media_caption" class="text-[10px] text-white/80 truncate">
            {{ item.userHabit.media_caption }}
          </div>
        </div>

        <!-- Ripple effect on click -->
        <div class="absolute inset-0 pointer-events-none">
          <div class="absolute inset-0 bg-white opacity-0 group-active:opacity-20 transition-opacity duration-150"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Lightbox Modal - Enhanced -->
  <Transition
    enter-active-class="transition-opacity duration-300"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition-opacity duration-300"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="showLightbox && selectedMedia"
      class="fixed inset-0 bg-black/95 backdrop-blur-sm z-50 flex items-center justify-center"
      @click="closeLightbox"
    >
      <!-- Close button with enhanced design -->
      <button
        @click="closeLightbox"
        class="absolute top-4 right-4 p-3 text-white bg-black/30 hover:bg-white/20 rounded-full transition-all duration-200 hover:scale-110 z-10 backdrop-blur-sm"
        title="Close (ESC)"
      >
        <X :size="24" />
      </button>

      <!-- Navigation buttons with enhanced design -->
      <button
        v-if="selectedUserIndex > 0"
        @click.stop="previousMedia"
        class="absolute left-4 top-1/2 -translate-y-1/2 p-4 text-white bg-black/30 hover:bg-white/20 rounded-full transition-all duration-200 hover:scale-110 z-10 backdrop-blur-sm"
        title="Previous"
      >
        <ChevronLeft :size="32" />
      </button>
      <button
        v-if="selectedUserIndex < mediaItems.length - 1"
        @click.stop="nextMedia"
        class="absolute right-4 top-1/2 -translate-y-1/2 p-4 text-white bg-black/30 hover:bg-white/20 rounded-full transition-all duration-200 hover:scale-110 z-10 backdrop-blur-sm"
        title="Next"
      >
        <ChevronRight :size="32" />
      </button>

      <!-- Thumbnail strip at top -->
      <div class="absolute top-4 left-1/2 -translate-x-1/2 max-w-2xl overflow-x-auto z-10">
        <div class="flex gap-2 px-4 py-2 bg-black/30 backdrop-blur-sm rounded-full">
          <div
            v-for="(item, index) in mediaItems"
            :key="item.userHabit.id"
            @click.stop="selectedUserIndex = index; selectedMedia = item"
            :class="[
              'w-12 h-12 rounded-lg overflow-hidden cursor-pointer transition-all duration-200 flex-shrink-0',
              index === selectedUserIndex
                ? 'ring-2 ring-white scale-110'
                : 'opacity-50 hover:opacity-100 hover:scale-105'
            ]"
          >
            <img
              v-if="item.userHabit.media_type === 'image'"
              :src="`/storage/${item.userHabit.media_path}`"
              class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full bg-purple-900 flex items-center justify-center">
              <Video :size="20" class="text-white" />
            </div>
          </div>
        </div>
      </div>

      <!-- Media content -->
      <Transition
        mode="out-in"
        enter-active-class="transition-all duration-300"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition-all duration-300"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div
          :key="selectedUserIndex"
          @click.stop
          class="max-w-6xl max-h-[90vh] w-full mx-4 flex flex-col pt-20 pb-4"
        >
          <!-- Media -->
          <div class="flex-1 flex items-center justify-center mb-4">
            <img
              v-if="selectedMedia.userHabit.media_type === 'image'"
              :src="`/storage/${selectedMedia.userHabit.media_path}`"
              :alt="`${selectedMedia.user.name}'s media`"
              class="max-w-full max-h-[calc(90vh-200px)] object-contain rounded-lg shadow-2xl"
            />
            <video
              v-else
              :src="`/storage/${selectedMedia.userHabit.media_path}`"
              controls
              autoplay
              class="max-w-full max-h-[calc(90vh-200px)] rounded-lg shadow-2xl"
            />
          </div>

          <!-- Caption card -->
          <div class="bg-gradient-to-r from-white/10 to-white/5 backdrop-blur-md rounded-2xl p-5 shadow-xl border border-white/10">
            <div class="flex items-start gap-4">
              <!-- User avatar -->
              <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center text-white font-bold text-lg shadow-lg flex-shrink-0">
                {{ selectedMedia.user.name.charAt(0).toUpperCase() }}
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <div class="text-white font-semibold text-lg">
                    {{ selectedMedia.user.name }}
                  </div>
                  <div class="px-2 py-0.5 bg-blue-500/30 rounded-full text-xs text-blue-200 font-medium">
                    {{ selectedUserIndex + 1 }} / {{ mediaItems.length }}
                  </div>
                </div>
                <div v-if="selectedMedia.userHabit.media_caption" class="text-gray-200 text-sm leading-relaxed">
                  {{ selectedMedia.userHabit.media_caption }}
                </div>
                <div v-else class="text-gray-400 text-sm italic">
                  No caption added
                </div>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </Transition>
</template>

