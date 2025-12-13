<template>
    <MessageBox
        title="
            160 characters are counted as 1sms in case of english language & 70 in other language.
            <br/>
            One simple text message containing extended GSM character set (-^()[]) is of 70 characters long.
            <br/>
            <span class='bg-red-500 inline-block px-1 text-[9px] text-white'>Check your SMS Count before sending sms.</span>
        "
        type="info"
        class="hidden md:flex"
    />
    <div class="max-w-[500px] mx-auto">
        <MessageBox
            :title="alertMessage.message"
            :type="alertMessage.type"
        />
        <Loader
            class="absolute inset-1/2 z-20"
            :active="isLoading"
        />
        <Heading
            class="mb-3"
            title="Send message"
        />
    
        <div class="grid gap-4">
            <Select.Primary
                label="Select Status *"
                :options="wooStatuses"
                itemKey="slug"
                v-model="form.status"
                @change="loadPhoneNumbers(form.status)"
            />
            <Input.Primary
                label="Phone numbers *"
                v-model="form.phone_numbers"
                placeholder="Enter your phone numbers, separated by comma."
            />
            <p class="font-semibold -mt-3 text-[10px] text-sky-500">
                Total phone number(s): {{ form.phone_numbers.replace(/^,+|,+$/g, '').split(',')?.length || 0 }}
            </p>

            <Textarea.Native
                label="Message *"
                placeholder="Write message"
                v-model="form.message"
            />
            <p v-if="form?.message?.length" class="font-semibold -mt-3 text-[10px] text-orange-500">
                {{ messageCountData.remainingCharacter }} character remaining, 
                {{  messageCountData.totalSMS }} SMS
            </p>
        </div>
    
        <slot name="btn">
            <Button.Primary
                class="mt-4 ml-auto"
                @onClick="btn => handleSendSMS(btn, form)"
            >
                Send SMS
            </Button.Primary>
        </slot>
    </div>
</template>
<script setup lang="ts">
    import { computed, inject, onMounted } from 'vue'
    import { 
        Select, 
        Textarea, 
        Button, 
        Heading, 
        Loader, 
        MessageBox,
        Input 
    } from '@components'
    import { calculateSMSDetails } from '@/helper'

    const {
        form,
        wooStatuses,
        loadPhoneNumbers,
        handleSendSMS,
        isLoading,
        alertMessage,
        loadWooStatuses
    } = inject('useSms')


    const messageCountData = computed(() => calculateSMSDetails(form.value.message))

    onMounted(async () => {
        await loadWooStatuses()
    })
</script>