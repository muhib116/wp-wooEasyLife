<template>
    <Layout>
        <Container>
            <Card.Native>
                <div class="border-b pb-3">
                    <h3 class="text-2xl font-semibold">Settings</h3>
                </div>
                <div class="flex h-full">
                    <div class="h-[calc(100%-50px)] flex-shrink-0 overflow-auto w-[255px] pr-5 py-5">
                        <LeftSideMenu />
                    </div>
                    <div class="p-5 border-l bg-gray-100 w-full h-[calc(100%-50px)] overflow-auto">
                        <RouterView />
                    </div>
                </div>
            </Card.Native>
        </Container>
    </Layout>
</template>
<script setup lang="ts">
    import { Layout, Container } from '@layout'
    import { Card } from '@components'
    import LeftSideMenu from './fragments/LeftSideMenu.vue'
</script>