<script setup>
import * as d3 from 'd3';
import { onMounted, ref, watch } from 'vue';

// Define the prop
const props = defineProps({
    dataArray: {
        type: Array,
        required: true
    }
});

const densityChartRef = ref(null);

const createDensityChart = (data) => {
    const margin = { top: 10, right: 30, bottom: 30, left: 40 },
        width = 860 - margin.left - margin.right,
        height = 600 - margin.top - margin.bottom;

    // Clear any existing SVG elements
    d3.select(densityChartRef.value).selectAll('*').remove();

    const svg = d3.select(densityChartRef.value)
        .append('svg')
        .attr('width', width + margin.left + margin.right)
        .attr('height', height + margin.top + margin.bottom)
        .append('g')
        .attr('transform', `translate(${margin.left},${margin.top})`);

    const x = d3.scaleLinear()
        .domain([0, d3.max(data)])
        .range([0, width]);

    svg.append('g')
        .attr('transform', `translate(0,${height})`)
        .call(d3.axisBottom(x));

    const kde = kernelDensityEstimator(kernelEpanechnikov(7), x.ticks(40));
    const density = kde(data);

    const y = d3.scaleLinear()
        .domain([0, d3.max(density, d => d[1])])
        .range([height, 0]);

    svg.append('g')
        .call(d3.axisLeft(y));

    const line = d3.line()
        .curve(d3.curveBasis)
        .x(d => x(d[0]))
        .y(d => y(d[1]));

    svg.append('path')
        .datum(density)
        .attr('fill', 'none')
        .attr('stroke', '#69b3a2')
        .attr('stroke-width', 1.5)
        .attr('stroke-linejoin', 'round')
        .attr('d', line);
};

const kernelDensityEstimator = (kernel, X) => {
    return V => X.map(x => [x, d3.mean(V, v => kernel(x - v))]);
};

const kernelEpanechnikov = k => {
    return v => Math.abs(v /= k) <= 1 ? 0.75 * (1 - v * v) / k : 0;
};

onMounted(() => {
    if (props.dataArray.length > 0) {
        createDensityChart(props.dataArray);
    }
});

watch(() => props.dataArray, (newData) => {
    if (densityChartRef.value) {
        d3.select(densityChartRef.value).selectAll('*').remove();
        createDensityChart(newData);
    }

});
</script>

<template>
    <div>
        <h1>Density Chart</h1>
        <div ref="densityChartRef"></div>
    </div>
</template>


<style>
/* Add any additional styles if needed */
</style>