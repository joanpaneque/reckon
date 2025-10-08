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
  workOrderEntry: {
    type: Object,
    required: true,
  },
  canEdit: {
    type: Boolean,
    required: true,
  },
});

const stopTimer = (entryId) => {
  const now = new Date();
  const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 19);

  router.patch(route('work-orders.entries.update', [props.workOrder.id, entryId]), {
    ended_at: localDateTime,
  });
};
</script>

<template>
  <AppLayout :title="workOrderEntry.name">
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-h2 font-bold text-text-primary tracking-wide-modern">{{ workOrderEntry.name }}</h1>
          <p class="text-sm text-text-secondary mt-1">{{ workOrder.name }}</p>
        </div>
        <LinkButton prefetch :href="route('work-orders.show', workOrder.id)" variant="secondary">
          Back to work order
        </LinkButton>
      </div>

      <Card title="Entry Details">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-text-secondary">Description</label>
            <p class="mt-1 text-sm text-text-primary">{{ workOrderEntry.description || 'No description provided' }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-text-secondary">Started At</label>
              <p class="mt-1 text-sm text-text-primary">{{ workOrderEntry.started_at || 'Not started' }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-text-secondary">Ended At</label>
              <p class="mt-1 text-sm text-text-primary">{{ workOrderEntry.ended_at || 'Not finished' }}</p>
            </div>
          </div>

          <div class="flex items-center gap-4">
            <LiveTimer v-if="workOrderEntry.started_at && !workOrderEntry.ended_at" :started-at="workOrderEntry.started_at" />
            <StaticTimer v-if="workOrderEntry.started_at && workOrderEntry.ended_at" :started-at="workOrderEntry.started_at" :ended-at="workOrderEntry.ended_at" />
          </div>

          <div v-if="canEdit" class="flex items-center gap-2 pt-4 border-t border-dark-border">
            <LinkButton
              v-if="workOrderEntry.started_at && !workOrderEntry.ended_at"
              @click="stopTimer(workOrderEntry.id)"
              variant="danger"
              size="sm"
            >
              Stop timer
            </LinkButton>
            <LinkButton prefetch :href="route('work-orders.entries.edit', [workOrder.id, workOrderEntry.id])" variant="secondary" size="sm">
              Edit Entry
            </LinkButton>
          </div>
        </div>
      </Card>
    </div>
  </AppLayout>
</template>
