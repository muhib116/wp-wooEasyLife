<template>
    <ul
      class="responsive_menu lg:flex lg:ml-6 lg:space-x-5 [&>li]:mb-0 [&>li]:flex"
      :class="{
        'hidden': !toggleLeftMenu
      }"
    >
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
</template>

<script setup lang="ts">
    defineProps<{
        menus: {
            to: object,
            title: string,
            visible: boolean
        },
        toggleLeftMenu: boolean
    }>()
</script>

<style scoped>
@media all and (max-width: 1024px){
  .responsive_menu {
    position: absolute;
    top: 100%;
    background-color: white;
    box-shadow: 0px 4px 4px #0002;
    border-radius: 0px 0px 4px 4px;
    white-space: nowrap;

    li {
      display: block;
    }
    li+li {
      border-top: 1px solid #0001;
    }
    a {
      display: block;
      padding: 14px;
    }
  }
}
</style>