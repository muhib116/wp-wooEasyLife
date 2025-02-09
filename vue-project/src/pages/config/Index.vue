<template>
    <Layout>
        <Container>
            <Card.Native class="p-0 shadow-none bg-transparent lg:bg-white lg:shadow-md lg:p-6">
                <div class="px-4 py-2 lg:py-0 lg:px-0 border-b pb-3 flex gap-3 items-center">
                    <Button.Native 
                        class="aspect-squire border border-gray-400 rounded size-7 outline-none grid lg:hidden place-content-center self-center"
                        @onClick="toggleLeftSidebar = !toggleLeftSidebar"
                        v-click-outside="() => {
                            toggleLeftSidebar = false
                        }"
                    >
                        <Icon
                            name="PhTextOutdent"
                            weight="bold"
                            size="20"
                        />
                    </Button.Native>

                    <h3 class="text-lg lg:text-2xl font-semibold">
                        Settings
                    </h3>
                </div>

                <div class="flex h-full">
                    <div 
                        class="NavigationResponsive duration-500 lg:block h-[calc(100%-50px)] flex-shrink-0 overflow-auto w-[255px] pr-5 py-5"
                        :class="{
                            'navigationActive': toggleLeftSidebar 
                        }"
                    >
                        <LeftSideMenu />
                    </div>
                    <div class="lg:p-5 lg:border-l lg:bg-gray-100 w-full lg:h-[calc(100%-50px)] lg:overflow-auto">
                        <RouterView />
                    </div>
                </div>
            </Card.Native>
        </Container>
    </Layout>
</template>
<script setup lang="ts">
    import { Layout, Container } from '@layout'
    import { Card, Icon, Button } from '@components'
    import LeftSideMenu from './fragments/LeftSideMenu.vue'
    import { ref } from 'vue'

    const toggleLeftSidebar = ref(false)
</script>

<style scoped>
    @media all and (max-width: 1024px) {
        .NavigationResponsive {
            position: fixed;
            z-index: 9999;
            top: 0;
            bottom: 0;
            left: -101%;
            height: 100vh;
            box-shadow: 1px 0 6px #0002;
            background-color: white;
            padding-left: 20px;
        }
        .navigationActive {
            left: 0;
        }
    }
</style>