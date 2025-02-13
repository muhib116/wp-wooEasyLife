<template>
    <Layout>
        <Container>
            <Loader
                class="absolute inset-1/2 -translate-x-1/2 bg-white rounded-full shadow"
                :active="isLoading"
            />
            <Card.Native>
                <div 
                    class="relative mb-3 lg:mb-0"
                >
                    <Button.Native 
                        class="aspect-squire border border-gray-400 rounded size-7 outline-none grid lg:hidden place-content-center self-center"
                        @onClick="toggleMenu = !toggleMenu"
                        v-click-outside="() => {
                            toggleMenu = false
                        }"
                    >
                        <Icon
                            name="PhTextOutdent"
                            weight="bold"
                            size="20"
                        />
                    </Button.Native>

                    <div 
                        class="responsive_menu lg:flex gap-4 mb-5 lg:-ml-6 -mr-6 lg:-mt-6 rounded-t-md border-b px-6 py-3 bg-white text-black"
                        :class="{
                            'hidden': !toggleMenu
                        }"
                    >
                        <Button.Native
                            @click="selectedOption = {id: 'dashboard', title: 'Dashboard', color: 'black'}"
                            class="hover:text-sky-500 font-medium"
                            :class="selectedOption.id == 'dashboard' ? 'text-sky-500 font-semibold' : ''"
                        >
                            Dashboard
                        </Button.Native>

                        <Button.Native
                            v-for="option in options"
                            @onClick="btn => handleFilter(option, btn)"
                            class="hover:text-sky-500 font-medium"
                            :class="selectedOption.id == option.id ? 'text-sky-500 font-semibold' : ''"
                        >
                            {{ option.title }}
                        </Button.Native>
                    </div>
                </div>

                <div
                    v-if="(userData?.remaining_order) > 0"
                >
                    <Dashboard v-if="selectedOption.id == 'dashboard'" />
                    <OrderList v-else />
                </div>

                <div v-else-if="!isLoading" >
                    <MessageBox
                        title="Insufficient balance! Access denied."
                        type="danger"
                    />
                </div>

            </Card.Native>
        </Container>
    </Layout>
</template>

<script setup lang="ts">
    import { Layout, Container } from '@layout'
    import { Button, Card, Loader, Icon, MessageBox } from '@/components'
    import { useMissingOrder } from './useMissingOrder'
    import OrderList from './fragments/OrderList.vue'
    import Dashboard from './fragments/Dashboard.vue'
    import { inject, provide, ref } from 'vue'

    const _useMissingOrder = useMissingOrder()
    const {
        options,
        selectedOption,
        isLoading,
        handleFilter,
    } = _useMissingOrder

    const toggleMenu = ref(false)
    const { userData }  = inject("useServiceProvider")
    provide('useMissingOrder', _useMissingOrder)
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
    z-index: 99;
    padding: 0;
    gap: 0;

    button+button {
      border-top: 1px solid #0001;
    }
    button {
    text-align: left;
      display: block;
      padding: 14px;
    }
  }
}
</style>