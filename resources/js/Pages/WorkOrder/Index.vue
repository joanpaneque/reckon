<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import LinkButton from '@/Components/LinkButton.vue';
import TimeDisplay from '@/Components/TimeDisplay.vue';
import Modal from '@/Components/Modal.vue';
import SharedUsersIndicator from '@/Components/SharedUsersIndicator.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  workOrders: {
    type: Object,
    required: true,
  },
  sharedWorkOrders: {
    type: Object,
    required: true,
  },
});

// Modal state
const showDeleteModal = ref(false);
const workOrderToDelete = ref(null);

const confirmDelete = (workOrderId, workOrderName) => {
  workOrderToDelete.value = { id: workOrderId, name: workOrderName };
  showDeleteModal.value = true;
};

const handleDeleteConfirm = () => {
  if (workOrderToDelete.value) {
    router.delete(route('work-orders.destroy', workOrderToDelete.value.id));
    workOrderToDelete.value = null;
  }
  showDeleteModal.value = false;
};

const handleDeleteCancel = () => {
  workOrderToDelete.value = null;
  showDeleteModal.value = false;
};
</script>

<template>
  <AppLayout title="Work orders">
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-medium text-gray-900">Work orders</h1>
        <LinkButton :href="route('work-orders.create')" variant="primary">
          Create work order
        </LinkButton>
      </div>

      <Card>
        <div class="space-y-4">
          <div v-for="workOrder in workOrders.data" :key="workOrder.id" class="flex items-center justify-between py-3 border-b border-gray-100 last:border-b-0">
            <div class="flex-1">
              <LinkButton :href="route('work-orders.show', workOrder.id)" variant="secondary">
                {{ workOrder.name }}
              </LinkButton>
              <div class="mt-1 flex items-center gap-3">
                <TimeDisplay :total-seconds="workOrder.total_seconds" :total-cost="workOrder.total_cost" compact />
                <SharedUsersIndicator v-if="workOrder.shared_with" :shared-with="workOrder.shared_with" />
              </div>
            </div>
            <div class="flex items-center gap-2">
              <LinkButton :href="route('work-orders.edit', workOrder.id)" variant="secondary" size="sm">
                Edit
              </LinkButton>
              <LinkButton @click="confirmDelete(workOrder.id, workOrder.name)" variant="danger" size="sm">
                Delete
              </LinkButton>
            </div>
          </div>
        </div>

        <div v-if="workOrders.links" class="flex items-center justify-center gap-1 mt-6 pt-4 border-t border-gray-100">
          <template v-for="link in workOrders.links" :key="link.label">
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

      <div class="mt-8">
        <h2 class="text-xl font-medium text-gray-900 mb-4">Shared with me</h2>
        <Card>
          <div class="space-y-4">
            <div v-for="workOrder in sharedWorkOrders.data" :key="workOrder.id" class="flex items-center justify-between py-3 border-b border-gray-100 last:border-b-0">
              <div class="flex-1">
                <LinkButton :href="route('work-orders.show', workOrder.id)" variant="secondary">
                  {{ workOrder.name }}
                </LinkButton>
                <div class="mt-1 flex items-center gap-3">
                  <TimeDisplay :total-seconds="workOrder.total_seconds" :total-cost="workOrder.total_cost" compact />
                  <div class="flex items-center gap-1 bg-green-600 text-white px-1.5 py-0.5 cursor-default select-none text-xs">
                    shared by {{ workOrder.user.email }}
                  </div>
                  <SharedUsersIndicator v-if="workOrder.shared_with" :shared-with="workOrder.shared_with" :exclude-current-user="true" />
                </div>
              </div>
            </div>
          </div>

          <div v-if="sharedWorkOrders.links" class="flex items-center justify-center gap-1 mt-6 pt-4 border-t border-gray-100">
            <template v-for="link in sharedWorkOrders.links" :key="link.label">
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
    </div>

    <!-- Delete Confirmation Modal -->
    <Modal
      :show="showDeleteModal"
      title="Delete Work Order"
      :message="`Are you sure you want to delete this work order?${workOrderToDelete ? ' (' + workOrderToDelete.name + ')' : ''}`"
      confirm-text="Delete"
      cancel-text="Cancel"
      confirm-variant="danger"
      @confirm="handleDeleteConfirm"
      @cancel="handleDeleteCancel"
      @close="showDeleteModal = false"
    />
  </AppLayout>
</template>
