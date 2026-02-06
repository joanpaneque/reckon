<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import LinkButton from '@/Components/LinkButton.vue';
import HabitHeader from '@/Components/Habit/HabitHeader.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  date: {
    type: String,
    required: true,
  },
  existingTodos: {
    type: Array,
    default: () => [],
  },
  buttonLabel: {
    type: String,
    default: "Editar todo's",
  },
});

const form = useForm({
  date: props.date,
  todos: props.existingTodos.length > 0 
    ? props.existingTodos.map(t => ({ 
        id: t.id,
        title: t.title, 
        description: t.description || '' 
      }))
    : [{ title: '', description: '' }],
});

const addTodo = () => {
  form.todos.push({ title: '', description: '' });
};

const removeTodo = (index) => {
  if (form.todos.length > 1) {
    form.todos.splice(index, 1);
  }
};

const submit = () => {
  // Filter out empty todos
  form.todos = form.todos.filter(todo => todo.title.trim() !== '');
  
  if (form.todos.length === 0) {
    form.setError('todos', 'Debe agregar al menos un todo.');
    return;
  }

  form.put(route('todos.update', props.date));
};
</script>

<template>
  <AppLayout :title="buttonLabel">
    <div class="space-y-6">
      <HabitHeader
        :title="buttonLabel"
        :back-route="route('todos.index')"
      />

      <Card>
        <form @submit.prevent="submit" class="space-y-6">
          <div>
            <label class="block text-sm font-medium text-text-secondary mb-1">Fecha</label>
            <Input 
              v-model="form.date" 
              type="date" 
              :error="form.errors.date"
              disabled
            />
            <p v-if="form.errors.date" class="mt-1 text-sm text-red-600">
              {{ form.errors.date }}
            </p>
          </div>

          <div>
            <div class="flex items-center justify-between mb-2">
              <label class="block text-sm font-medium text-text-secondary">Todos</label>
              <Button type="button" @click="addTodo" variant="secondary" size="sm">
                + Agregar Todo
              </Button>
            </div>

            <div v-if="form.errors.todos" class="mb-2">
              <p class="text-sm text-red-600">{{ form.errors.todos }}</p>
            </div>

            <div class="space-y-3">
              <div
                v-for="(todo, index) in form.todos"
                :key="index"
                class="p-4 border border-dark-border rounded-modern space-y-3"
              >
                <div class="flex items-start justify-between gap-2">
                  <div class="flex-1 space-y-3">
                    <div>
                      <label class="block text-sm font-medium text-text-secondary mb-1">
                        Título <span class="text-red-600">*</span>
                      </label>
                      <Input
                        v-model="todo.title"
                        :error="form.errors[`todos.${index}.title`]"
                        placeholder="Ej: Comprar leche"
                      />
                      <p v-if="form.errors[`todos.${index}.title`]" class="mt-1 text-sm text-red-600">
                        {{ form.errors[`todos.${index}.title`] }}
                      </p>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-text-secondary mb-1">
                        Descripción (opcional)
                      </label>
                      <textarea
                        v-model="todo.description"
                        class="input-modern w-full resize-none"
                        :class="{ 'border-red-600 focus:border-red-600 focus:ring-red-600': form.errors[`todos.${index}.description`] }"
                        rows="2"
                        placeholder="Descripción adicional..."
                      />
                      <p v-if="form.errors[`todos.${index}.description`]" class="mt-1 text-sm text-red-600">
                        {{ form.errors[`todos.${index}.description`] }}
                      </p>
                    </div>
                  </div>

                  <Button
                    v-if="form.todos.length > 1"
                    type="button"
                    @click="removeTodo(index)"
                    variant="danger"
                    size="sm"
                    class="flex-shrink-0"
                  >
                    Eliminar
                  </Button>
                </div>
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t border-dark-border">
            <LinkButton :href="route('todos.index')" variant="secondary" :disabled="form.processing">
              Cancelar
            </LinkButton>
            <Button type="submit" :disabled="form.processing">
              Actualizar Todos
            </Button>
          </div>
        </form>
      </Card>
    </div>
  </AppLayout>
</template>


