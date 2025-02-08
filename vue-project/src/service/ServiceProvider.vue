<template>
    <slot></slot>
</template>

<script setup lang="ts">
    import { onMounted, provide } from 'vue'
    import { isEmpty, isArray } from 'lodash'
    import {
        useServiceProvider
    } from './useServiceProvider'
    import { useRouter, useRoute } from 'vue-router'
    import { showNotification } from '@/helper'

    const _useServiceProvider = useServiceProvider()
    const { 
        userData,
        router,
        route,
        loadUserData, 
        getNoticeOfBalanceOver,
    } = _useServiceProvider



    router.value = useRouter()
    route.value = useRoute()

    setInterval(loadUserData, 100000)

    onMounted(async () => {
        if(isEmpty(userData.value)) {
            await loadUserData()

            const notificationMsg: {
                type: "success" | "info" | "warning" | "danger"
                message: string
            } = getNoticeOfBalanceOver(userData.value.remaining_order || 0)
            
            if(notificationMsg){
                console.log({notificationMsg})
                showNotification(notificationMsg, false)
            }
            
            if(isArray(userData.value.notice) && userData.value?.notice?.length){
                userData.value.notice.forEach((item: {
                    type: string,
                    message: string
                }) => {
                    showNotification(item)
                })
            }
        }
    })

    provide('useServiceProvider', _useServiceProvider)
</script>