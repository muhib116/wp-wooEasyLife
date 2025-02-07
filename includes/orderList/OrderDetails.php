<style>
    #woo_easy_life_order_preview_popup_wrapper{
        background-color: #3335;
        padding: 30px;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;

        .woo_easy_popup_content{
            max-width: 1000px;
            width: 100%;
            height: 80vh;
            overflow: auto;
            background-color: #fff;
            position: relative;

            .woo_easy_close-popup{
                position: absolute;
                right: 10px;
                top: 10px;
                font-size: 30px;
                width: 30px;
                height: 30px;
                display: block;
                line-height: 10px;
                background: transparent;
                border: none;
                font-weight: 200;
                cursor: pointer;
            }
            .woo_easy_close-popup:hover{
                color: red;
            }

            .woo_easy_header {
                padding: 15px 20px;
                border-bottom: 1px solid #c1c1c1;
                display: flex;
                justify-content: space-between;
                align-items: center;
                h3 {
                    margin: 0;
                    font-size: 18px;
                    font-weight: bold;
                }
            }

            #woo_easy_life_order_details{
                padding: 20px;
                .woo_easy_customer_details{
                    h3.title{
                        font-weight: 600;
                        margin: 0;
                        margin-bottom: 10px;
                    }
                    h4{
                        margin: 0;
                        font-weight: 300;
                    }
                    .woo_easy_customer_info{
                        display: grid;
                        grid-template-columns: auto 1fr;
                        gap: 4px;
                    }
                    .woo_easy_order_list{
                        overflow: auto;
                        table{
                            width: 100%;
                            border-collapse: collapse;
                        }
                        table, td, th {
                            border: 1px solid #c1c1c1;
                        }
                    }
                }
            }
        }

        .woo_easy_loader{
            position: absolute;
            z-index: 4;
            animation: spin 1s linear infinite;
        }
    }

    .woo_easy_multi_order_btn{
        transition: 0.3 ease-in-out;
    }
    .woo_easy_multi_order_btn:hover{
        transform: scale(1.4);
    }
    @keyframes spin {
        from {
            transform: rotate(0deg); /* Start at 0 degrees */
        }
        to {
            transform: rotate(360deg); /* Complete a full rotation */
        }
    }
</style>
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>

