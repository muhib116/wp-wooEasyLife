<template>
  <Table.Tr>
    <Table.Td class="space-y-1">
      <div class="flex gap-2 truncate">
        <span
          class="px-1 bg-gray-500 text-white capitalize rounded-sm text"
          title="Order Id"
        >
          #{{ item.id }}
        </span>
        <span
          v-if="item.is_repeat_customer"
          class="px-1 bg-sky-500 text-white capitalize rounded-sm text"
          title="Repeat customer"
        >
          Repeat
        </span>
      </div>

      <div class="flex gap-1 font-semibold" title="Customer name">
        {{ item.customer_name }}
      </div>

      <div>
        <div v-if="item.customer_phone">
          <span class="font-semibold text-[#02b795]"> 📞 Phone: </span>
          {{ item.customer_phone }}
        </div>

        <div v-if="item.customer_email" class="truncate">
          <span class="font-semibold text-orange-500"> 📨 Email: </span>
          {{ item.customer_email || 'n/a' }}
        </div>
      </div>

      <div class="truncate">
        📅 {{ printDate(item.created_at) }}
      </div>
    </Table.Td>
    <Table.Td class="space-y-2">
      <div>
        🏠 <span class="font-semibold text-sky-500">Billing address:</span>
        <br />
        {{ item.billing_address }}
      </div>

      <div>
        📍 <span class="font-semibold text-red-500">Shipping address:</span>
        <br />
        {{ item.shipping_address }}
      </div>
    </Table.Td>
    <Table.Td class="capitalize">
      <span
        :style="{
          color: selectedOption.color
        }"
      >
        {{ item.status }}
      </span>
    </Table.Td>
    <Table.Td class="truncate">
      <div v-if="item.abandoned_at">
        <span class="font-semibold text-red-500">
          Abandoned At: 
        </span>
        <br />
        {{ printDate(item.abandoned_at) }}
      </div>
      <div v-if="item.recovered_at">
        <span class="font-semibold text-green-500">
          Recovered At: 
        </span>
        <br />
        {{ printDate(item.recovered_at) }}
      </div>

    </Table.Td>
    <Table.Td class="truncate">
        <Button.Primary
            class="mx-auto"
            @click="toggleModal=true"
            icon="PhEye"
        >
            Cart Info
        </Button.Primary>
    </Table.Td>
    <Table.Td class="truncate">
      <div class="grid gap-3">
        <Select.Primary
          :options="options"
          v-model="selectedStatus"
        />

        <Button.Primary
          class="!bg-green-500"
          @onClick="(btn) => updateStatus(item, selectedStatus, btn)"
        >
          <Icon
            name="PhUserCheck"
            weight="bold"
          />
          Apply
        </Button.Primary>
      </div>
    </Table.Td>
  </Table.Tr>

  <Modal
    v-model="toggleModal"
    @close="toggleModal = false"
    title="Cart Details"
    class="max-w-[50%] w-full"
    hideFooter
  >
    <CartDetails :order="item" />
  </Modal>
</template>

<script setup lang="ts">
import { printDate } from "@/helper";
import { Table, Button, Modal, Icon, Select } from "@components";
import { inject, onMounted, ref } from "vue";
import CartDetails from "./CartDetails.vue";

const props = defineProps<{
  item: object;
}>();

const toggleModal = ref(false);
const selectedStatus = ref(props.item.status)
const { updateStatus, options, selectedOption } = inject("useMissingOrder");

</script>