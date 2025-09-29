<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import LinkButton from '@/Components/LinkButton.vue';
import LiveTimer from '@/Components/LiveTimer.vue';
import StaticTimer from '@/Components/StaticTimer.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  workOrder: {
    type: Object,
    required: true,
  },
  workOrderEntries: {
    type: Object,
    required: true,
  },
});

const stopTimer = (entryId) => {
  const now = new Date();
  const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 19);


  router.patch(route('work-orders.entries.update', [props.workOrder.id, entryId]), {
    ended_at: localDateTime,
  }, {
    preserveScroll: true,
    preserveState: true,
  });
};
</script>

<template>
  <AppLayout :title="`${workOrder.name} - Entries`">
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-medium text-gray-900">{{ workOrder.name }}</h1>
          <p class="text-sm text-gray-500 mt-1">Work Order Entries</p>
        </div>
        <LinkButton :href="route('work-orders.entries.create', workOrder.id)" variant="primary">
          Create new entry
        </LinkButton>
      </div>

      <Card>
        <div class="space-y-4">
          <div v-for="entry in workOrderEntries.data" :key="entry.id" class="flex items-center justify-between py-3 border-b border-gray-100 last:border-b-0">
            <div class="flex items-center gap-4">
              <LinkButton :href="route('work-orders.entries.show', [workOrder.id, entry.id])" variant="secondary">
                {{ entry.name }}
              </LinkButton>
              <LiveTimer v-if="entry.started_at && !entry.ended_at" :started-at="entry.started_at" />
              <StaticTimer v-if="entry.started_at && entry.ended_at" :started-at="entry.started_at" :ended-at="entry.ended_at" />
            </div>
            <div class="flex items-center gap-2">
              <LinkButton
                v-if="entry.started_at && !entry.ended_at"
                @click="stopTimer(entry.id)"
                variant="danger"
                size="sm"
              >
                Stop timer
              </LinkButton>
              <LinkButton :href="route('work-orders.entries.edit', [workOrder.id, entry.id])" variant="secondary" size="sm">
                Edit
              </LinkButton>
              <LinkButton :href="route('work-orders.entries.destroy', [workOrder.id, entry.id])" method="delete" variant="danger" size="sm">
                Delete
              </LinkButton>
            </div>
          </div>
        </div>

        <div v-if="workOrderEntries.links" class="flex items-center justify-center gap-1 mt-6 pt-4 border-t border-gray-100">
          <template v-for="link in workOrderEntries.links" :key="link.label">
            <LinkButton
              v-if="link.url"
              :href="link.url"
              :variant="link.active ? 'primary' : 'secondary'"
              size="sm"
              v-html="link.label"
            />
            <span v-else class="px-3 py-1 text-sm text-gray-400" v-html="link.label" />
          </template>
        </div>
      </Card>
    </div>
  </AppLayout>
</template>
