<template>
    <MessageBox
        v-if="licenseAlertMessage?.message"
        :title="licenseAlertMessage.message"
        :type="licenseAlertMessage.type"
    />
</template>

<script setup lang="ts">
    import {
        MessageBox
    } from '@components'
    import { inject } from 'vue'

    const { licenseAlertMessage } = inject('useServiceProvider');
</script>