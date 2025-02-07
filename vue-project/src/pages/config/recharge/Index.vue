<template>
    <Card.Native>
        <MessageBox
            v-if="alertMessage.title.trim()"
            :title="alertMessage.title"
            :type="alertMessage.type"
            :wait="alertMessage.wait"
            class="!fixed bottom-4 right-4 z-50"
            @onClose="alertMessage.title = ''"
        />
        <Heading
            title="Seamless Recharge for SMS Services"
            subtitle="Quick and Secure Payments to Keep Your SMS Services Running Smoothly"
            class="mb-4"
        />
        <hr class="mt-4 mb-6" />

        <div class="grid gap-4">
            <div
                v-for="(item, index) in data"
                :key="index"
            >
                <div
                    class="cursor-pointer selection-none flex justify-between items-center border pl-4"
                    @click="() => {
                        if(form.transaction_method == item.paymentPartner){
                            form.transaction_method = ''
                        }else {
                            form.transaction_method = item.paymentPartner
                        }
                    }"
                >
                    <h3 class="font-bold text-lg">
                        {{ item.paymentPartner }}
                    </h3>
        
                    <img
                        class="w-20"
                        :src="item.logo"
                    />
                </div>
    
                <div
                    v-if="form.transaction_method == item.paymentPartner"
                    class="p-5 border border-t-0 rounded-b text-lg"
                >
                    <div class="font-semibold">
                        <p>Account type: {{ item.accountType }}</p>
                        <p>Transaction fee: {{ item.fee }}%</p>
                    </div>
                    <br />

    
                    <div class="grid gap-4 p-8 rounded" :style="{backgroundColor: item.bg}">
                        <Input.Primary
                            :label="`Your ${item.paymentPartner} account number *`"
                            placeholder="01xxxxxxxxx"
                            v-model="form.accountNumber"
                            inputClass="!bg-white px-[10px] py-[6px] mt-1 rounded"
                        />
                        
                        <Input.Primary
                            label="Recharge amount *"
                            placeholder="Enter your amount!"
                            type="number"
                            :min="minRechargeAmount"
                            v-model="form.rechargeableAmount"
                            inputClass="!bg-white px-[10px] py-[6px] mt-1 rounded"
                        />
                        
                        <div
                            v-if="form.rechargeableAmount"
                            class="bg-green-100 text-green-600 w-fit px-3 py-1 rounded-1 text-xl"
                        >
                            You have to pay: 
                            {{ getPayableAmount(item.fee) }}
                        </div>
                        
                        <Input.Primary
                            placeholder="2M7A5"
                            :label="`Your ${item.paymentPartner} Transaction ID *`"
                            v-model="form.transactionId"
                            inputClass="!bg-white px-[10px] py-[6px] mt-1 rounded"
                        />
                    </div>
                    <br />
                    <div v-html="item.instructions"></div>
                    <br />

                    <Button.Primary @onClick="rechargeBalance">
                        Recharge Now
                    </Button.Primary>
                </div>
            </div>
        </div>
    </Card.Native>
</template>

<script setup lang="ts">
    import { useRecharge } from './useResharge'
    import {
        Input,
        Card,
        Heading,
        Button,
        MessageBox
    } from '@components'

    const {
        data,
        form,
        alertMessage,
        minRechargeAmount,
        rechargeBalance,
        getPayableAmount,
    } = useRecharge()
</script>