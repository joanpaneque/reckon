<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import WorkOrderForm from '@/Components/WorkOrder/WorkOrderForm.vue';
import WorkOrderHeader from '@/Components/WorkOrder/WorkOrderHeader.vue';
</script>

<template>
  <AppLayout title="Create work order">
    <div class="space-y-6">
      <WorkOrderHeader
        title="Create work order"
        :back-route="route('work-orders.index')"
      />

      <Card>
        <WorkOrderForm
          :submit-route="route('work-orders.store')"
          method="post"
          submit-label="Create work order"
        />
      </Card>
    </div>
  </AppLayout>
</template>
