<template>
  <Container class="bg-white border-gray-200 border-b z-[30] sticky">
    <nav class="w-full flex justify-between">
      <div class="flex -mb-[2px]">
        <a href="#" class="flex items-center">
          <img
            class="w-auto max-w-[120px]"
            :src="`https://api.wpsalehub.com/app-logo`"
            alt="WPSaleHub"
          />
        </a>

        <ul class="flex sm:ml-6 sm:flex sm:space-x-5 [&>li]:mb-0 [&>li]:flex">
          <template v-for="(item, index) in menus" :key="index">
            <li v-if="item.visible">
              <RouterLink
                :to="item.to"
                exact-active-class="border-orange-500/100 text-gray-700"
                class="text-base border-b-2 border-orange-500/0 hover:border-orange-500/100 text-gray-500 hover:text-gray-700 inline-flex items-center"
              >
                {{ item.title }}
              </RouterLink>
            </li>
          </template>
        </ul>
      </div>

      <RightMenu />
    </nav>
  </Container>
</template>

<script setup lang="ts">
import { Container } from "@layout";
import { inject } from "vue";
import RightMenu from "./fragments/RightMenu.vue";

const { configData } = inject("configData");

const menus = [
  {
    title: "Dashboard",
    to: {
      name: "dashboard",
    },
    visible: true,
  },
  {
    title: "Orders",
    to: {
      name: "orders",
    },
    visible: true,
  },
  {
    title: "Missing Orders",
    to: {
      name: "missingOrders",
    },
    visible: true,
  },
  {
    title: "Black List",
    to: {
      name: "blackList",
    },
    visible: true,
  },
  {
    title: "Fraud Check",
    to: {
      name: "fraudCheck",
    },
    visible: configData.value.fraud_customer_checker,
  },
];
</script>
