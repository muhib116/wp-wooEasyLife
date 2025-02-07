<template>
    <Container>
        <template #header>
            <Header />
            <ClientInfo
                :data="order.billing_address"
                :date="order.date_created"
                :courierData="order.courier_data"
            />
        </template>
        
        <template #table>
            <TableHeader />
            <TableBody
                :productInfo="order.product_info"
                :currency_symbol="order.currency_symbol"
            />
            <TableFooter
                :order="order"
            />
        </template>

        <template #footer>
            <Footer
                :order="order"
            />
        </template>
    </Container>
</template>

<script setup lang="ts">
    import Container from './fragments/Container.vue'
    import Header from './fragments/Header.vue'
    import ClientInfo from './fragments/ClientInfo.vue'
    import TableHeader from './fragments/TableHeader.vue'
    import TableBody from './fragments/TableBody.vue'
    import TableFooter from './fragments/TableFooter.vue'
    import Footer from './fragments/Footer.vue'

    defineProps<{
        order: {
            currency_symbol: string
            billing_address: {
                first_name: string
                last_name: string
                address_1: string
                address_2: string
                phone: string
                order_id: string
            }
            date_created: string
            courier_data: object
            product_info: {
                product_info: {
                    product_name: string
                    product_quantity: number
                    product_price: number
                    product_total: number
                }[]
            }
        }
    }>()
</script>