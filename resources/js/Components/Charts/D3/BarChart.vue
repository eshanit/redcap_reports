<script setup>
import * as d3 from 'd3';
import { onMounted, ref, watch } from 'vue';

// Define the prop
const props = defineProps({
    dataObject: {
        type: Object,
        required: true
    }
});

const barChartRef = ref(null);

const createBarChart = (data) => {
  const margin = { top: 10, right: 30, bottom: 30, left: 40 },
        width = 860 - margin.left - margin.right,
        height = 600 - margin.top - margin.bottom;

  // Clear any existing SVG elements
  d3.select(barChartRef.value).selectAll('*').remove();

  const svg = d3.select(barChartRef.value)
    .append('svg')
    .attr('width', width + margin.left + margin.right)
    .attr('height', height + margin.top + margin.bottom)
    .append('g')
    .attr('transform', `translate(${margin.left},${margin.top})`);

 // Convert the data object to an array of objects
 const dataArray = Object.entries(data).map(([key, value]) => ({ key, value }));

// Sort the data
const sortedData = dataArray.sort((a, b) => a.value - b.value);

const x = d3.scaleBand()
    .range([0, width])
    .domain(sortedData.map(d => d.key))
    .padding(0.1);

svg.append('g')
    .attr('transform', `translate(0,${height})`)
    .call(d3.axisBottom(x));

const y = d3.scaleLinear()
    .domain([0, d3.max(sortedData, d => d.value)])
    .range([height, 0]);

svg.append('g')
    .call(d3.axisLeft(y));

svg.selectAll('rect')
    .data(sortedData)
    .enter()
    .append('rect')
    .attr('x', d => x(d.key))
    .attr('y', d => y(d.value))
    .attr('width', x.bandwidth())
    .attr('height', d => height - y(d.value))
    .attr('fill', '#69b3a2');
};

onMounted(() => {
    if (Object.keys(props.dataObject).length > 0) {
        createBarChart(props.dataObject);
    }
});

watch(() => props.dataObject, (newData) => {
    if (barChartRef.value) {
        d3.select(barChartRef.value).selectAll('*').remove();
        createBarChart(newData);
    }
});

</script>
<template>
    <div>
        <h1>Sorted Bar Chart</h1>
        <div ref="barChartRef"></div>
    </div>
</template>

<style>
/* Add any additional styles if needed */
</style>