<div id="woo_easy_app" >
    <div v-if="toggleModal" id="woo_easy_life_order_preview_popup_wrapper">
        <svg v-if="isLoading" class="woo_easy_loader" xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" viewBox="0 0 256 256"><path d="M136,32V64a8,8,0,0,1-16,0V32a8,8,0,0,1,16,0Zm88,88H192a8,8,0,0,0,0,16h32a8,8,0,0,0,0-16Zm-45.09,47.6a8,8,0,0,0-11.31,11.31l22.62,22.63a8,8,0,0,0,11.32-11.32ZM128,184a8,8,0,0,0-8,8v32a8,8,0,0,0,16,0V192A8,8,0,0,0,128,184ZM77.09,167.6,54.46,190.22a8,8,0,0,0,11.32,11.32L88.4,178.91A8,8,0,0,0,77.09,167.6ZM72,128a8,8,0,0,0-8-8H32a8,8,0,0,0,0,16H64A8,8,0,0,0,72,128ZM65.78,54.46A8,8,0,0,0,54.46,65.78L77.09,88.4A8,8,0,0,0,88.4,77.09Z"></path></svg>
        <div class="woo_easy_popup_content">
            <button 
                v-if="!seeMore"
                class="woo_easy_close-popup"
                @click="toggleModal = false"
            >×</button>
            <div class="woo_easy_header">
                <h3>Duplicate Order History</h3>
                <button
                    v-if="seeMore"
                    style="
                        border: none;
                        background: transparent;
                        font-size: 16px;
                        display: flex;
                        align-items: center;
                        cursor: pointer;
                    "
                    @click="seeMore = false"
                >
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 256 256"><path d="M165.66,202.34a8,8,0,0,1-11.32,11.32l-80-80a8,8,0,0,1,0-11.32l80-80a8,8,0,0,1,11.32,11.32L91.31,128Z"></path></svg>
                    Back
                </button>
            </div>

            <div id="woo_easy_life_order_details">
                <div class="woo_easy_customer_details">
                    <h3 class="title">Customer Details</h3>
                    <div v-if="orderInfo?.length" class="woo_easy_customer_info">
                        <h4>
                            <span style="font-weight: bold;">
                                Name:
                            </span> 
                            {{ orderInfo[0]?.billing_address?.first_name }}
                            {{ orderInfo[0]?.billing_address?.last_name }}
                        </h4>
                        <h4>
                            <span style="font-weight: bold;">
                                Phone:
                            </span> 
                            {{ orderInfo[0]?.billing_address?.phone }}
                        </h4>
                        <h4>
                            <span style="font-weight: bold;">
                                Email:
                            </span> 
                            {{ orderInfo[0]?.billing_address?.email }}
                        </h4>
                        <h4>
                            <span style="font-weight: bold;">
                                Address:
                            </span> 
                            {{ orderInfo[0]?.billing_address?.address_1 }} 
                            {{ orderInfo[0]?.billing_address?.address_2 }}
                        </h4>
                    </div>

                    <div class="woo_easy_order_list">
                        <table 
                            v-if="!seeMore"
                            class="wp-list-table widefat fixed striped table-view-list orders wc-orders-list-table wc-orders-list-table-shop_order"
                        >
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Date</th>
                                    <th>Payment Method</th>
                                    <th>Status</th>
                                    <th style="width: 60px;">Total</th>
                                    <th style="width: 30px;">Action</th>
                                </tr>
                            </thead>
                            <tbody v-if="orderInfo?.length">
                                <tr 
                                    v-for="item in orderInfo"
                                    :key="item.id"
                                >
                                    <td style="white-space: nowrap;">#{{ item.id }} {{ item.customer_name }}</td>
                                    <td>{{ item.date_created }}</td>
                                    <td>{{ item.payment_method_title }}</td>
                                    <td>{{ item.status == 'processing' ? 'New order' : item.status }}</td>
                                    <td v-html="item.product_price"></td>
                                    <td>
                                        <button 
                                            style="
                                                background: #03A9F4;
                                                border: none;
                                                padding: 6px 12px;
                                                border-radius: 2px;
                                                color: white;
                                            "
                                            @click="getSelectedProduct(item.id)"
                                            title="View order details"
                                        >
                                            View
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table 
                            class="wp-list-table widefat fixed striped table-view-list orders wc-orders-list-table wc-orders-list-table-shop_order"
                            v-else
                        >
                            <thead>
                                <tr>
                                    <th style="width: 30px;">Image</th>
                                    <th>Product Name</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total Price</th>
                                    
                                </tr>
                            </thead>
                            <tbody v-if="selectedOrder">
                                <tr 
                                    v-for="item in selectedOrder?.product_info || []"
                                    :key="item.id"
                                >
                                    <td>
                                        <img 
                                            :src="item.product_image" 
                                            alt="product image"
                                            style="
                                                width: 60px;
                                                height: 60px;
                                                object-fit: cover;
                                                object-position: center;
                                            "
                                        />
                                    </td>
                                    <td>{{ item.product_name }}</td>
                                    <td>{{ item.product_price }}</td>
                                    <td>{{ item.product_quantity }}</td>
                                    <td>{{ item.product_total }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
  const { createApp, ref } = Vue

  createApp({
    setup() {
        const toggleModal = ref(false)
        const customerInfo = ref()
        const orderInfo = ref([])
        const isLoading = ref(false)
        const seeMore   = ref(false)
        const selectedOrder = ref({});

        const getOrderList = async (payload) => {
            const { data } = await axios.get(`${location.origin}/wordpress/wp-json/wooeasylife/v1/orders`, {
                params: payload
            })
            return data
        }

        const getSelectedProduct = (order_id) => {
            seeMore.value = true
            selectedOrder.value = orderInfo.value.find(item => item.id == order_id)?.product_info
        }

        setTimeout(() => {
            window.onclick = async function (e) {
                const dataSet = e.target.dataset
                if(dataSet.billing_phone && dataSet.order_status)
                {
                    e.preventDefault();
                    toggleModal.value = true
                    try {
                        orderInfo.value = []
                        isLoading.value = true
                        const { data } = await getOrderList({
                            status: dataSet.order_status,
                            billing_phone: dataSet.billing_phone
                        })
                        orderInfo.value = data
                    } finally {
                        isLoading.value = false
                    }
                }
            }
        })

        return {
            toggleModal,
            getOrderList,
            isLoading,
            orderInfo,
            getSelectedProduct,
            selectedOrder,
            seeMore
        }
    }
  }).mount('#woo_easy_app')
</script>