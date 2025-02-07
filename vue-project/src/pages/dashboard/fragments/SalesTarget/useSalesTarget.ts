import { createOrUpdateWPOption, getWPOption } from "@/api";
import { computed, onMounted, ref } from "vue";
import { add, format, parseISO } from "date-fns";
import { getSalesProgressForTarget } from "@/api/dashboard";
import { merge } from "lodash";

export const useSalesTarget = () => {
  const alertMessage = ref({
    title: "",
    type: "info",
  });
  const defaultData: {
    monthly_target_amount: number;
    daily_target_amount: number;
    start_date: Date | string;
    end_date: Date | string;
  } = {
    monthly_target_amount: 0,
    daily_target_amount: 0,
    start_date: new Date().toISOString().split("T")[0], // Default to today's date in 'yyyy-MM-dd' format
    end_date: "",
  };

  const isLoading = ref(false);
  const chartData = ref({
    today_sales: 0,
    daily: {},
    monthly: {},
    dateWise: {},
  });
  const salesTargetData = ref({
    option_name: "sales_target",
    data: {
      ...defaultData,
    },
  });

  const dailyTargetAmount = computed(() => {
    return (salesTargetData.value?.data?.monthly_target_amount || 0) / 30;
  });

  const endDate = computed(() => {
    let startDate = salesTargetData.value.data?.start_date;

    if (typeof startDate === "string" && startDate) {
      startDate = parseISO(startDate); // Convert ISO string to Date object
    }

    if (!startDate || isNaN(new Date(startDate).getTime())) {
      console.error(
        "Invalid start_date value:",
        salesTargetData.value?.data?.start_date
      );
      return ""; // Return an empty string or handle the error appropriately
    }

    const date = add(new Date(startDate), { days: 30 }); // Add 30 days
    return format(date, "yyyy-MM-dd"); // Format as 'yyyy-MM-dd'
  });

  const loadSalesTargetData = async () => {
    try {
      isLoading.value = true;
      const { data } = await getWPOption({
        option_name: "sales_target",
      }); // Replace with your actual API call

      if(!data) return

      salesTargetData.value = {
        ...salesTargetData.value,
        data: data,
      }
      
      _prepareChartData(data);
    } finally {
      isLoading.value = false;
    }
  };

  const saveSalesTarget = async (btn) => {
    try {
        btn.isLoading = true;
        isLoading.value = true;
        salesTargetData.value.data.daily_target_amount =
        dailyTargetAmount.value || 0;
        salesTargetData.value.data.end_date = endDate.value;
      
        await createOrUpdateWPOption(salesTargetData.value);
        await loadSalesTargetData();

        alertMessage.value = {
            title: "Sales target saved!",
            type: "success",
        };
    } finally {
      btn.isLoading = false;
      isLoading.value = false;
    }
  };

  const _prepareChartData = async (_data) => {
    const chartPreset = {
      type: "pie",
      options: {
        xaxis: {
          categories: ["Achieved", "Target"],
        },
        yaxis: {
          show: false,
        },
        labels: ["Achieved", "Target"],
        colors: ["#02b795", "#eb2128"],
        legend: {
          show: false,
        },
        dataLabels: {
          enabled: true,
        },
        plugins: {
          tooltip: {
            callbacks: {
              label: (tooltipItem: {
                label: string
                raw: string | number
              }) => {
                const label = tooltipItem.label || '';
                const value = tooltipItem.raw;
                return `${label}: ${value} items`; // Customize the tooltip here
              },
            },
          }
        }
      },
      series: [],
    };

    try {
      isLoading.value = true;
      // get date wise data
      const { data } = await getSalesProgressForTarget({
        start_date: _data.start_date,
        end_date: _data.end_date,
      });
      const { categories, series, total_sales, today_sales } = data;

      chartData.value.today_sales = today_sales || 0;
      chartData.value.daily = merge({}, chartPreset, {
        series: [today_sales, _data?.daily_target_amount || 0],
      });

      chartData.value.monthly = merge({}, chartPreset, {
        series: [+total_sales, _data?.monthly_target_amount || 0],
      });

      if (categories && series?.length) {
        chartData.value.dateWise = merge({}, chartPreset, {
          type: "area",
          options: {
            xaxis: {
              categories: categories,
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
            dataLabels: {
                enabled: false
            },
          },
          series: [
            series[0],
            {
              name: "Target Sale Amount",
              data: new Array(series[0]?.data?.length || 0).fill(
                Math.round((_data?.monthly_target_amount / 30))
              ),
            },
          ],
        });
      }
    } finally {
      isLoading.value = false;
    }
  };

  onMounted(() => {
    loadSalesTargetData();
  });

  return {
    endDate,
    isLoading,
    chartData,
    alertMessage,
    salesTargetData,
    dailyTargetAmount,
    saveSalesTarget,
    loadSalesTargetData,
  };
};
