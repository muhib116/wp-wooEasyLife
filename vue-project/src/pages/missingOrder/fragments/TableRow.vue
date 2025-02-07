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
        :class="item.status == 'abandoned' ? 'text-red-500' : 'text-green-500'"
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
        <Button.Primary
          v-if="item.status == 'abandoned'"
          class="!bg-green-500"
          @onClick="(btn) => markAsRecovered(item, btn)"
        >
          <Icon
            name="PhUserCheck"
            weight="bold"
          />
          Confirm order
        </Button.Primary>
        <Button.Primary
          v-else
          class="!bg-orange-500"
          @onClick="(btn) => markAsAbandoned(item, btn)"
        >
          <Icon
            name="PhUserCircleDashed"
            weight="bold"
          />
          Mark as abandoned
        </Button.Primary>
        <Button.Primary
          class="!bg-gray-700"
          @onClick="(btn) => markAsAbandoned(item, btn)"
        >
          <Icon
            name="PhPhoneX"
            weight="bold"
          />
          Call not received
        </Button.Primary>
        <Button.Primary
          class="!bg-red-500"
          @onClick="(btn) => markAsAbandoned(item, btn)"
        >
          
          <Icon
            name="PhTrash"
            weight="bold"
          />
          Delete
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
import { Table, Button, Modal, Icon } from "@components";
import { inject, ref } from "vue";
import CartDetails from "./CartDetails.vue";

defineProps<{
  item: object;
}>();

const toggleModal = ref(false);
const { markAsRecovered, markAsAbandoned } = inject("useMissingOrder");
</script>
