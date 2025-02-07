<template>
    <div
        v-if="configData"
        class="print:bg-transparent bg-gray-100 min-h-screen print:pb-0 pb-10 text-gray-600"
    >
        <Navigation 
            class="sticky z-50 print:hidden"
            :class="isDevelopmentMode ? 'top-0' : 'top-8'"
        />

        <div
            v-if="!hideAlerts"
            class="fixed bottom-4 right-4 z-50"
        >
            <!-- messages start -->
            <MessageBox
                v-if="!!hasNewOrder"
                title="New Order Received 🎉"
                type="success"
                @onClose="hasNewOrder = false"
            />
            <MessageBox
                v-if="userData?.notice && userData?.notice?.message"
                :title="userData?.notice?.message"
                :type="userData?.notice?.type"
                cleanBox

                @onClose="() => {
                    userData.notice.message = ''
                }"
            />
            <MessageBox
                v-if="internetStatusMessage.title"
                :type="internetStatusMessage.type"
                :title="internetStatusMessage.title"

                @onClose="() => {
                    internetStatusMessage.title = ''
                }"
            />
            <!-- messages end -->
        </div>


        <main class="print:mt-0 mt-6">
            <slot></slot>
        </main>
    </div>
    <div v-else class="h-[100vh] relative">
        <Loader
            active
            class="absolute inset-1/2 -translate-x-1/2 -translate-y-1/2 "
            size="30"
        />
    </div>

    <Tutorials/>
</template>

<script setup lang="ts">
    import { Navigation, Loader, MessageBox } from '@components'
    import { onBeforeMount, provide, inject, onMounted } from 'vue'
    import { useCourier } from '@/pages/config/courier/useCourier'
    import { useNotification } from './useNotification'
    import { useLayout } from './useLayout'
    import Tutorials from '@/tutorials/Index.vue'

    defineProps<{
        hideAlerts: boolean
    }>()

    const isDevelopmentMode =  import.meta.env.DEV
    const _useCourierConfig = useCourier()
    const { loadCourierConfigData } = _useCourierConfig

    const { isValidLicenseKey } = inject('useServiceProvider')
    const _useLayout = useLayout()
    const {
        configData,
        loadConfig,
        internetStatusMessage
    } = _useLayout

    const {
        userData
    } = inject('useServiceProvider')

    onBeforeMount(async () => {
        await loadConfig()
    })
    
    const _useNotification = useNotification()
    const {
        hasNewOrder
    } = _useNotification

    onMounted(async () => {
        if(isValidLicenseKey.value) {
            await loadCourierConfigData()
        }
    })

    provide('useCourierConfig', _useCourierConfig)
    provide('configData', {configData})
    provide('useNotification', _useNotification)
</script>