<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import WorkOrderForm from '@/Components/WorkOrder/WorkOrderForm.vue';
import WorkOrderHeader from '@/Components/WorkOrder/WorkOrderHeader.vue';

const props = defineProps({
  workOrder: {
    type: Object,
    required: true,
  },
});

</script>

<template>
  <AppLayout title="Edit work order">
    <div class="space-y-6">
      <WorkOrderHeader
        title="Edit work order"
        :back-route="route('work-orders.index')"
      />

      <Card>
        <WorkOrderForm
          :work-order="workOrder"
          :submit-route="route('work-orders.update', workOrder.id)"
          method="patch"
          submit-label="Update work order"
        />
      </Card>
    </div>
  </AppLayout>
</template>
