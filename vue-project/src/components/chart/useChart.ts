import { ref, watch } from "vue";

export type ChartData = {
    type: 'line' | 'bar' | 'pie' | 'donut' | 'radar' | 'area' | 'scatter'
    series: Array<{ name: string; data: number[] }>
    categories?: string[] // Optional for X-axis labels
    options?: Record<string, any> // Optional for additional configuration
}

export const useChart = () => 
{
    const chartRef = ref()
    const defaultChartData = ref({
        type: 'area',
        options: {
            chart: {
                toolbar: {
                    show: false, // Hides the toolbar
                },
            },
            colors: ['#0ca5e9', '#f97315', '#ABA62B', '#8D8D8D', '#2BAD7E', '#3062BB', '#C51B1B', '#2B80AD'],
            stroke: {
                curve: 'smooth',
                width: 2
            },
            dataLabels: {
                enabled: false
            },
            xaxis: {
                // categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 0,
                    inverseColors: true,
                    opacityFrom: 1,
                    opacityTo: 1,
                    stops: [0, 0, 100]
                },
            },
            grid: {
                show: true,
            },
            tooltip: {
                // x: {
                //     show: false,
                // }
            },
            legend: {
                show: true,
                horizontalAlign: 'left',
                fontSize: '12px',
                position: 'top',
                height: 50,
                labels: {
                    colors: '#000000'
                },
            }
        },
        series: [
            // {
            //     name: 'Sales',
            //     data: [30, 40, 35, 50, 49, 60, 70],
            // },
            // {
            //     name: 'Sales',
            //     data: [10, 70, 35, 10, 39, 60, 20],
            // },
        ],
    });
    
    return {
        chartRef,
        defaultChartData
    }
}