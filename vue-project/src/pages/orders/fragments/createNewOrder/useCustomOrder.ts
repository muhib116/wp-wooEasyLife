import { createOrder, getProducts, validateCoupon } from "@/api"
import { normalizePhoneNumber, validateBDPhoneNumber } from "@/helper";
import { computed, onMounted, ref, inject } from "vue"

export const useCustomOrder = () => 
{
    const { getOrders, toggleNewOrder } = inject('useOrders');

    const products = ref([])
    const productSearchKey = ref('')
    const couponValidationErrorMessage = ref('')
    const appliedCoupon = ref('')
    const couponDiscount = ref(0);
    const placeHolderData = {
        order_status: 'wc-confirmed',
        first_name: '',
        last_name: '',
        address_1: '',
        address_2: '',
        phone: '',
        customer_note: '',
        created_via: '',
        products: [],
        shippingMethod: {},
        paymentMethod: {},
        coupons: []
    }

    const form = ref({...placeHolderData})
    const filteredProducts = computed(() => {
        if(productSearchKey.value){
            return products.value.filter(item => {
                const searchKey = productSearchKey.value?.toLowerCase();
                return item.name?.toLowerCase().includes(searchKey) || item.id?.toString().toLowerCase().includes(searchKey);
            });            
        }

        return products.value
    })

    const isLoading = ref(false)
    const loadProducts = async () => {
        try {
            isLoading.value = true
            const { data } = await getProducts()
            products.value = data
        } finally {
            isLoading.value = false
        }
    }

    const addProductToForm = (item) => {
        // Check existence of product
        const existProduct = form.value.products.find(productItem => {
            return productItem.product.id === item.id
        })
    
        if (existProduct) {
            existProduct.quantity++
            return
        }
    
        form.value.products.push({
            product: item,
            quantity: 1
        })

        calculateCouponDiscountAmount(form.value.coupons)
    }

    const handleCouponValidation = async (btn) => {
        if(appliedCoupon.value == '') {
            couponValidationErrorMessage.value = 'Coupon code cannot be empty.'
            return
        }
        if(form.value.coupons.find(item => item.coupon_code == appliedCoupon.value)) {
            couponValidationErrorMessage.value = 'Coupon code already applied.'
            appliedCoupon.value = ''
            return
        }

        try {
            btn.isLoading = true
            const { data } = await validateCoupon({
                coupon_code: appliedCoupon.value
            })

            if(data){
                form.value.coupons.push(data)
                calculateCouponDiscountAmount(form.value.coupons)
                appliedCoupon.value = ''
            }
        } catch({ response }) {
            couponValidationErrorMessage.value = response?.data?.message
        } finally {
            btn.isLoading = false
        }
    }

    const calculateCouponDiscountAmount = (coupons: {
        "discount_type": string,
        "amount": number | string,
        "usage_limit": number,
        "usage_count": number,
        "expiry_date": string
    }[]) => {
        const calc_discounts_sequentially = coupons[0]?.calc_discounts_sequentially
        //write code here to apply coupon
        if(calc_discounts_sequentially){
            _discountsCalculationSequentially(coupons)
            return
        }
        _discountsCalculation(coupons)
    }

    const _discountsCalculation = (coupons: {
        "discount_type": string,
        "amount": number | string,
        "usage_limit": number,
        "usage_count": number,
        "expiry_date": string
    }[]) => {
        let totalDiscount = 0;

        coupons.forEach(coupon => {
            const { discount_type, amount } = coupon;
            // Apply discount based on type
            if (discount_type === "percent") {
                const discountPercent = parseFloat(amount);
                if (!isNaN(discountPercent)) {
                    totalDiscount += (getItemsTotal.value * discountPercent) / 100;
                } else {
                    console.warn(`Invalid percentage amount for coupon: ${amount}`);
                }
            } else if (discount_type === "fixed_cart") {
                const discountFixed = parseFloat(amount);
                if (!isNaN(discountFixed)) {
                    totalDiscount += Math.min(discountFixed, getItemsTotal.value); // Ensure the discount doesn't exceed the total
                } else {
                    console.warn(`Invalid fixed cart amount for coupon: ${amount}`);
                }
            } else if (discount_type === "fixed_product") {
                // Fixed product discounts typically apply per product. Adjust this logic as needed.
                const discountFixedProduct = parseFloat(amount);
                if (!isNaN(discountFixedProduct)) {
                    totalDiscount += Math.min(discountFixedProduct, getItemsTotal.value); // Ensure the discount doesn't exceed the total
                } else {
                    console.warn(`Invalid fixed product amount for coupon: ${amount}`);
                }
            } else {
                console.warn(`Unsupported discount type: ${discount_type}`);
            }
        });
        // Ensure discount does not exceed the total
        totalDiscount = Math.min(totalDiscount, getItemsTotal.value);
    
        couponDiscount.value = +totalDiscount.toFixed(2)

        return {
            totalDiscount: totalDiscount.toFixed(2),
            discountedTotal: (getItemsTotal.value - totalDiscount).toFixed(2),
        };
    }
    const _discountsCalculationSequentially = (coupons: {
        "discount_type": string,
        "amount": number | string,
        "usage_limit": number,
        "usage_count": number,
        "expiry_date": string
    }[]) => {
        let remainingTotal = getItemsTotal.value; // Start with the full items total
        let totalDiscount = 0;
    
        // Apply each coupon sequentially
        coupons.forEach(coupon => {
            const { discount_type, amount } = coupon;
    
            // Skip invalid amounts
            const discountAmount = parseFloat(amount);
            if (isNaN(discountAmount)) {
                console.warn(`Invalid discount amount for coupon: ${amount}`);
                return;
            }
    
            let appliedDiscount = 0;
    
            // Apply discount based on type
            if (discount_type === "percent") {
                // Percent discount
                appliedDiscount = (remainingTotal * discountAmount) / 100;
            } else if (discount_type === "fixed_cart") {
                // Fixed cart discount
                appliedDiscount = Math.min(discountAmount, remainingTotal); // Cannot exceed remaining total
            } else if (discount_type === "fixed_product") {
                // Fixed product discount
                appliedDiscount = Math.min(discountAmount, remainingTotal); // Typically applies per product; adjust logic as needed
            } else {
                console.warn(`Unsupported discount type: ${discount_type}`);
                return;
            }
    
            // Add applied discount to total discount and reduce remaining total
            totalDiscount += appliedDiscount;
            remainingTotal -= appliedDiscount;
    
            // Ensure remaining total doesn't go below zero
            remainingTotal = Math.max(remainingTotal, 0);
        });
    
        // Ensure total discount doesn't exceed original items total
        totalDiscount = Math.min(totalDiscount, getItemsTotal.value);
    
        // Update coupon discount value
        couponDiscount.value = +totalDiscount.toFixed(2);
    
        return {
            totalDiscount: totalDiscount.toFixed(2),
            discountedTotal: (getItemsTotal.value - totalDiscount).toFixed(2),
        };
    };
    
    const getItemsTotal = computed(() => {
        let total_amount = 0
        form.value.products.forEach(item => {
            total_amount += (+item.product.price * +item.quantity)
        })

        return total_amount
    })
    
    const handleCreateOrder = async (btn) => {
        if(
            form.value.first_name.trim() == '' || 
            form.value.phone.trim() == '' || 
            form.value.address_1.trim() == '' || 
            form.value.created_via.trim() == '' || 
            !form.value.shippingMethod?.method_id || 
            !form.value.paymentMethod?.id || 
            !form.value.products.length
        ){
            alert('The fields marked with an asterisk (*) are required and cannot be left empty!');
            return
        }

        if(!validateBDPhoneNumber(normalizePhoneNumber(form.value.phone.trim()))){
            alert('Please enter a valid bangladeshi number!')
            return
        }


        try {
            btn.isLoading = true
            const products = form.value.products.map(item => {
                return {
                    id: item.product.id,
                    quantity: item.quantity
                }
            })
            const address = [
                {first_name: form.value.first_name},
                {last_name: form.value.last_name},
                {address_1: form.value.address_1},
                {address_2: form.value.address_2},
                {phone: form.value.phone}
            ]
            const coupon_codes = form.value.coupons.map(item => item.coupon_code)
    
            const payload = {
                products: products,
                address,
                payment_method_id: form.value.paymentMethod.id,
                shipping_method_id: form.value.shippingMethod.method_id,
                shipping_cost: form.value.shippingMethod.shipping_cost,
                customer_note: form.value.customer_note,
                order_source: form.value.created_via,
                order_status: form.value.order_status,
                coupon_codes: coupon_codes
            }
    
            const { data } = await createOrder(payload)
            if(data.order_id){
                await getOrders()
                toggleNewOrder.value = false
            }
        } catch (err) {
            console.log({err})
        } finally {
            btn.isLoading = false
        }
    }

    onMounted(() => {
        if(!products.value.length){
            loadProducts()
        }
    })
    return {
        form,
        products,
        isLoading,
        getItemsTotal,
        appliedCoupon,
        couponDiscount,
        productSearchKey,
        filteredProducts,
        couponValidationErrorMessage,
        handleCreateOrder,
        loadProducts,
        handleCouponValidation,
        addProductToForm,
        calculateCouponDiscountAmount,
    }
}