import { getDashboardData, getAbandonedOrders, updateAbandonedOrderStatus, createOrder, getProduct } from "@/api"
import { normalizePhoneNumber, showNotification, validateBDPhoneNumber } from "@/helper"
import { computed, onMounted, ref } from "vue"

// Add proper type definitions
interface Product {
    product_id: string | number;
    quantity: string | number;
    variation_id?: string | number;
    product_meta?: any;
}

interface ValidProduct {
    id: number;
    quantity: number;
    name: string;
    price: number;
    stock_status: string;
}

interface InvalidProduct {
    id: number;
    error: string;
    [key: string]: any;
}

interface CartContents {
    products: Product[];
    payment_method_id?: string;
    shipping_method?: string;
    shipping_cost?: string | number;
    customer_note?: string;
    coupon_codes?: string[];
}

interface AbandonedOrder {
    id: number;
    customer_name: string;
    customer_phone: string;
    customer_email?: string;
    billing_address?: string;
    cart_contents: CartContents;
    status: string;
    [key: string]: any;
}

interface OrderFilter {
    page: number;
    per_page: number;
    status: string;
    search: string;
    start_date: string | null;
    end_date: string | null;
}

interface DashboardData {
    total_abandoned_orders: number;
    total_remaining_abandoned: number;
    lost_amount: number;
    total_recovered_orders: number;
    recovered_amount: number;
    total_active_carts: number;
    total_confirmed_orders: number;
    total_call_not_received_orders: number;
    average_cart_value: number;
}

interface Option {
    title: string;
    id: string;
    color: string;
}

