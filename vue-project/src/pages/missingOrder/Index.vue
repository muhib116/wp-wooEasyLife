<template>
    <Layout>
        <Container>
            <Loader
                class="absolute inset-1/2 -translate-x-1/2 bg-white rounded-full shadow"
                :active="isLoading"
            />
            <Card.Native>
                <MessageBox
                    :title="alertMessage.title"
                    :type="alertMessage.type"
                />

                <div class="flex gap-4 mb-5 -ml-6 -mr-6 -mt-6 rounded-t-md border-b px-6 py-3 bg-sky-600 text-white/60">
                    <Button.Native
                        v-for="item in filter"
                        @onClick="btn => handleFilter(item, btn)"
                        class="font-light hover:text-white"
                        :class="selectedFilter == item.slug ? 'text-white' : ''"
                    >
                        {{ item.title }}
                    </Button.Native>
                </div>

                <Dashboard v-if="selectedFilter == 'dashboard'" />
                <OrderList v-else />

            </Card.Native>
        </Container>
    </Layout>
</template>

<script setup lang="ts">
    import { Layout, Container } from '@layout'
    import { Button, MessageBox, Card, Loader } from '@/components'
    import { useMissingOrder } from './useMissingOrder'
    import OrderList from './fragments/OrderList.vue'
    import Dashboard from './fragments/Dashboard.vue'
    import { provide } from 'vue'

    const _useMissingOrder = useMissingOrder()
    const {
        filter,
        isLoading,
        alertMessage,
        selectedFilter,
        handleFilter,
    } = _useMissingOrder

    provide('useMissingOrder', _useMissingOrder)
</script>