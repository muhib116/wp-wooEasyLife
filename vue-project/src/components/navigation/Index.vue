<template>
  <Container class="bg-white border-gray-200 border-b sticky z-[999] md:z-[30] ">
    <nav class="w-full flex justify-between items-center">
      <div class="flex items-center -mb-[2px] relative">
        <a href="#" class="hidden xl:flex items-center">
          <img
            class="w-auto max-w-[120px]"
            :src="`https://api.wpsalehub.com/app-logo`"
            alt="WPSaleHub"
          />
        </a>


        <Button.Native 
          class="aspect-squire border border-gray-400 rounded size-7 outline-none grid lg:hidden place-content-center self-center"
          v-click-outside="() => {
            toggleLeftMenu = false
          }"
          @onClick="toggleLeftMenu = !toggleLeftMenu"
        >
          <Icon
            name="PhTextIndent"
            weight="bold"
            size="20"
          />
        </Button.Native>

        <DesktopNavigation
          :menus="menus"
          :toggleLeftMenu="toggleLeftMenu"
        />
      </div>

      <RightMenu />
    </nav>
  </Container>
</template>

<script setup lang="ts">
import { Container } from "@layout"
import { Button, Icon } from '@components'
import { inject, ref } from "vue"
import RightMenu from "./fragments/RightMenu.vue"
import DesktopNavigation from './fragments/menu/DesktopNavigation.vue'

const { configData } = inject("configData");
const toggleLeftMenu = ref(false)
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
    visible: configData?.value?.fraud_customer_checker,
  },
];
</script>
