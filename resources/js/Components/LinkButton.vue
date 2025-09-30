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
  const base = 'inline-block font-medium border focus:outline-none focus:ring-2 focus:ring-offset-2 no-underline';

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
  <Link v-if="props.href" :href="props.href" :method="props.method" :data="props.data" :class="classes" :prefetch="props.prefetch">
    <slot />
  </Link>
  <button v-else :class="classes" @click="$emit('click')">
    <slot />
  </button>
</template>
