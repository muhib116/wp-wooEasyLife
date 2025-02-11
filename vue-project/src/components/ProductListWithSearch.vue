<template>
    <div class="border border-gray-200 p-4 pt-6 bg-gray-50 z-40 w-full mt-1 relative">
        <Loader
            :active="isLoading"
            class="absolute inset-1/2 -transform-x-1/2 -transform-y-1/2 z-50"
        />
        <Button.Native 
            class="absolute -right-1 -top-1 p-1 rounded bg-red-500 text-white z-20"
            @click="$emit('close')"
        >
            <Icon
                name="PhX"
                size="20"
            />
        </Button.Native>
        <Input.Primary
            placeholder="Search Product"
            v-model="productSearchKey"
            type="search"
            wrapperClass="!mr-3"
        />

        <div 
            v-if="filteredProducts?.length"
            class="max-h-[190px] overflow-auto mt-2 grid gap-3"
        >
            <div
                v-for="item in filteredProducts"
                :key="item.id"
                class="flex gap-4 py-1 items-center justify-between border  rounded pr-2 mr-1"
            >
                <div class="flex gap-4 items-center">
                    <img
                        :src="item.image"
                        class="size-10"
                    />
                    <p class="w-[170px] leading-[16px]">
                        #{{ item.id }} {{ item.name }}
                    </p>
                </div>

                <Button.Primary
                    class="px-[6px] py-[3px] !bg-green-500"
                    title="Add Product"
                    @onClick="btn => addProductToForm(item, btn)"
                >
                    Add
                </Button.Primary>
            </div>
        </div>
        <div v-else-if="!isLoading">
            <MessageBox
                title="No product found!"
                type="info"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
    import { Button, Icon, Input, MessageBox, Loader} from '@components'
    import { onMounted, ref, computed } from 'vue'
    import { getProducts } from '@/api'

    defineProps<{
        addProductToForm: (product: object) => void
    }>()

    const isLoading = ref(false)
    const productSearchKey = ref('')
    const products = ref<[]>()


    const loadProducts = async () => {
        try {
            isLoading.value = true
            const { data } = await getProducts()
            
            // default product quantity set to 1, to simplify product add in existing order
            products.value = data.map(product => {
                return {
                    ...product,
                    product_quantity: 0,
                    from: 'new-product'
                }
            })
        } finally {
            isLoading.value = false
        }
    }

    const filteredProducts = computed(() => {
        if(productSearchKey.value){
            return (products.value || []).filter(item => {
                const searchKey = productSearchKey.value?.toLowerCase();
                return item.name?.toLowerCase().includes(searchKey) || item.id?.toString().toLowerCase().includes(searchKey);
            });            
        }

        return products.value
    })

    onMounted(() => {
        if(!products.value?.length){
            loadProducts()
        }
    })
</script>