<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import Button from '@/Components/Button.vue';
import Modal from '@/Components/Modal.vue';
import { Home, ClipboardList, Users, Calendar, Menu, X } from 'lucide-vue-next';

defineProps({
  title: {
    type: String,
    default: 'App',
  },
});

const page = usePage();
const showLogoutModal = ref(false);
const mobileMenuOpen = ref(false);

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

const closeMobileMenu = () => {
  mobileMenuOpen.value = false;
};
</script>

<template>
  <div class="min-h-screen bg-white flex">
    <!-- Mobile menu overlay -->
    <div
      v-if="mobileMenuOpen"
      class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"
      @click="closeMobileMenu"
    ></div>

    <!-- Sidebar -->
    <aside
      class="w-48 bg-white border-r border-gray-200 fixed inset-y-0 left-0 z-50 transform transition-transform duration-200 ease-in-out md:relative md:translate-x-0"
      :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <div class="px-4 py-4 border-b border-gray-200 h-16 flex items-center justify-between">
        <Link
           prefetch :href="route('index')" class="text-lg font-medium text-gray-900">
          Reckon
        </Link>
        <button
          @click="closeMobileMenu"
          class="md:hidden text-gray-700 hover:text-gray-900"
        >
          <X :size="20" />
        </button>
      </div>

      <nav>
        <Link
           prefetch
          :href="route('index')"
          @click="closeMobileMenu"
          class="flex items-center gap-2 px-4 py-3 text-sm text-gray-700 border-b border-gray-200"
          :class="{ 'text-gray-900 underline decoration-dotted decoration-2 underline-offset-4': route().current('index') }"
        >
          <Home :size="16" />
          Home
        </Link>
        <Link
           prefetch
          :href="route('work-orders.index')"
          @click="closeMobileMenu"
          class="flex items-center gap-2 px-4 py-3 text-sm text-gray-700 border-b border-gray-200"
          :class="{ 'text-gray-900 underline decoration-dotted decoration-2 underline-offset-4': route().current('work-orders.*') }"
        >
          <ClipboardList :size="16" />
          Work orders
        </Link>
        <Link
           prefetch
          :href="route('friends.index')"
          @click="closeMobileMenu"
          class="flex items-center gap-2 px-4 py-3 text-sm text-gray-700 border-b border-gray-200"
          :class="{ 'text-gray-900 underline decoration-dotted decoration-2 underline-offset-4': route().current('friends.*') }"
        >
          <Users :size="16" />
          Friends
        </Link>
        <Link
           prefetch
          :href="route('habits.index')"
          @click="closeMobileMenu"
          class="flex items-center gap-2 px-4 py-3 text-sm text-gray-700 border-b border-gray-200"
          :class="{ 'text-gray-900 underline decoration-dotted decoration-2 underline-offset-4': route().current('habits.*') }"
        >
          <Calendar :size="16" />
          Habits
        </Link>
      </nav>
    </aside>

    <!-- Main content -->
    <div class="flex-1 flex flex-col w-full md:w-auto">
      <header class="border-b border-gray-200">
        <div class="px-4 md:px-6 py-4 h-16 flex items-center justify-between md:justify-end">
          <button
            @click="mobileMenuOpen = true"
            class="md:hidden text-gray-700 hover:text-gray-900"
          >
            <Menu :size="24" />
          </button>

          <div class="flex items-center gap-2 md:gap-4">
            <span class="text-xs md:text-sm text-gray-600 truncate max-w-[120px] md:max-w-none">
              {{ page.props.auth.user?.email }}
            </span>
            <Button @click="logout" variant="secondary" size="sm">
              Sign out
            </Button>
          </div>
        </div>
      </header>

      <main class="flex-1 px-4 md:px-6 py-6 md:py-8">
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
