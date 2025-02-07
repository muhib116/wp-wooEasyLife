<template>
  {{ useOrders }}
  <Table.Tr class="group" :class="`status-${order.status}`">
    <Table.Td>{{ index + 1 }}</Table.Td>
    <Table.Td @click="setSelectedOrder(order)">
      <div class="flex gap-2">
        <span
          class="px-1 bg-gray-500 text-white capitalize rounded-sm text"
          title="Order Id"
        >
          #{{ order.id }}
        </span>
        <span
          class="px-1 bg-sky-500 text-white capitalize rounded-sm text"
          title="Order source"
        >
          {{ order.created_via.replace("-", " ") }}
        </span>
      </div>
      <div class="flex gap-1 font-semibold">
        {{ order.billing_address.first_name }}
        {{ order.billing_address.last_name }}
        <a
          class="text-orange-500 hover:scale-150 duration-200 opacity-0 group-hover:opacity-100"
          :href="`${baseUrl}/wp-admin/post.php?post=${order.id}&action=edit`"
          target="_blank"
        >
          <Icon name="PhArrowSquareOut" size="20" weight="bold" />
        </a>
      </div>
      <div class="text-[12px] flex gap-1 items-center">
        📅 {{ order.date_created }}
      </div>
      <div class="text-[12px] flex gap-1 items-center">
        📞 {{ order.billing_address.phone }}
      </div>
      <div
        class="text-[12px] flex gap-1 items-center"
        :title="`${order.billing_address.address_1}, ${order.billing_address.address_2}`"
      >
        <p class="max-w-[200px] truncate">
          🏠
          {{ order.billing_address.address_1 }},
          {{ order.billing_address.address_2 }}
        </p>
      </div>

      <div class="flex gap-2">
        <span
          v-if="order.ip_block_listed"
          class="!py-0 !text-[10px] flex items-center text-red-500"
        >
          <Icon name="PhCellTower" size="12" />
          Ip blocked
        </span>
        <span
          v-if="order.phone_block_listed"
          class="!py-0 !text-[10px] flex items-center text-red-500"
        >
          <Icon name="PhSimCard" size="12" />
          Phone blocked
        </span>
      </div>
    </Table.Td>
    <Table.Td>
      <div v-if="order?.customer_report" class="group whitespace-nowrap">
        <div class="flex gap-2" title="Total order">
          📦 Total:
          <strong>{{ order.customer_report?.total_order || 0 }}</strong>
        </div>
        <div class="flex gap-2 text-green-600" title="Confirmed order">
          🎉 Confirmed:
          <strong>{{ order.customer_report?.confirmed || 0 }}</strong>
        </div>
        <div class="flex gap-2 text-red-600" title="Canceled order">
          ❌ Canceled:
          <strong>{{
            order.customer_report?.total_order -
              order.customer_report?.confirmed || 0
          }}</strong>
        </div>
        <div class="flex gap-2 flex-wrap text-sky-600" title="Success Rate">
          ✅ Rate:
          <strong class="truncate block">
            {{ order.customer_report?.success_rate || "0%" }}
          </strong>
        </div>
        <button
          class="opacity-0 group-hover:opacity-100 text-white bg-orange-500 shadow mt-1 rounded-sm px-2"
          @click="toggleFraudHistoryModel = true"
        >
          View Details
        </button>
      </div>
      <div v-else>n/a</div>
    </Table.Td>
    <Table.Td @click="setSelectedOrder(order)" class="whitespace-nowrap">
      <span title="Delivery partner"> 🚚 Steadfast </span>
      <br />
      <span title="Consignment Id"> 🆔 100198765 </span>
      <br />
      <span class="font-medium text-sky-500" title="Courier Status">
        📦 In Review
      </span>
    </Table.Td>
    <Table.Td @click="setSelectedOrder(order)">
      <div class="grid">
        <span title="Payment methods" class="font-medium whitespace-nowrap">
          🚚 {{ order.payment_method_title || "n/a" }}
        </span>
        <span
          class="truncate"
          :title="`Shipping methods: ${
            order.shipping_methods.join(', ') || 'n/a'
          }`"
        >
          📍 {{ order.shipping_methods.join(", ") || "n/a" }}
        </span>
        <span title="Shipping cost" class="font-medium text-red-500">
          💰 Cost: <span v-html="order.currency_symbol"></span
          >{{ order.shipping_cost || "n/a" }}
        </span>
      </div>
    </Table.Td>
    <Table.Td @click="setSelectedOrder(order)">
      <span class="truncate">
        💵 Price: <span v-html="order.product_price"></span>
      </span>
      <div class="whitespace-nowrap">
        💰 Discount: {{ order.discount_total }}
        <br />
        🎟️ Coupons: {{ order.applied_coupons.join(", ") || "n/a" }}
      </div>
    </Table.Td>
    <Table.Td @click="setSelectedOrder(order)" class="whitespace-nowrap">
      <button
        class="relative order-status capitalize px-3 py-1"
        :class="`status-${order.status}`"
      >
        {{
          order.status == "processing"
            ? "New Order"
            : order.status.replaceAll("-", " ")
        }}

        <span
          v-if="order.total_order_per_customer_for_current_order_status > 1"
          title="Multiple order place"
          class="cursor-pointer absolute -top-2 right-0 w-5 bg-red-500 aspect-square border-none text-white rounded-full text-[10px] hover:scale-110 shadow duration-300"
          @click="toggleMultiOrderModel = true"
        >
          {{ order.total_order_per_customer_for_current_order_status }}
        </span>
      </button>
    </Table.Td>
    <Table.Td>
      <button
        class="relative flex flex-col whitespace-nowrap justify-center items-center text-orange-500"
        @click="toggleNotesModel = true"
      >
        <Icon name="PhNote" size="20" />
        Notes
      </button>
    </Table.Td>
    <Table.Td>
      <button
        class="relative flex flex-col whitespace-nowrap justify-center items-center text-sky-500"
        @click="toggleModel = true"
      >
        <Icon name="PhMapPinLine" size="20" />
        Address
      </button>
    </Table.Td>
    <Table.Td class="pointer-events-none">
      <button
        class="relative flex flex-col whitespace-nowrap justify-center items-center text-blue-500 pointer-events-auto"
        title="Order details"
        @click="
          (e) => {
            e.preventDefault();
            setActiveOrder(order);
          }
        "
      >
        <Icon name="PhFileText" size="20" />
        Details
      </button>
    </Table.Td>
  </Table.Tr>

  <Modal
    v-model="toggleModel"
    @close="toggleModel = false"
    class="max-w-[70%] w-full"
    title="Address manage"
  >
    <Address :order="order" />
  </Modal>

  <Modal
    v-model="toggleFraudHistoryModel"
    @close="toggleFraudHistoryModel = false"
    class="max-w-[50%] w-full"
    :title="`Fraud history`"
  >
    <FraudHistory :order="order" />
  </Modal>

  <Modal
    v-model="toggleMultiOrderModel"
    @close="toggleMultiOrderModel = false"
    class="max-w-[80%] w-full"
    title="Duplicate Order History"
  >
    <MultipleOrders :item="order" />
  </Modal>

  <Modal
    v-model="toggleNotesModel"
    @close="toggleNotesModel = false"
    class="max-w-[50%] w-full"
    title="Order Notes"
    hideFooter
  >
    <Notes :order="order" />
  </Modal>
</template>

<script setup lang="ts">
import { Table, Icon, Modal } from "@components";
import { inject, ref } from "vue";
import Address from "@/pages/orders/fragments/fragments/address/Index.vue";
import { baseUrl } from "@/api";
import FraudHistory from "@/pages/orders/fragments/fragments/FraudHistory.vue";
import MultipleOrders from "@/pages/orders/fragments/fragments/MultipleOrders.vue";
import Notes from "@/pages/orders/fragments/fragments/notes/Index.vue";

defineProps<{
  index: number
  order: {
    productId: number
    productName: string
    quantity: number
    price: number
    total: number
  }
}>()

const { setActiveOrder, setSelectedOrder } = inject("useOrders", {});

const toggleModel = ref(false);
const toggleFraudHistoryModel = ref(false);
const toggleMultiOrderModel = ref(false);
const toggleNotesModel = ref(false);
</script>
