<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  options: {
    type: Array,
    default: () => [],
  },
  displayFormat: {
    type: Function,
    default: (option) => option,
  },
  filterFunction: {
    type: Function,
    default: (option, searchTerm) => {
      const display = typeof option === 'string' ? option : JSON.stringify(Object.values(option));
      return display.toLowerCase().includes(searchTerm.toLowerCase());
    },
  },
  placeholder: {
    type: String,
    default: 'Search...',
  },
  maxResults: {
    type: Number,
    default: 3,
  },
  error: {
    type: String,
    default: null,
  },
  showOnFocus: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:modelValue', 'select']);

const isOpen = ref(false);
const inputRef = ref(null);

const filteredOptions = computed(() => {
  // Si showOnFocus es true y no hay texto, mostrar todas las opciones
  if (props.showOnFocus && !props.modelValue) {
    return props.options.slice(0, props.maxResults);
  }

  // Si no hay texto escrito, no mostrar opciones (comportamiento por defecto)
  if (!props.modelValue) return [];

  return props.options
    .filter(option => props.filterFunction(option, props.modelValue))
    .slice(0, props.maxResults);
});

const updateValue = (value) => {
  emit('update:modelValue', value);
  isOpen.value = true;
};

const selectOption = (option) => {
  emit('select', option);
  emit('update:modelValue', '');
  isOpen.value = false;
  inputRef.value?.blur();
};

const onFocus = () => {
  // Si showOnFocus es true, siempre mostrar al hacer focus
  if (props.showOnFocus) {
    isOpen.value = true;
  } else if (props.modelValue) {
    // Comportamiento por defecto: solo mostrar si hay texto
    isOpen.value = true;
  }
};

const onBlur = () => {
  // Delay to allow click on option
  setTimeout(() => {
    isOpen.value = false;
  }, 200);
};
</script>

<template>
  <div class="relative">
    <input
      ref="inputRef"
      type="text"
      :value="modelValue"
      @input="updateValue($event.target.value)"
      @focus="onFocus"
      @blur="onBlur"
      :placeholder="placeholder"
      class="input-modern w-full"
      :class="error ? 'border-red-600 focus:border-red-600 focus:ring-red-600' : ''"
    />

    <div
      v-if="isOpen && filteredOptions.length > 0"
      class="absolute z-10 w-full mt-1 bg-dark-secondary border border-dark-border rounded-modern shadow-modern max-h-48 overflow-y-auto"
    >
      <button
        v-for="(option, index) in filteredOptions"
        :key="index"
        type="button"
        @click="selectOption(option)"
        class="w-full px-3 py-2 text-left text-text-primary hover:bg-accent hover:text-dark-primary focus:bg-accent focus:text-dark-primary focus:outline-none border-b border-dark-border last:border-b-0 transition-colors duration-modern"
      >
        {{ displayFormat(option) }}
      </button>
    </div>

    <p v-if="error" class="mt-1 text-sm text-red-600">
      {{ error }}
    </p>
  </div>
</template>
