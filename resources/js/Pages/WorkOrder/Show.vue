<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import LinkButton from '@/Components/LinkButton.vue';
import LiveTimer from '@/Components/LiveTimer.vue';
import StaticTimer from '@/Components/StaticTimer.vue';
import TimeSummary from '@/Components/TimeSummary.vue';
import Modal from '@/Components/Modal.vue';
import SharedUsersIndicator from '@/Components/SharedUsersIndicator.vue';
import { router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Crown } from 'lucide-vue-next';

const page = usePage();
const isOwner = computed(() => props.workOrder.user_id === page.props.auth.user.id);

const props = defineProps({
  workOrder: {
    type: Object,
    required: true,
  },
  workOrderEntries: {
    type: Object,
    required: true,
  },
  totalTime: {
    type: Number,
    required: true,
  },
  totalCost: {
    type: Number,
    required: true,
  },
  canEdit: {
    type: Boolean,
    required: true,
  },
});

// Modal state
const showDeleteModal = ref(false);
const entryToDelete = ref(null);

const confirmDelete = (entryId, entryName) => {
  entryToDelete.value = { id: entryId, name: entryName };
  showDeleteModal.value = true;
};

const handleDeleteConfirm = () => {
  if (entryToDelete.value) {
    router.delete(route('work-orders.entries.destroy', [props.workOrder.id, entryToDelete.value.id]));
    entryToDelete.value = null;
  }
  showDeleteModal.value = false;
};

const handleDeleteCancel = () => {
  entryToDelete.value = null;
  showDeleteModal.value = false;
};

const stopTimer = (entryId) => {
  const now = new Date();
  const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 19);

  router.patch(route('work-orders.entries.update', [props.workOrder.id, entryId]), {
    ended_at: localDateTime,
  });
};
</script>

<template>
  <AppLayout :title="workOrder.name">
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <div class="flex items-center gap-3">
            <h1 class="text-2xl font-medium text-gray-900 flex items-center gap-2">
              {{ workOrder.name }}
              <Crown v-if="isOwner" :size="20" class="text-yellow-500" />
            </h1>
            <SharedUsersIndicator v-if="workOrder.shared_with" :shared-with="workOrder.shared_with" />
          </div>
        </div>
        <LinkButton prefetch :href="route('work-orders.index')" variant="secondary">
          Back to list
        </LinkButton>
      </div>

      <TimeSummary :total-seconds="totalTime" :total-cost="totalCost" :hour-price="workOrder.hour_price" />

      <Card title="Entries">
        <div v-if="canEdit" class="mb-4">
          <LinkButton prefetch :href="route('work-orders.entries.create', workOrder.id)" variant="primary" size="sm">
            Create new entry
          </LinkButton>
        </div>

        <div class="space-y-4">
          <div v-for="entry in workOrderEntries.data" :key="entry.id" class="flex items-center justify-between py-3 border-b border-gray-100 last:border-b-0">
            <div class="flex items-center gap-4">
              <LinkButton prefetch :href="route('work-orders.entries.show', [workOrder.id, entry.id])" variant="secondary">
                {{ entry.name }}
              </LinkButton>
              <LiveTimer v-if="entry.started_at && !entry.ended_at" :started-at="entry.started_at" />
              <StaticTimer v-if="entry.started_at && entry.ended_at" :started-at="entry.started_at" :ended-at="entry.ended_at" />
            </div>
            <div v-if="canEdit" class="flex items-center gap-2">
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
              <LinkButton @click="confirmDelete(entry.id, entry.name)" variant="danger" size="sm">
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

    <!-- Delete Confirmation Modal -->
    <Modal
      :show="showDeleteModal"
      title="Delete Entry"
      :message="`Are you sure you want to delete this entry?${entryToDelete ? ' (' + entryToDelete.name + ')' : ''}`"
      confirm-text="Delete"
      cancel-text="Cancel"
      confirm-variant="danger"
      @confirm="handleDeleteConfirm"
      @cancel="handleDeleteCancel"
      @close="showDeleteModal = false"
    />
  </AppLayout>
</template>
