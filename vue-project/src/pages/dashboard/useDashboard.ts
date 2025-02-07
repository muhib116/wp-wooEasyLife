import { onMounted, ref } from "vue"
import { getOrderStatuses } from "@/api"
import { 
    startOfDay, 
    endOfDay, 
    subDays, 
    startOfMonth, 
    endOfMonth, 
    subMonths,
    startOfYear, 
    endOfYear, 
    subYears,
    format, 
} from 'date-fns'


export const useDashboard = () => 
{
    const isLoading = ref(false)
    const orderStatuses = ref([])

    const filterOptions = [
        {
            id: 'today',
            title: 'Today'
        },
        {
            id: 'yesterday',
            title: 'Yesterday'
        },
        {
            id: 'last-7-days',
            title: 'Last 7 days'
        },
        {
            id: 'last-30-days',
            title: 'Last 30 days'
        },
        {
            id: 'last-90-days',
            title: 'Last 90 days'
        },
        {
            id: 'this-month',
            title: 'This month'
        },
        {
            id: 'last-month',
            title: 'Last month'
        },
        {
            id: 'this-year',
            title: 'This year'
        },
        {
            id: 'last-year',
            title: 'Last year'
        },
        {
            id: 'last-2-years',
            title: 'Last 2 years'
        },
        {
            id: 'custom',
            title: 'Custom'
        },
    ]

    const selectedFilterOption = ref<string>('last-7-days')
    const selectedStatusOption = ref<string>('completed');

    const orderStatistics = ref({})
    const customDates = ref({
        start_date: '',
        end_date: ''
    })

    const loadOrderStatuses = async () => {
        try {
            isLoading.value = true
            const { data } = await getOrderStatuses()
            orderStatuses.value = data.map(item => {
                return {
                    title: item.title,
                    slug: item.slug
                }
            })
        } finally {
            isLoading.value = false
        }
    }

    const getDateRangeFormatted = (period: string) => {
        let startDate, endDate
    
        switch (period) {
            case 'today':
                startDate = startOfDay(new Date()) // Start of the current day
                endDate = endOfDay(new Date()) // End of the current day
                break
    
            case 'yesterday':
                const yesterday = subDays(new Date(), 1) // Subtract one day from today
                startDate = startOfDay(yesterday) // Start of yesterday
                endDate = endOfDay(yesterday) // End of yesterday
                break
    
            case 'last-7-days':
                startDate= subDays(new Date(), 7)
                endDate = startOfDay(new Date())
                break
    
            case 'last-30-days':
                startDate= subDays(new Date(), 30)
                endDate = startOfDay(new Date())
                break
    
            case 'last-90-days':
                startDate= subDays(new Date(), 90)
                endDate = startOfDay(new Date())
                break
    
            case 'this-month':
                startDate = startOfMonth(new Date()) // Start of the current month
                endDate = endOfMonth(new Date()) // End of the current month
                break
    
            case 'last-month':
                startDate = startOfMonth(subMonths(new Date(), 1)); // Start of the last month
                endDate = endOfMonth(subMonths(new Date(), 1)); // End of the last month
                break;
    
            case 'this-year':
                startDate = startOfYear(new Date()) // Start of the current year
                endDate = endOfYear(new Date()) // End of the current year
                break
    
            case 'last-year':
                startDate = startOfYear(subYears(new Date(), 1)); // Start of the last year
                endDate = endOfYear(subYears(new Date(), 1)); // End of the last year
                break;
    
            case 'last-2-years':
                startDate = endOfYear(subYears(new Date(), 2)); // End of the last year
                endDate = endOfYear(subYears(new Date(), 1)); // End of the last year
                break;
        }
    
        // Format dates as 'YYYY-MM-DD'
        customDates.value = {
            start_date: startDate ? format(startDate, 'yyyy-MM-dd') : '',
            end_date: endDate ? format(endDate, 'yyyy-MM-dd') : ''
        }
        return {
            startDate: customDates.value.start_date,
            endDate: customDates.value.end_date,
        }
    }

    onMounted(async () => {
        if(!orderStatuses.value?.length){
            await loadOrderStatuses()
        }
    })
    return {
        isLoading,
        selectedStatusOption,
        orderStatuses,
        selectedFilterOption,
        orderStatistics,
        filterOptions,
        customDates,
        getDateRangeFormatted
    }
}