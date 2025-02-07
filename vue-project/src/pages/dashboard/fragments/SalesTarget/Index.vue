<template>
  <DashboardCard
    title="Sales Target"
    subtitle="Track Progress Toward Your Sales Goals"
    :showDateFilter="false"
    @dateChange="() => {}"
  >
    <template #before-filter>
      <Button.Outline
        class="border-orange-500 bg-orange-50 text-orange-500"
        title="Make a sales target"
        @onClick="toggleModal = true"
      >
        Set your target
      </Button.Outline>
    </template>

    <div class="grid md:grid-cols-2 2xl:grid-cols-[3fr_3fr_4fr] gap-6">
      <Card title="Daily" :chartData="chartData.daily" />
      <Card title="Monthly" :chartData="chartData.monthly" />
    </div>
    <br />
    <Card
      title="Date wise"
      :chartData="chartData.dateWise"
      hideTargetAchieve
    />
  </DashboardCard>

  <Modal
    v-model="toggleModal"
    @close="toggleModal = false"
    title="Set your sales target"
    class="max-w-[550px] w-full"
    hideFooter
  >
    <SalesTargetForm
        @close="toggleModal = false"
    />
  </Modal>
</template>

<script setup lang="ts">
import { Button, Icon, Modal } from "@components";
import DashboardCard from "../DashboardCard.vue";
import { provide, ref } from "vue";
import SalesTargetForm from "./SalesTargetForm.vue";
import Card from "./Card.vue";
import { useSalesTarget } from "./useSalesTarget";

const _useSalesTarget = useSalesTarget();
const {
  chartData,
} = _useSalesTarget;

const toggleModal = ref(false);
provide("useSalesTarget", _useSalesTarget);
</script>
