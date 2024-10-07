<script setup lang='ts'>
import { ref, watch, onMounted } from 'vue';
import { formatDistance, parseISO } from 'date-fns';

const props = defineProps<{
    data: object;
    recordId: string;
}>();

const chartSeries = ref([]);
const chartData = ref([]); // Store data for the table
const chartOptions = ref({
  chart: {
    id: 'hb1ac-chart',
  },
  xaxis: {
    type: 'datetime',
  },
  title: {
    // text: `HbA1c Levels for Patient ${props.recordId}`,
    align: 'left',
  },
  yaxis: {
    title: {
      text: 'HbA1c Level (%)',
    },
  },
  markers: {
    size: 5,
  },
 // colors: ['#85DD80']
});

// Function to update the chart series and table data
const updateChartData = () => {
  const visits = props.data[props.recordId];

  if (visits) {
    const visitData = visits.map((visit, index) => {
      const visitDate = new Date(visit.visit_date); // Create a Date object
      const prevVisitDate = index > 0 ? new Date(visits[index - 1].visit_date) : null;
      const timeDiff = prevVisitDate ? formatDistance(visitDate, prevVisitDate, { addSuffix: true }) : '-';

      return {
        x: visitDate.getTime(),
        y: parseFloat(visit.hb1ac),
        visit_date: visit.visit_date,
        hb1ac: visit.hb1ac,
        time_diff: timeDiff, // Add time difference
      };
    });

    chartSeries.value = [{
      name: props.recordId,
      data: visitData,
    }];
    chartData.value = visitData; // Store visitData for the table
  } else {
    chartSeries.value = [];
    chartData.value = [];
  }
};

// Initial data load
onMounted(() => {
  updateChartData();
});

// Watch for changes in recordId to update chart
watch(() => props.recordId, (newId) => {
  updateChartData();
});
</script>

<template>
    <div class="container p-4 mx-auto">
        <h1 class="mb-4 text-xl font-bold">HbA1c Levels for Patient {{ recordId }}</h1>
        <apexchart type="line" :options="chartOptions" :series="chartSeries" v-if="chartSeries.length > 0" />
        <p v-else>No data available for this patient.</p>

        <h2 class="mt-8 text-lg font-semibold">Data Table</h2>
        <table class="min-w-full mt-4 border border-gray-300">
            <thead>
                <tr>
                    <th class="px-4 py-2 border border-gray-300">Visit Date</th>
                    <th class="px-4 py-2 border border-gray-300">HbA1c Level (%)</th>
                    <th class="px-4 py-2 border border-gray-300">Time Difference</th> <!-- New column -->
                </tr>
            </thead>
            <tbody>
                <tr v-for="visit in chartData" :key="visit.visit_date">
                    <td class="px-4 py-2 border border-gray-300">{{ visit.visit_date }}</td>
                    <td class="px-4 py-2 border border-gray-300">{{ visit.hb1ac }}</td>
                    <td class="px-4 py-2 border border-gray-300">{{ visit.time_diff }}</td> <!-- Display time difference -->
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
.container {
    max-width: 800px;
    margin: 0 auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    text-align: left;
}

th {
    background-color: #f9f9f9;
}
</style>