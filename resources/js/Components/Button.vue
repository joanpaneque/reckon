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
  const base = 'font-medium rounded-modern focus:outline-none transition-all duration-modern ease-in-out hover:scale-[1.02]';

  const variants = {
    primary: 'bg-accent text-dark-primary hover:bg-gradient-to-r hover:from-accent hover:to-accent-hover active:bg-accent-active hover:shadow-modern-hover disabled:bg-disabled-bg disabled:text-disabled-text disabled:hover:scale-100 disabled:hover:shadow-none',
    secondary: 'bg-dark-secondary border border-dark-input-border text-text-primary hover:border-accent hover:shadow-modern-hover disabled:bg-disabled-bg disabled:text-disabled-text disabled:hover:scale-100 disabled:hover:shadow-none',
    danger: 'bg-red-600 text-text-primary hover:bg-red-700 hover:shadow-modern-hover active:bg-red-800 disabled:bg-disabled-bg disabled:text-disabled-text disabled:hover:scale-100 disabled:hover:shadow-none',
  };

  const sizes = {
    sm: 'px-5 py-2 text-sm h-10',
    md: 'px-5 py-3 text-base h-12',
  };

  return `${base} ${variants[props.variant]} ${sizes[props.size]}`;
});
</script>

<template>
  <button :type="type" :class="classes">
    <slot />
  </button>
</template>
