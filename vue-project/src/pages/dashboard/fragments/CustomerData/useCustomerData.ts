import { getCustomerData } from "@/api/dashboard"
import { computed, ref } from "vue"

export const useCustomerData = () => {
    const customerData = ref()
    const isLoading = ref(false)

    const loadCustomerData = async (
        date: {
            start_date: string
            end_date: string
        }, 
        status: string
    ) => {
        try {
            isLoading.value = true
            const { data } = await getCustomerData({...date, status})
            customerData.value = data
        } finally {
            isLoading.value = false
        }
    }

    const chartData = computed(() => {
        if(!customerData.value) return
        return {
            type: 'area',
            options: {
                xaxis: {
                    categories: customerData.value?.categories || []
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 0,
                        inverseColors: true,
                        opacityFrom: 1,
                        opacityTo: 0.5,
                        stops: [0, 0, 100]
                    },
                },
                legend: {
                    show: true,
                    position: 'top',
                    horizontalAlign: 'center',
                    labels: {
                        colors: '#333',
                        useSeriesColors: false
                    },
                },
                tooltip: {
                    enabled: true,
                    shared: true,
                    y: {
                        formatter: function (val, opts) {
                            // Dynamically fetch the series name
                            const seriesName = opts.seriesIndex !== undefined ? customerData.value?.series[opts.seriesIndex]?.name : '';
                            return val;
                        }
                    }
                },
            },
            series: [
                ...customerData.value?.series || []
            ]
        }
    })

    return {
        isLoading,
        chartData,
        customerData,
        loadCustomerData,
    }
}