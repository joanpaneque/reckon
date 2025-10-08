<script setup>
import { computed, onMounted, onUnmounted } from 'vue';
import Button from '@/Components/Button.vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    required: true,
  },
  message: {
    type: String,
    required: true,
  },
  confirmText: {
    type: String,
    default: 'Confirm',
  },
  cancelText: {
    type: String,
    default: 'Cancel',
  },
  confirmVariant: {
    type: String,
    default: 'danger',
    validator: (value) => ['primary', 'secondary', 'danger'].includes(value),
  },
});

const emit = defineEmits(['confirm', 'cancel', 'close']);

const handleConfirm = () => {
  emit('confirm');
  emit('close');
};

const handleCancel = () => {
  emit('cancel');
  emit('close');
};

const handleEscapeKey = (e) => {
  if (e.key === 'Escape' && props.show) {
    handleCancel();
  }
};

onMounted(() => {
  document.addEventListener('keydown', handleEscapeKey);
});

onUnmounted(() => {
  document.removeEventListener('keydown', handleEscapeKey);
});
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-opacity duration-modern"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-modern"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center">
        <!-- Backdrop -->
        <div
          class="absolute inset-0 bg-black bg-opacity-50"
          @click="handleCancel"
        ></div>

        <!-- Modal -->
        <Transition
          enter-active-class="transition-all duration-modern"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition-all duration-modern"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <div v-if="show" class="relative card-modern max-w-md w-full mx-4">
            <div class="mb-4">
              <h3 class="text-lg font-semibold text-text-primary mb-2">{{ title }}</h3>
              <p class="text-sm text-text-secondary">{{ message }}</p>
            </div>

            <div class="flex items-center justify-end gap-3">
              <Button
                variant="secondary"
                size="sm"
                @click="handleCancel"
              >
                {{ cancelText }}
              </Button>
              <Button
                :variant="confirmVariant"
                size="sm"
                @click="handleConfirm"
              >
                {{ confirmText }}
              </Button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>
