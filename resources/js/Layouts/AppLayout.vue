<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import Button from '@/Components/Button.vue';
import Modal from '@/Components/Modal.vue';
import { Home, ClipboardList, Users, Calendar } from 'lucide-vue-next';

defineProps({
  title: {
    type: String,
    default: 'App',
  },
});

const page = usePage();
const showLogoutModal = ref(false);

const logout = () => {
  showLogoutModal.value = true;
};

const confirmLogout = () => {
  router.post(route('logout'));
  showLogoutModal.value = false;
};

const cancelLogout = () => {
  showLogoutModal.value = false;
};
</script>

<template>
  <div class="min-h-screen bg-white flex">
    <!-- Sidebar -->
    <aside class="w-48 border-r border-gray-200">
      <div class="px-4 py-4 border-b border-gray-200 h-16 flex items-center">
        <Link
           prefetch :href="route('index')" class="text-lg font-medium text-gray-900">
          Reckon
        </Link>
      </div>

      <nav>
        <Link
           prefetch
          :href="route('index')"
          class="flex items-center gap-2 px-4 py-3 text-sm text-gray-700 border-b border-gray-200"
          :class="{ 'text-gray-900 underline decoration-dotted decoration-2 underline-offset-4': route().current('index') }"
        >
          <Home :size="16" />
          Home
        </Link>
        <Link
           prefetch
          :href="route('work-orders.index')"
          class="flex items-center gap-2 px-4 py-3 text-sm text-gray-700 border-b border-gray-200"
          :class="{ 'text-gray-900 underline decoration-dotted decoration-2 underline-offset-4': route().current('work-orders.*') }"
        >
          <ClipboardList :size="16" />
          Work orders
        </Link>
        <Link
           prefetch
          :href="route('friends.index')"
          class="flex items-center gap-2 px-4 py-3 text-sm text-gray-700 border-b border-gray-200"
          :class="{ 'text-gray-900 underline decoration-dotted decoration-2 underline-offset-4': route().current('friends.*') }"
        >
          <Users :size="16" />
          Friends
        </Link>
        <Link
           prefetch
          :href="route('habits.index')"
          class="flex items-center gap-2 px-4 py-3 text-sm text-gray-700 border-b border-gray-200"
          :class="{ 'text-gray-900 underline decoration-dotted decoration-2 underline-offset-4': route().current('habits.*') }"
        >
          <Calendar :size="16" />
          Habits
        </Link>
      </nav>
    </aside>

    <!-- Main content -->
    <div class="flex-1 flex flex-col">
      <header class="border-b border-gray-200">
        <div class="px-6 py-4 h-16 flex items-center justify-end">
          <div class="flex items-center gap-4">
            <span class="text-sm text-gray-600">
              {{ page.props.auth.user?.email }}
            </span>
            <Button @click="logout" variant="secondary" size="sm">
              Sign out
            </Button>
          </div>
        </div>
      </header>

      <main class="flex-1 px-6 py-8">
        <slot />
      </main>
    </div>

    <!-- Logout Modal -->
    <Modal
      :show="showLogoutModal"
      title="Sign Out"
      message="Are you sure you want to sign out?"
      confirm-text="Sign Out"
      cancel-text="Cancel"
      confirm-variant="danger"
      @confirm="confirmLogout"
      @cancel="cancelLogout"
      @close="cancelLogout"
    />
  </div>
</template>
