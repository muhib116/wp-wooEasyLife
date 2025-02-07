<template>
    <Card.Native class="relative min-h-[200px] !py-10">
        <LicenseAlert />

        <Heading
            title="Get Started with Your License Key Today"
            subtitle="Activate Your License Key to Unlock Full Access"
        />
        
        <br/>
        <br/>

        <Loader
            :active="isLoading"
            class="absolute inset-x-1/2 -translate-x-1/2 z-20"
        />

        <div 
            class="grid gap-4 items-end mb-10 p-5 rounded"
            :class="isValidLicenseKey ? 'bg-green-50 border border-green-400' : 'bg-red-50/50 border border-red-400'"
        >
            <Input.Primary
                label="Enter License Key"
                placeholder="fd0xxxxxxxxxxxxxxxxxxxxxxxxxx98yhj"
                v-model="licenseKey"
                :disabled="isValidLicenseKey"
            />
            <Button.Primary 
                class="ml-auto"
                :class="!licenseKey?.trim() || !isValidLicenseKey ? '!bg-green-500' : '!bg-red-500'"
                @onClick="(btn) => {
                    ActivateLicense(btn, isValidLicenseKey)
                }"
            >
                {{ isValidLicenseKey ? 'Deactivate License' : 'Activate License' }}
            </Button.Primary>
        </div>
        
        <hr class="mt-10 mb-6" />
        <div class="space-y-1 flex justify-center gap-6 -mb-2 text-lg">
            <a
                class="flex items-center gap-3"
                href="tel:+8801789909958"
            >
                <Icon
                    class="bg-blue-500 text-white p-1 rounded-full shadow"
                    name="PhPhone"
                    size="35"
                />
                +880 1789-909958
            </a>

            <a 
                class="flex items-center gap-3"
                href="https://wa.me/+8801789909958"
                target="_blank"
            >
                <Icon
                    class="bg-green-500 text-white p-1 rounded-full shadow"
                    name="PhWhatsappLogo"
                    size="35"
                />
                +880 1789-909958
            </a>

            <a 
                class="flex items-center gap-3"
                href="https://www.facebook.com/profile.php?id=61571827960541"
                target="_blank"
            >
                <Icon
                    class="bg-[#0766ff] text-white p-1 rounded-full shadow"
                    name="PhFacebookLogo"
                    size="35"
                />
                Visit our facebook page
            </a>
        </div>
    </Card.Native>
</template>

<script setup lang="ts">
    import { Card, Input, Icon, Loader, Button, Heading } from '@components'
    import { useLicense } from './UseLicense'
    import LicenseAlert from './LicenseAlert.vue'
    import { inject } from 'vue'

    const {
        isLoading,
        ActivateLicense,
    } = useLicense()
    const {
        licenseKey,
        isValidLicenseKey
    } = inject('useServiceProvider')
</script>