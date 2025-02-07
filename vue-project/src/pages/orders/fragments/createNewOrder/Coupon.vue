<template>
    <div>
        <button
            class="text-orange-500"
            @click="toggleCoupon = !toggleCoupon"
        >
            Add a coupon
        </button>

        <div v-if="toggleCoupon">
            <div 
                class="flex gap-2 mt-2"
            >
                <Input.Primary
                    placeholder="Enter coupon code."
                    wrapperClass="flex-1"
                    v-model="appliedCoupon"
                />
                <Button.Primary
                    @onClick="handleCouponValidation"
                >
                    Apply
                </Button.Primary>
            </div>
            <p 
                v-if="couponValidationErrorMessage"
                class="text-red-500 text-sm"
            >{{ couponValidationErrorMessage }}</p>

            <div class="flex flex-wrap gap-3 mt-2">
                <span
                    class="flex gap-1 border px-1 rounded text-sm text-gray-500 bg-gray-50"
                    v-for="(item, index) in form.coupons"
                    :key="index"
                >
                    {{ item.coupon_code }}
                    <button 
                        class="hover:text-red-500"
                        @click="() => {
                            form.coupons.splice(index, 1)
                            calculateCouponDiscountAmount(form.coupons)
                        }"
                    >
                        <Icon
                            name="PhX"
                        />
                    </button>
                </span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { Button, Input, Icon } from '@components'
    import { inject, ref } from 'vue'

    const toggleCoupon = ref(false)
    const {
        form,
        appliedCoupon,
        handleCouponValidation,
        couponValidationErrorMessage,
        calculateCouponDiscountAmount
    } = inject('useCustomOrder')
</script>