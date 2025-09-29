<script setup>
import { computed } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'danger'].includes(value),
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md'].includes(value),
  },
  type: {
    type: String,
    default: 'button',
  },
});

const classes = computed(() => {
  const base = 'font-medium border focus:outline-none focus:ring-2 focus:ring-offset-2';

  const variants = {
    primary: 'bg-gray-900 border-gray-900 text-white hover:bg-gray-800 focus:ring-gray-500',
    secondary: 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-gray-500',
    danger: 'bg-red-600 border-red-600 text-white hover:bg-red-700 focus:ring-red-500',
  };

  const sizes = {
    sm: 'px-3 py-1 text-sm',
    md: 'px-4 py-2 text-sm',
  };

  return `${base} ${variants[props.variant]} ${sizes[props.size]}`;
});
</script>

<template>
  <button :type="type" :class="classes">
    <slot />
  </button>
</template>
