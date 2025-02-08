<template>
    <Layout>
        <Container>
            <Loader
                class="absolute inset-1/2 -translate-x-1/2 bg-white rounded-full shadow"
                :active="isLoading"
            />
            <Card.Native>
                <div class="flex gap-4 mb-5 -ml-6 -mr-6 -mt-6 rounded-t-md border-b px-6 py-3 bg-white text-black">
                    <Button.Native
                        @click="selectedOption = {id: 'dashboard', title: 'Dashboard', color: 'black'}"
                        class="hover:text-sky-500 font-medium"
                        :class="selectedOption.id == 'dashboard' ? 'text-sky-500 font-semibold' : ''"
                    >
                        Dashboard
                    </Button.Native>

                    <Button.Native
                        v-for="item in options"
                        @onClick="btn => handleFilter(item, btn)"
                        class="hover:text-sky-500 font-medium"
                        :class="selectedOption.id == item.id ? 'text-sky-500 font-semibold' : ''"
                    >
                        {{ item.title }}
                    </Button.Native>
                </div>

                <Dashboard v-if="selectedOption.id == 'dashboard'" />
                <OrderList v-else />

            </Card.Native>
        </Container>
    </Layout>
</template>

<script setup lang="ts">
    import { Layout, Container } from '@layout'
    import { Button, Card, Loader } from '@/components'
    import { useMissingOrder } from './useMissingOrder'
    import OrderList from './fragments/OrderList.vue'
    import Dashboard from './fragments/Dashboard.vue'
    import { provide } from 'vue'

    const _useMissingOrder = useMissingOrder()
    const {
        options,
        selectedOption,
        isLoading,
        handleFilter,
    } = _useMissingOrder

    provide('useMissingOrder', _useMissingOrder)
</script>