export const useMissingOrder = () => {
    const isLoading = ref<boolean>(false)
    const abandonOrders = ref<AbandonedOrder[]>([])
    const options = ref<Option[]>([
        {
            title: 'Abandoned',
            id: 'abandoned',
            color: '#8cc520'
        },
        {
            title: 'Call not received',
            id: 'call-not-received',
            color: '#f97315'
        },
        {
            title: 'Confirmed',
            id: 'confirmed',
            color: '#00b002'
        },
        {
            title: 'Delete',
            id: 'canceled',
            color: '#e82661'
        }
    ])

    const dashboardData = ref<DashboardData>({
        total_abandoned_orders: 0,
        total_remaining_abandoned: 0,
        lost_amount: 0,
        total_recovered_orders: 0,
        recovered_amount: 0,
        total_active_carts: 0,
        total_confirmed_orders: 0,
        total_call_not_received_orders: 0,
        average_cart_value: 0
    })
    
    const totalRecords = ref<number>(0)
    const currentPage = ref<number>(1)
    const totalPages = ref<number>(0)
    const orderFilter = ref<OrderFilter>({
        page: 1,
        per_page: 20,
        status: "abandoned",
        search: "",
        start_date: null,
        end_date: null
    });

    const selectedOption = ref<Option>(options.value[0])

    // Product existence check function
    const checkProductsExistence = async (productsInCart: Product[]): Promise<{ validProducts: ValidProduct[], invalidProducts: InvalidProduct[] }> => {
        const validProducts: ValidProduct[] = [];
        const invalidProducts: InvalidProduct[] = [];
        
        for (const product of productsInCart) {
            try {
                const productId = 252//parseInt(String(product.product_id));
                const quantity = parseInt(String(product.quantity));
                
                // Basic validation first
                if (!productId || isNaN(productId) || productId <= 0) {
                    invalidProducts.push({
                        ...product,
                        id: productId,
                        error: 'Invalid product ID'
                    } as InvalidProduct);
                    continue;
                }
                
                if (!quantity || isNaN(quantity) || quantity <= 0) {
                    invalidProducts.push({
                        ...product,
                        id: productId,
                        error: 'Invalid quantity'
                    } as InvalidProduct);
                    continue;
                }
                
                // Check if product exists in WooCommerce
                const productExists = await getProduct(productId);
                console.log(`Checked product ${productId}:`, productExists);
                
                if (productExists && productExists.id) {
                    validProducts.push({
                        id: productId,
                        quantity: quantity,
                        name: productExists.name || `Product #${productId}`,
                        price: productExists.price || 0,
                        stock_status: productExists.stock_status || 'instock'
                    });
                } else {
                    invalidProducts.push({
                        ...product,
                        id: productId,
                        error: 'Product not found in store'
                    } as InvalidProduct);
                }
                
            } catch (error: any) {
                console.error(`Error checking product ${product.product_id}:`, error);
                invalidProducts.push({
                    ...product,
                    id: parseInt(String(product.product_id)),
                    error: error?.response?.status === 404 ? 'Product not found' : 'Product not available'
                } as InvalidProduct);
            }
        }
        
        return { validProducts, invalidProducts };
    };

    const createOrderFromAbandonedData = async (form: AbandonedOrder, btn: any): Promise<number | string> => {
        // Enhanced validation
        const validationErrors: string[] = [];
        
        if (!form?.customer_phone?.trim()) {
            validationErrors.push('Customer phone number is required');
        } else if (!validateBDPhoneNumber(normalizePhoneNumber(form.customer_phone.trim()))) {
            validationErrors.push('Phone number is not valid bangladeshi number');
        }
        
        if (!form?.customer_name?.trim()) {
            validationErrors.push('Customer name is required');
        }
        
        if (!form?.cart_contents?.products?.length) {
            validationErrors.push('No products found in cart');
        }
        
        if (validationErrors.length > 0) {
            showNotification({
                type: 'danger',
                message: validationErrors.join(', ')
            });
            throw new Error(validationErrors.join(', '));
        }

        try {
            // Only set loading if btn has isLoading property
            if (btn && typeof btn.isLoading !== 'undefined') {
                btn.isLoading = true;
            }
            
            // Check product existence first
            showNotification({
                type: 'info',
                message: 'Checking product availability...'
            });
            
            const { validProducts, invalidProducts } = await checkProductsExistence(form.cart_contents.products);
            
            // Enhanced product validation handling
            if (invalidProducts.length > 0) {
                const invalidProductMessages = invalidProducts.map(p => 
                    `Product ID ${p.id || 'unknown'}: ${p.error}`
                );
                
                console.warn('Invalid products found:', invalidProducts);
                
                if (validProducts.length === 0) {
                    // All products are invalid
                    const errorMessage = `Cannot create order. All products are unavailable:\n${invalidProductMessages.join('\n')}`;
                    showNotification({
                        type: 'danger',
                        message: errorMessage
                    });
                    throw new Error('No valid products available for order creation');
                } else {
                    // Some products are invalid, show warning but continue
                    showNotification({
                        type: 'warning',
                        message: `Some products are unavailable and will be excluded from the order:\n${invalidProductMessages.join('\n')}`
                    });
                }
            }
            
            if (validProducts.length === 0) {
                throw new Error('No valid products found after verification');
            }

            // Prepare address data
            const address = [
                { first_name: form.customer_name.trim() },
                { last_name: '' },
                { address_1: form.billing_address?.trim() || '' },
                { address_2: '' },
                { phone: normalizePhoneNumber(form.customer_phone.trim()) },
                { email: form.customer_email?.trim() || '' }
            ];

            // Prepare order payload
            const payload = {
                products: validProducts,
                address,
                payment_method_id: form?.cart_contents?.payment_method_id || 'cod',
                shipping_method_id: form?.cart_contents?.shipping_method || '',
                shipping_cost: parseFloat(String(form?.cart_contents?.shipping_cost || 0)),
                customer_note: form?.cart_contents?.customer_note?.trim() || '',
                order_source: 'abandoned',
                order_status: 'wc-confirmed',
                coupon_codes: Array.isArray(form?.cart_contents?.coupon_codes) 
                    ? form.cart_contents.coupon_codes.filter((code: string) => code && code.trim())
                    : []
            };

            console.log('📤 Creating order with validated products:', {
                total_products: validProducts.length,
                products: validProducts.map(p => `${p.name} (ID: ${p.id}, Qty: ${p.quantity})`),
                customer: form.customer_name,
                phone: form.customer_phone
            });

            // Create the order
            const response = await createOrder(payload);
            
            console.log('📥 Order creation response:', response);
            
            // Handle different response structures
            let orderId: string | number | null = null;
            const responseData = response?.data || response;
            
            if (responseData?.order_id) {
                orderId = responseData.order_id;
            } else if (responseData?.data?.order_id) {
                orderId = responseData.data.order_id;
            } else if (responseData?.id) {
                orderId = responseData.id;
            } else if (response?.order_id) {
                orderId = response.order_id;
            }
            
            if (orderId) {
                const successMessage = validProducts.length === form.cart_contents.products.length
                    ? `✅ Order #${orderId} created successfully with all ${validProducts.length} products!`
                    : `⚠️ Order #${orderId} created with ${validProducts.length} out of ${form.cart_contents.products.length} products!`;
                
                showNotification({
                    type: 'success',
                    message: successMessage
                });
                
                return orderId;
            } else {
                console.error('❌ No order ID found in response:', response);
                throw new Error('No order ID returned from create-custom-order API');
            }

        } catch (error: any) {
            console.error('❌ Order creation error:', error);
            console.error('❌ Error details:', {
                message: error?.message,
                response: error?.response?.data,
                status: error?.response?.status
            });
            
            let errorMessage = 'Order creation failed!';
            
            if (error?.response?.data?.message) {
                errorMessage = error.response.data.message;
            } else if (error?.message) {
                errorMessage = error.message;
            }
            
            // Add more specific error handling
            if (errorMessage.toLowerCase().includes('product not found')) {
                errorMessage = 'Some products in the cart are no longer available. Please check the cart contents.';
            }
            
            showNotification({
                type: 'danger',
                message: errorMessage
            });
            
            throw error;
            
        } finally {
            if (btn && typeof btn.isLoading !== 'undefined') {
                btn.isLoading = false;
            }
        }
    }

    const updateStatus = async (item: AbandonedOrder, selectedStatus: string, btn: any): Promise<void> => {
        if (!selectedStatus) {
            showNotification({
                type: 'danger',
                message: 'Please select an item from dropdown.'
            })
            return
        }
        
        try {
            isLoading.value = true
            btn.isLoading = true
            
            let orderCreated = false;
            let orderId: string | number | null = null;
            
            // If status is confirmed, create the order first
            if (selectedStatus === 'confirmed') {
                try {
                    orderId = await createOrderFromAbandonedData(item, { isLoading: false });
                    orderCreated = true;
                } catch (orderError) {
                    console.error('Order creation failed:', orderError);
                    
                    // Don't proceed with status update if order creation fails
                    showNotification({
                        type: 'danger',
                        message: 'Failed to create order. Status will not be updated.'
                    });
                    return; // Exit early
                }
            }
            
            // Update the abandoned order status
            const payload = {
                ...item,
                status: selectedStatus,
                ...(orderId && { wc_order_id: orderId })
            }
            
            const { message } = await updateAbandonedOrderStatus(item.id, payload)
            
            showNotification({
                type: 'success',
                message: orderCreated ? `Status updated and order #${orderId} created successfully!` : message
            })

            await loadAbandonedOrder()
            
        } catch (error: any) {
            console.error('Status update error:', error);
            showNotification({
                type: 'danger',
                message: error?.response?.data?.message || 'Status update failed!'
            })
        } finally {
            isLoading.value = false
            btn.isLoading = false
        }
    }

    const handleFilter = (option: Option): void => {
        orderFilter.value.status = option.id
        selectedOption.value = option
        loadAbandonedOrder()
    }

    const loadAbandonedOrder = async (): Promise<void> => {
        try {
            isLoading.value = true
            if (orderFilter.value.page === 0) {
                orderFilter.value.page = 1;
            }

            const { data, pagination } = await getAbandonedOrders(orderFilter.value)
            totalRecords.value = pagination.total_count
            currentPage.value = pagination.current_page
            totalPages.value = pagination.total_pages
            abandonOrders.value = data
        } catch (error: any) {
            console.error('Error loading abandoned orders:', error);
            showNotification({
                type: 'danger',
                message: 'Failed to load abandoned orders'
            })
        } finally {
            isLoading.value = false
        }
    }

    const loadDashboardData = async (date?: any): Promise<void> => {
        try {
            isLoading.value = true
            const { data } = await getDashboardData(date)
            dashboardData.value = data
        } catch (error: any) {
            console.error('Error loading dashboard data:', error);
            showNotification({
                type: 'danger',
                message: 'Failed to load dashboard data'
            })
        } finally {
            isLoading.value = false
        }
    }

    onMounted(() => {
        loadAbandonedOrder()
    })

    return {
        options,
        isLoading,
        totalPages,
        orderFilter,
        currentPage,
        totalRecords,
        dashboardData,
        abandonOrders,
        selectedOption,
        updateStatus,
        handleFilter,
        loadDashboardData,
        loadAbandonedOrder,
        createOrderFromAbandonedData,
        checkProductsExistence,
    }
}