<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  href: String,
  method: String,
  data: Object,
  prefetch: Boolean,
  variant: {
    type: String,
    default: 'secondary',
    validator: (value) => ['primary', 'secondary', 'danger'].includes(value),
  },
  size: {
    type: String,
    default: 'sm',
    validator: (value) => ['sm', 'md'].includes(value),
  },
});

defineEmits(['click']);

const classes = computed(() => {
  const base = 'inline-block font-medium rounded-modern focus:outline-none transition-all duration-modern ease-in-out hover:scale-[1.02] no-underline';

  const variants = {
    primary: 'bg-accent text-dark-primary hover:bg-gradient-to-r hover:from-accent hover:to-accent-hover hover:shadow-modern-hover active:bg-accent-active',
    secondary: 'bg-dark-secondary border border-dark-input-border text-text-primary hover:border-accent hover:shadow-modern-hover',
    danger: 'bg-red-600 text-text-primary hover:bg-red-700 hover:shadow-modern-hover active:bg-red-800',
  };

  const sizes = {
    sm: 'px-5 py-2 text-sm h-10 flex items-center',
    md: 'px-5 py-3 text-base h-12 flex items-center',
  };

  return `${base} ${variants[props.variant]} ${sizes[props.size]}`;
});
</script>

<template>
  <Link v-if="props.href" :href="props.href" :method="props.method" :data="props.data" :class="classes" :prefetch="props.prefetch">
    <slot />
  </Link>
  <button v-else :class="classes" @click="$emit('click')">
    <slot />
  </button>
</template>
