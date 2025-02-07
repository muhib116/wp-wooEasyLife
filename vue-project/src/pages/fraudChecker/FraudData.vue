<template>
    <div>
        <slot></slot>
        <Card.Native 
            :class="hideShadow ? '' : '!shadow-none'"
        >
            <h3 
                v-if="data?.report?.success_rate == '100%'"
                class="font-bold text-xl mb-4 text-center animate-bounce text-green-600"
            >
                🎉 The number has no fraud history! ✅
            </h3>

            <Table.Table
                v-if="data && data?.report?.total_order"
                class="w-full"
            >
                <Table.THead class="bg-gray-700 !text-gray-100">
                    <Table.Th>Courier Name</Table.Th>
                    <Table.Th class="text-center">Confirm</Table.Th>
                    <Table.Th class="text-center">Cancel</Table.Th>
                    <Table.Th class="text-center">Success Rate</Table.Th>
                </Table.Thead>
                <Table.TBody>
                    <Table.Tr
                        v-for="(item, index) in data.report.courier"
                        :key="index"
                    >
                        <Table.Th>{{ item.title }}</Table.Th>
                        <Table.Th class="!font-light text-center bg-green-100">{{ item.report.confirmed }}</Table.Th>
                        <Table.Th class="!font-light text-center bg-red-100">{{ item.report.cancel }}</Table.Th>
                        <Table.Th class="!font-light text-center bg-sky-100">{{ item.report.success_rate }}</Table.Th>
                    </Table.Tr>
                </Table.TBody>

                <Table.THead>
                    <Table.Th class="bg-gray-700 text-gray-100">Total</Table.Th>
                    <Table.Th class="bg-green-500 text-white text-center">
                        {{ data.report.confirmed }}
                    </Table.Th>
                    <Table.Th class="bg-red-500 text-white text-center">
                        {{ data.report.cancel }}
                    </Table.Th>
                    <Table.Th class="bg-sky-500 text-white text-center">
                        {{ data.report.success_rate }}
                    </Table.Th>
                </Table.Thead> 
            </Table.Table>

            <FraudProgress :data="data" />

            <div
                v-if="!data"
                class="py-10 font-bold text-xl mb-4 text-center text-red-600"
            >
                <fraudCheckImg
                    class="mx-auto max-w-[300px] mb-10"
                />
                <h3 class="animate-bounce">
                    🫣 Search for fraud using a phone number.
                </h3>
            </div>

            <div
                v-if="data && data?.report?.total_order == 0"
            >
                <fraudCheckImg
                    class="mx-auto max-w-[300px] mb-10"
                />
                <h3 class="font-bold text-xl mb-4 text-center">
                    🎉 The number has no data! ✅
                </h3>
            </div>
        </Card.Native>
    </div>
</template>

<script setup lang="ts">
    import { Table, Card } from '@components'
    import fraudCheckImg from './fraudCheckImg.vue'
    import FraudProgress from './FraudProgress.vue'

    withDefaults(
        defineProps<{
            hideShadow: boolean
            data: {
                report: {
                    total_order: number | string,
                    confirmed: string,
                    cancel: string,
                    success_rate: string,
                    courier: {
                        title: string
                        report: {
                            confirmed: string,
                            cancel: string,
                            success_rate: string,
                        }
                    }
                }
            }
        }>(), 
        {
            hideShadow: true
        }
    )
    
</script>