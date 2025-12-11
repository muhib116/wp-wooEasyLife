<template>
  <div class="flex items-center">

    <svg width="800" height="800" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="mr-4 size-5 iconify iconify--noto"><circle cx="63.93" cy="64" r="60" :fill="licenseStatus == 'valid' ? '#689f38' : 'red'"/><circle cx="60.03" cy="63.1" r="56.1" :fill="licenseStatus == 'valid' ? '#7cb342' : 'red'"/><path d="M23.93 29.7c4.5-7.1 14.1-13 24.1-14.8 2.5-.4 5-.6 7.1.2 1.6.6 2.9 2.1 2 3.8-.7 1.4-2.6 2-4.1 2.5a44.64 44.64 0 0 0-23 17.4c-2 3-5 11.3-8.7 9.2-3.9-2.3-3.1-9.5 2.6-18.3" fill="#aed581"/></svg>


    <ul class="flex items-center [&>li]:mb-0">
      <li class="flex items-center gap-2">
        <Balance />
      </li>
      <li>
        <button
          v-if="isValidLicenseKey"
          class="pointer-events-none bg-[#f0fdf4] border border-green-200 px-1 md:px-2 py-1 text-green-600 hover:text-green-600 rounded cursor-pointer font-normal mr-4"
          title="Valid License"
        >
          <span class="hidden xl:block">Valid License</span>
          <Icon
            class="block xl:hidden"
            name="PhSealCheck"
            size="20"
            weight="bold"
          />
        </button>
        <Button.Native
          v-else
          class="bg-[#fdf0f0] border border-red-200 px-2 py-1 text-red-600 hover:text-red-600 rounded cursor-pointer font-normal mr-4"
          :to="{
            name: 'license'
          }"
        >
          <span class="hidden xl:block">Activate License</span>
          <Icon
            class="block xl:hidden"
            name="PhSeal"
            size="22"
            weight="bold"
          />
        </Button.Native>
      </li>
    </ul>

    <div class="border-r border-gray-200 py-2.5 mr-3"></div>

    <ul class="flex items-center md:gap-2 xl:gap-3">
      <li>
        <RouterLink
          :to="{
            name: 'license',
          }"
          class="text-gray-400 hover:text-orange-500 py-4 inline-flex"
          exact-active-class="text-orange-500"
        >
          <Icon name="PhGearSix" size="22" />
        </RouterLink>
      </li>
      <li>
        <button 
          class="text-red-500 hover:text-red-600 animate-bounce py-4 hidden md:inline-flex"
          @click="setActiveTutorialList($route.name)"
          title="Watch the tutorial!"
        >
          <Icon name="PhPlayCircle" size="28" />
        </button>
      </li>
      <li>
        <a href="#" class="text-gray-400 hover:text-gray-500 py-4 hidden md:inline-flex">
          <Icon name="PhQuestion" size="28" />
        </a>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import { Icon, Button } from "@components"
import { setActiveTutorialList } from '@/tutorials/useTutorials'
import Balance from './Balance.vue'
import { inject, onMounted } from "vue"
import { getLicenseStatus } from '@/api'

const { isValidLicenseKey, licenseStatus } = inject('useServiceProvider')

onMounted(async () => {
  const { data } = await getLicenseStatus()
  licenseStatus.value = data.license_status
})
</script>
