import { createOrder, validateCoupon, getShippingMethods, getPaymentMethods } from "@/api"
import { normalizePhoneNumber, showNotification, validateBDPhoneNumber } from "@/helper";
import { computed, ref, inject } from "vue"
import {
    shippingMethods, 
    paymentMethods
} from "@/storage"

const placeHolderData = {
    order_status: 'wc-processing',
    first_name: '',
    last_name: '',
    address_1: '',
    address_2: '',
    phone: '',
    customer_note: '',
    created_via: '',
    products: [],
    shippingMethod: {
        zone_name: '',
        method_id: '',
        instance_id: '',
        method_title: '',
        settings: [],
        shipping_cost: 0,
        title: ''
    },
    paymentMethod: {},
    cod_amount: null,
    coupons: []
}
const form = ref({...placeHolderData})

export const useCustomOrder = () => 
{
    const { getOrders, toggleNewOrder } = inject('useOrders');

    const couponValidationErrorMessage = ref('')
    const appliedCoupon = ref('')
    const couponDiscount = ref(0);
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


    const resetCustomOrderForm = () => {
        form.value = {...placeHolderData}
        appliedCoupon.value = ''
        couponDiscount.value = 0
        couponValidationErrorMessage.value = ''
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

            // Calculate total and COD
            const calculatedTotal = getItemsTotal.value - couponDiscount.value + (parseFloat(form.value.shippingMethod.shipping_cost) || 0);
            const CODAmount = form.value.cod_amount ? parseFloat(form.value.cod_amount) : null;
            const hasCodModification = CODAmount !== null && CODAmount !== calculatedTotal;

            const payload = {
                products: products,
                address,
                payment_method_id: form.value.paymentMethod.id,
                shipping_method_id: form.value.shippingMethod.method_id,
                shipping_cost: form.value.shippingMethod.shipping_cost,
                customer_note: form.value.customer_note,
                order_source: form.value.created_via,
                order_status: form.value.order_status,
                coupon_codes: coupon_codes,
                // Add COD amount if modified
                ...(hasCodModification && {
                    cod_amount: CODAmount,
                    add_order_note: true // Flag to add order note
                })
            }

            const { data } = await createOrder(payload)
            if(data.order_id){
                await getOrders()
                toggleNewOrder.value = false
                
                // Show success notification
                showNotification({
                    type: 'success',
                    message: hasCodModification 
                        ? `Order created successfully with modified COD amount: ${CODAmount}`
                        : 'Order created successfully'
                })
            }
        } catch (err) {
            console.log({err})
            showNotification({
                type: 'danger',
                message: err?.response?.data?.message || 'Failed to create order. Please try again.'
            })
        } finally {
            btn.isLoading = false
        }
    }













    // Define a proper type
    type ShippingMethod = {
        zone_name: string;
        method_id: string;
        instance_id: string;
        method_title: string;
        settings: any[];
        shipping_cost: number;
        title: string;
    };

    const EMPTY_SHIPPING_METHOD: ShippingMethod = {
        zone_name: '',
        method_id: '',
        instance_id: '',
        method_title: '',
        settings: [],
        shipping_cost: 0,
        title: ''
    };

    /**
     * Find shipping method by name from available shipping methods
     * Returns complete shipping method object with all fields
     * @param methodName - Name of the shipping method (e.g., "Free shipping")
     * @returns Complete shipping method object or empty object if not found
     */
    const _findShippingMethodIdByName = (methodName: string): ShippingMethod => {
        if (!methodName?.trim() || !shippingMethods.value?.length) {
            return { ...EMPTY_SHIPPING_METHOD };
        }

        const normalizedName = methodName.trim().toLowerCase();

        // First try exact match on method_title (more specific)
        let method = shippingMethods.value.find((m: any) =>
            m.method_title?.toLowerCase() === normalizedName
        );

        // If not found, try substring match (less specific, wider range)
        if (!method) {
            method = shippingMethods.value.find((m: any) =>
                m.title?.toLowerCase().includes(normalizedName)
            );
        }

        if (!method) {
            console.warn(
                `Shipping method "${methodName}" not found. Available: ${
                    shippingMethods.value.map((m: any) => m.method_title).join(', ')
                }`
            );
            return { ...EMPTY_SHIPPING_METHOD };
        }

        return {
            zone_name: method.zone_name || '',
            method_id: method.method_id || '',
            method_title: method.method_title || '',
            instance_id: method.instance_id || '',
            method_title: method.method_title || '',
            settings: method.settings || [],
            shipping_cost: method.shipping_cost || 0,
            title: method.title || ''
        };
    };

    /**
     * Clone an existing order to create a new order form
     * Supports new custom API order format
     */
    const cloneOrder = async (order: any, btn: { isLoading: boolean }) => {
        resetCustomOrderForm();
        
        if (!order) {
            showNotification({
                type: 'danger',
                message: 'Invalid order data. Cannot clone.'
            });
            btn.isLoading = false;
            return;
        }

        try {
            btn.isLoading = true;

            // Validate order has required fields
            if (!order.product_info?.product_info?.length) {
                throw new Error('Order must contain at least one product.');
            }

            const { 
                billing_address, 
                shipping_address, 
                product_info, 
                order_notes, 
                payment_method, 
                shipping_methods, 
                shipping_cost, 
                applied_coupons, 
                customer_custom_data 
            } = order;

            // Extract address with fallbacks (billing → shipping → customer_custom_data)
            const firstName = billing_address?.first_name || shipping_address?.first_name || customer_custom_data?.first_name || '';
            const lastName = billing_address?.last_name || shipping_address?.last_name || customer_custom_data?.last_name || '';
            const address1 = billing_address?.address_1 || shipping_address?.address_1 || '';
            const address2 = billing_address?.address_2 || shipping_address?.address_2 || '';
            const phone = billing_address?.phone || '';

            // Map products from new format
            const products = product_info.product_info.map((item: any) => ({
                product: {
                    id: item.id,
                    name: item.product_name,
                    price: item.product_price,
                    currency_symbol: item.currency_symbol || '',
                    regular_price: item.regular_price || item.product_price,
                    sale_price: item.sale_price || '',
                    sku: item.sku || '',
                    stock_status: item.stock_status || 'instock',
                    stock_quantity: item.stock_quantity,
                    in_stock: item.in_stock || true,
                    type: item.type || 'simple',
                    permalink: item.permalink || '',
                    image: item.product_image || item.image || '',
                    from: 'cloned-order'
                },
                quantity: item.product_quantity
            }));

            // Map shipping method with auto-lookup by name
            let shippingMethod = { ...EMPTY_SHIPPING_METHOD };
            shippingMethod.shipping_cost = shipping_cost || 0;

            if (shipping_methods?.length > 0) {
                const foundMethod = _findShippingMethodIdByName(shipping_methods[0]);
                if (foundMethod.method_id) {
                    shippingMethod = {
                        ...foundMethod,
                        shipping_cost: shipping_cost || 0,
                        title: `${foundMethod.zone_name}-${foundMethod.method_title}-(${foundMethod.shipping_cost})`
                    };
                } else {
                    showNotification({
                        type: 'warning',
                        message: `Shipping method "${shipping_methods[0]}" not found. Please select one manually.`
                    });
                }
            }

            // Map payment method (use injected paymentMethods if available, else fallback to API)
            let paymentMethodData = {};
            try {
                const methodsList = paymentMethods?.value || (await getPaymentMethods()).data || [];
                const foundPaymentMethod = methodsList.find((m: any) => m.id === payment_method);
                if (foundPaymentMethod) {
                    paymentMethodData = foundPaymentMethod;
                } else if (payment_method) {
                    throw new Error(`Payment method "${payment_method}" not found.`);
                }
            } catch (err) {
                console.warn('Payment method lookup failed:', err);
                showNotification({
                    type: 'warning',
                    message: 'Could not find payment method. Please select one manually.'
                });
            }

            // Build form data
            const formData = {
                ...placeHolderData,
                order_status: 'wc-processing',
                first_name: firstName,
                last_name: lastName,
                address_1: address1,
                address_2: address2,
                phone: phone,
                customer_note: order_notes?.customer_note || '',
                created_via: 'clone-order',
                products: products,
                shippingMethod: shippingMethod,
                paymentMethod: paymentMethodData,
                coupons: applied_coupons || [],
                cod_amount: order.total || 0
            };

            form.value = formData;

            calculateCouponDiscountAmount(form.value.coupons);
            toggleNewOrder.value = true;

            showNotification({
                type: 'success',
                message: 'Order cloned successfully. Review details and submit.'
            });
        } catch (err) {
            console.error('Error cloning order:', err);
            showNotification({
                type: 'danger',
                message: (err as any)?.message || 'Failed to clone order. Please try again.'
            });
        } finally {
            btn.isLoading = false;
        }
    };

    return {
        form,
        isLoading,
        getItemsTotal,
        appliedCoupon,
        couponDiscount,
        filteredProducts,
        couponValidationErrorMessage,
        handleCreateOrder,
        handleCouponValidation,
        addProductToForm,
        calculateCouponDiscountAmount,
        cloneOrder,
        resetCustomOrderForm,
    }
}