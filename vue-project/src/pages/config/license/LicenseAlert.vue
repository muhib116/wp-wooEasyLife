<template>
    <MessageBox
        v-if="licenseAlertMessage.message || licenseAlertMessage.title"
        :title="licenseAlertMessage.message || licenseAlertMessage.title"
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