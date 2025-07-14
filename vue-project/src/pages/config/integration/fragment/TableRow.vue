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
            label="to get sms or call"
            wrapperClass="w-[220px]"
            type="tel"
            v-model="configData[objKey]"
            placeholder="Enter a valid phone number."
        />
        <div
            v-else-if="objKey == 'whatsapp_phone'"
            class="grid"
        >
            <Input.Primary
                label="To enabled whatsapp message"
                wrapperClass="w-[220px]"
                type="tel"
                v-model="configData[objKey]"
                placeholder="ex: +88017xxxxxxxx"
            />
            <div class="p-2 mt-1 rounded-md max-w-md font-medium">
                To get WhatsApp URL, Number use: <code class="bg-green-200 text-green-900 px-2 py-1 rounded">[whatsapp_url]</code> <code class="bg-green-200 text-green-900 px-2 py-1 rounded">[whatsapp_number]</code> shortcode
            </div>
        </div>
        <Input.Primary
            v-else-if="objKey == 'whatsapp_default_message' && configData['whatsapp_phone']"
            label="Write a default message"
            wrapperClass="w-[220px]"
            type="tel"
            v-model="configData[objKey]"
            placeholder="ex: Hi! I’m interested"
        />
        <div
            v-else-if="objKey == 'validate_duplicate_order'"
            @click="handle_config_for_duplicate_order_validation(objKey)"
        >
            <Switch
                v-model="configData[objKey]"
                disabled
            />
        </div>
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
    import { normalizePhoneNumber, showNotification, validateBDPhoneNumber } from '@/helper';
    import { Table, Input, Switch } from '@components'
    import { inject } from 'vue'
    import { set, get } from 'lodash'

    const {
        configData,
        UpdateConfig
    } = inject('useIntegration')

    defineProps<{
        index: number,
        objKey: string,
    }>()

    const handle_config_for_duplicate_order_validation = async (objKey: string) => {
        if(get(configData, `value.${objKey}`) == 0 && !validateBDPhoneNumber(normalizePhoneNumber(configData.value['admin_phone'].trim()))) {
            showNotification({
                type: 'danger',
                message: 'Please add the admin\'s valid phone number to enable this option.'
            })
            return
        }

        if(get(configData, `value.${objKey}`) == 0){
            set(configData, `value.${objKey}`, 1)
        }else {
            set(configData, `value.${objKey}`, 0)
        }
        await UpdateConfig()
    }
</script>