<template>
    <div class="max-w-[450px] mx-auto space-y-4 relative">
        <MessageBox
            title="স্ট্যাটাস ইংরেজিতে লিখুন। "
            type="warning"
        />
        <MessageBox
            :title="alertMessage?.message"
            :type="alertMessage?.type"
        />

        <Heading
            v-if="!hideTitle"
            title="Create new status"
            class="mb-2"
        />

        <Input.Primary
            label="Title *"
            placeholder="Enter title"
            :modelValue="form.title"
            @update:modelValue="onTitleUpdate"
        />

        <div class="space-y-1">
            <Input.Primary
                label="Slug *"
                placeholder="Enter slug"
                disabled
                :modelValue="generateSlug(form.title)"
                wrapper-class="[&>*+*]:bg-gray-100"
            />
            <span v-if="generateSlug(form.title)?.length" class="bg-red-500 text-white p-1 rounded">
                {{ generateSlug(form.title)?.length || 0 }}/{{ maxSlugLength }}
            </span>
        </div>

        <div class="px-1">
            <ColorPicker 
                label="Select Color *"
                v-model="form.color"
            />
        </div>
        
        <Input.Primary
            label="Description"
            v-model="form.description"
        />

        <slot 
            name="btn"
            v-if="!hideSubmitBtn"
        >
            <Button.Primary
                @onClick="handleCustomStatusCreate"
                class="ml-auto"
            >
                Create Status
            </Button.Primary>
        </slot>
    </div>
</template>

<script setup lang="ts">
    import { Input, ColorPicker, Heading, Button, MessageBox } from '@components'
    import { computed, inject, ref } from 'vue'
    import { generateSlug } from '@/helper'

    const {
        form,
        alertMessage,
        handleCustomStatusCreate
    } = inject('useCustomStatus')
    const maxSlugLength = 17
    const hideSubmitBtn = ref(false)

    const onTitleUpdate = (value: string) => {
        // Validation rules
        const allowedPattern = /^[A-Za-z0-9\s\-_]+$/ // allow English letters, numbers, spaces and common delimiters

        // If input is empty, clear alerts and hide submit button
        if (!value || value.trim() === '') {
            if (alertMessage && 'value' in alertMessage) {
                alertMessage.value.message = ''
            }
            hideSubmitBtn.value = true
            // don't update the model with empty value
        } else if (!allowedPattern.test(value || '')) {
            if (alertMessage && 'value' in alertMessage) {
                alertMessage.value.message = 'Only English letters, numbers and - _  characters are allowed.'
                alertMessage.value.type = 'danger'
            }
            hideSubmitBtn.value = true
            return
        }

        // 2) Compute slug length using the same slug generator used elsewhere
        const slug = generateSlug(value || '') || ''
        const length = slug.length || 0

        if (length > maxSlugLength) {
            // Show warning and prevent updating the model
            if (alertMessage && 'value' in alertMessage) {
                alertMessage.value.message = `Slug too long (${length}/${maxSlugLength}). Please shorten the title.`
                alertMessage.value.type = 'danger'
            }
            hideSubmitBtn.value = true
            return
        }

        // Clear any existing alert and enable submit
        hideSubmitBtn.value = false
        if (alertMessage && 'value' in alertMessage) {
            alertMessage.value.message = ''
        }

        // Accept and update the title
        if (form && 'value' in form) {
            form.value.title = value
        }
    }

    defineProps<{
        hideTitle: boolean
    }>()

    // const getSlug = computed(() => generateSlug(form.value.title))
</script>