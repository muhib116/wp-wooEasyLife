<template>
  <Table.Tr>
    <Table.Td class="space-y-1">
      <div class="flex gap-2">
        <span
          class="px-1 bg-gray-500 text-white capitalize rounded-sm text"
          title="Order Id"
        >
          #{{ item.id }}
        </span>
        <span
          v-if="item?.last_wc_order_at"
          class="px-1 bg-red-500 text-white capitalize rounded-sm text"
        >
          Has order ({{ item.last_wc_order_at }})
        </span>

        <OrderHistory
          :item="item"
        />
      </div>
      <span 
        class="font-medium text-red-500"
        v-if="item?.last_wc_order_at"
      >
        {{ `Last order status: ${item.last_wc_order_current_status}` }}
      </span>

      <div class="flex gap-1 font-semibold" title="Customer name">
        {{ item.customer_name }}
      </div>

      <div>
        <div class="flex gap-2 items-center">
          <a :href="`tel:${item.customer_phone}`" class="block text-orange-500 underline">
            <span class="font-semibold"> 📞 Phone: </span>
            {{ item.customer_phone }}
          </a>
          
          <Whatsapp
            :phone_number="item.customer_phone"
          />
          <Button.Native 
              @onClick="btn => handleFraudCHeck(item.customer_phone, btn)"
              title="Fraud Check"
              class="flex items-center gap-2 font-semibold relative hover:scale-105 hover:z-30 duration-200 cursor-pointer p-1 border shadow rounded-full bg-[#f14a00] text-white"
          >
              <Icon name="PhUserList" size="20" />
          </Button.Native>

          <RouterLink
              :to="{
                  name: 'orders',
                  query: {
                      phone: item.customer_phone
                  }
              }"
              target="_blank"
          >
              Check
          </RouterLink>
        </div>

        <div v-if="item.customer_email" class="truncate">
          <span class="font-semibold"> 📨 Email: </span>
          {{ item.customer_email || 'n/a' }}
        </div>
      </div>

      <div class="truncate">
        📅 {{ item.created_at }}
      </div>
      <div>
        🏠 {{ item.billing_address }}
      </div>
    </Table.Td>

    <Table.Td class="space-y-2 truncate">
      <h3>💵 Price: {{ item.total_value || 0 }}৳</h3>
      <h3>💰 Discount: {{ item?.cart_contents?.total_discount || 0 }}৳</h3>
      <h3>🎟️ Coupons: {{ item?.cart_contents?.coupon_codes?.length ? item.cart_contents.coupon_codes.join(', ') : 'N/A' }}</h3>
    </Table.Td>

    <Table.Td class="space-y-2 truncate">
      <h3 title="Payment method">
        🚚 {{ item.cart_contents?.payment_method || 'n/a' }}
      </h3>
      <h3 title="Shipping method">
        📍 {{ item.cart_contents?.shipping_method_title || 'n/a' }}
      </h3>
      <h3 title="Shipping cost">
        💰 Cost: {{ item.cart_contents?.shipping_cost || 'n/a' }}
      </h3>
    </Table.Td>
    
    <Table.Td class="capitalize min-w-[160px] space-y-2">
      <span
        :style="{
          color: selectedOption.color
        }"
      >
        {{ item.status }}
      </span>
      <Button.Primary
          class="mt-1"
          @click="toggleModal=true"
          icon="PhEye"
      >
          Cart Info
      </Button.Primary>

      <div v-if="item.abandoned_at">
        <span class="font-semibold text-blue-500">
          Abandoned At: 
        </span>
        <br />
        {{ item.abandoned_at }}
      </div>
      <div v-if="item.status == 'confirmed'">
        <span class="font-semibold text-green-500">
          Recovered At: 
        </span>
        <br />
        {{ item.recovered_at }}
      </div>
    </Table.Td>

    <Table.Td 
      class="truncate"
    >
      <div
        v-if="item.status == 'abandoned' || item.status == 'call-not-received'"
        class="grid gap-3 min-w-[135px]"
      >
        <Select.Primary
          :options="options"
          v-model="selectedStatus"
        />

        <Button.Primary
          class="!bg-green-500 justify-center"
          @onClick="(btn) => updateStatus(item, selectedStatus, btn)"
        >
          Apply now
        </Button.Primary>
      </div>
      <div v-else>
          n/a
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

  <Modal 
    v-model="toggleFraudCheckModal" 
    @close="toggleFraudCheckModal = false"
    :title="item.customer_name"
>
    <FraudData
        :data="data" 
        class="max-w-[600px] mx-auto"
    />
  </Modal>
</template>

<script setup lang="ts">
import { Table, Button, Modal, Select, Whatsapp, Icon } from "@components";
import { inject, ref } from "vue";
import CartDetails from "./CartDetails.vue";
import FraudData from '@/pages/fraudChecker/FraudData.vue'
import { useFraudChecker } from '@/pages/fraudChecker/useFraudChecker'
import OrderHistory from './OrderHistory.vue';

const props = defineProps<{
  item: object;
}>();

const toggleModal = ref(false);
const toggleFraudCheckModal = ref(false);
const selectedStatus = ref(props.item.status)
const { updateStatus, options, selectedOption } = inject("useMissingOrder");
const { handleFraudCheck, data } = useFraudChecker()

const handleFraudCHeck = async (phone, btn) => {
    await handleFraudCheck(phone, btn)
    toggleFraudCheckModal.value = true
}
</script>