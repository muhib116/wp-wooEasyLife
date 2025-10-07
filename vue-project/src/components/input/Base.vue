<template>
    <div :class="wrapperClass">
        <Label
            v-if="label"
            :for="uid"
            :class="labelClass"
            :style="{ ...labelStyle }"
        >
            {{ label }}
        </Label>

        <div
            class="relative"
            :class="[inputClass, iconPosition == 'left' && 'flex-row-reverse']"
            :style="{ gap: iconGap }"
        >
            <span
                class="absolute inset-0"
                :style="{ ...bgStyle }"
            ></span>
            <Button.Native
                v-if="!hideMicrophone"
                class="absolute z-10 right-1 top-1/2 -translate-y-1/2 size-[25px] grid place-content-center rounded-full opacity-50 hover:opacity-100"
                @click="handleVoiceToText"
                :loading="isRecognizing"
            >
                <Icon
                    name="PhMicrophone"
                    size="20"
                />
            </Button.Native>
            <div class="flex relative z-10 items-center gap-2 w-full">

                <Native
                    v-model="localValue"
                    v-bind="$attrs"
                    :id="uid"
                    class="bg-transparent !w-full pr-4"
                    :style="inputStyle"
                />
                <Icon
                    :name="iconName"
                    :source="iconSource"
                    :color="iconColor"
                    :size="iconSize"
                    :weight="iconWeight"
                    :mirrored="iconMirrored"
                    class="cursor-pointer select-none"
                    :class="iconClass"
                    @click="$emit('iconClick')"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
    import Native from './ui/Native.vue'
    import Label from './Label.vue'
    import { Icon, Button } from '@components'
    import { provide, getCurrentInstance, computed, useAttrs } from 'vue'
    import { useVoiceToText } from '@/service/useVoiceToText.ts'
    
    const {
        isRecognizing,
        startSpeechRecognition
    } = useVoiceToText()
    const instance = getCurrentInstance()
    const attrs = useAttrs()
    
    defineOptions({
        name: 'BaseInput',
        inheritAttrs: false,
    })

    const props = defineProps({
        wrapperClass: String,
        labelClass: String,
        labelStyle: Object,
        inputStyle: Object,
        bgStyle: Object,
        inputClass: String,
        iconClass: String,
        iconSource: String,
        iconName: String,
        iconColor: String,
        iconSize: String,
        iconWeight: String,
        iconMirrored: Boolean,
        iconGap: {
            type: String,
            default: '8px',
        },
        label: String,
        iconPosition: String,
        hideMicrophone: Boolean
    })
    const uid = computed(() => attrs?.id || `component_id_${instance.uid}`)

    provide('props', props) // ignore

    const localValue = defineModel()
    const handleVoiceToText = () => {
        startSpeechRecognition((text) => {
            localValue.value = text
        })
    }
</script>
