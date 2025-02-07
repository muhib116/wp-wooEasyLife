<template>
<Table.Tr>
    <Table.Td class="capitalize">
        {{ index + 1 }}
    </Table.Td>
    <Table.Td class="capitalize">{{ objKey.replaceAll('_', ' ') }}</Table.Td>
    <Table.Td>
        <Input.Primary
            v-if="objKey == 'daily_order_place_limit_per_customer'"
            wrapperClass="w-20"
            type="number"
            v-model="configData[objKey]"
        />
        <Input.Primary
            v-else-if="objKey == 'admin_phone'"
            label="to get sms"
            wrapperClass="w-[220px]"
            type="text"
            v-model="configData[objKey]"
            placeholder="Enter a valid phone number."
        />
        <Input.Primary
            v-else-if="objKey == 'invoice_company_name'"
            wrapperClass="w-[220px]"
            type="text"
            v-model="configData[objKey]"
            placeholder="Enter a company name."
        />
        <Input.Primary
            v-else-if="objKey == 'invoice_logo'"
            wrapperClass="w-[220px]"
            type="text"
            v-model="configData[objKey]"
            placeholder="Enter logo url."
        />
        <Input.Primary
            v-else-if="objKey == 'invoice_phone'"
            wrapperClass="w-[220px]"
            type="text"
            v-model="configData[objKey]"
            placeholder="Enter a valid phone number."
        />
        <Input.Primary
            v-else-if="objKey == 'invoice_email'"
            wrapperClass="w-[220px]"
            type="text"
            v-model="configData[objKey]"
            placeholder="Enter a valid email."
        />
        <Switch
            v-else
            v-model="configData[objKey]"
            @onInput="UpdateConfig()"
        />
    </Table.Td>
</Table.Tr>
</template>

<script setup lang="ts">
    import { Table, Input, Switch } from '@components'
    import { inject } from 'vue'

    const {
        configData,
        UpdateConfig
    } = inject('useIntegration')

    defineProps<{
        index: number,
        objKey: string,
    }>()
</script>