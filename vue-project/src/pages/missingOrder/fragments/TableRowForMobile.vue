<template>
    <Table.Tr>
      <Table.Td class="space-y-2">
        <div>
            <div class="flex gap-2 truncate">
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
            </div>
            <span 
              class="font-medium text-red-500"
              v-if="item?.last_wc_order_at"
            >
              {{ `Last order status: ${item.last_wc_order_current_status}` }}
            </span>
            <br/>
      
            <div class="flex gap-1 font-semibold" title="Customer name">
              {{ item.customer_name }}
            </div>
      
            <div>
              <div class="flex justify-between whitespace-nowrap">
                <a :href="`tel:${item.customer_phone}`" class="block text-orange-500 underline">
                  <span class="font-semibold"> 📞 Phone: </span>
                  {{ item.customer_phone }}
                </a>
        
                <Whatsapp
                  :phone_number="item.customer_phone"
                />
              </div>
              <div v-if="item.customer_email" class="truncate">
                <span class="font-semibold"> 📨 Email: </span>
                {{ item.customer_email || 'n/a' }}
              </div>
            </div>
      
            <div class="truncate">
              📅 {{ item.abandoned_at }}
            </div>
            <div>
              🏠 {{ item.billing_address }}
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
              <h3>💵 Price: {{ item.total_value || 0 }}৳</h3>
              <h3>💰 Discount: {{ item?.cart_contents?.total_discount || 0 }}৳</h3>
              <h3>🎟️ Coupons: {{ item?.cart_contents?.coupon_codes?.length ? item.cart_contents.coupon_codes.join(', ') : 'N/A' }}</h3>
          </div>
  
          <div>
              <h3 title="Payment method">
                  🚚 {{ item.cart_contents?.payment_method || 'n/a' }}
              </h3>
              <h3 title="Shipping method">
                  📍 {{ item.cart_contents?.shipping_method_title || 'n/a' }}
              </h3>
              <h3 title="Shipping cost">
                  💰 Cost: {{ item.cart_contents?.shipping_cost || 'n/a' }}
              </h3>
          </div>
        </div>

        <div>
            <span
                :style="{
                color: selectedOption.color
                }"
            >
                Status: {{ item.status }}
            </span>
            <div v-if="item.abandoned_at">
                <span class="font-semibold text-blue-500">
                Abandoned At: 
                </span>
                <br />
                {{ item.abandoned_at }}
            </div>
            <div v-if="item.recovered_at">
                <span class="font-semibold text-green-500">
                Recovered At: 
                </span>
                <br />
                {{ item.recovered_at }}
            </div>
        </div>

        <div>
            <Button.Primary
              class="mb-3 w-full justify-center"
              @click="toggleModal=true"
              icon="PhEye"
            >
                Product Info
            </Button.Primary>

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
  import { Table, Button, Modal, Select, Whatsapp } from "@components";
  import { inject, ref } from "vue";
  import CartDetails from "./CartDetails.vue";
  
  const props = defineProps<{
    item: object;
  }>();
  
  const toggleModal = ref(false);
  const selectedStatus = ref(props.item.status)
  const { updateStatus, options, selectedOption } = inject("useMissingOrder");
</script>