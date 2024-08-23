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

const histogramRef = ref(null);

const createHistogram = (data) => {
    const margin = { top: 10, right: 30, bottom: 30, left: 40 },
        width = 860 - margin.left - margin.right,
        height = 600 - margin.top - margin.bottom;

    // Clear any existing SVG elements
    d3.select(histogramRef.value).selectAll('*').remove();

    const svg = d3.select(histogramRef.value)
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

    const binGenerator = d3.bin()
        .domain(x.domain())
        .thresholds(x.ticks(10));



    const bins = binGenerator(data);

    const y = d3.scaleLinear()
        .range([height, 0])
        .domain([0, d3.max(bins, d => d.length)]);



    svg.append('g')
        .call(d3.axisLeft(y));

    svg.selectAll('rect')
        .data(bins)
        .enter()
        .append('rect')
        .attr('x', 1)
        .attr('transform', d => `translate(${x(d.x0)},${y(d.length)})`)
        .attr('width', d => x(d.x1) - x(d.x0) - 1)
        .attr('height', d => height - y(d.length))
        .style('fill', d => (d.x0 < 7 ? 'orange' : '#69b3a2'));

    svg.append('line')
        .attr('x1', x(7))
        .attr('x2', x(7))
        .attr('y1', y(0))
        .attr('y2', y(d3.max(bins, d => d.length)))
        .attr('stroke', 'grey')
        .attr('stroke-dasharray', '4');

    svg.append('text')
        .attr('x', x(8))
        .attr('y', y(d3.max(bins, d => d.length) - 1))
        .text('threshold: 7')
        .style('font-size', '15px');

        return svg.node();

};

onMounted(() => {
    if (props.dataArray.length > 0) {
        createHistogram(props.dataArray);
    }
});

watch(() => props.dataArray, (newData) => {
    if (histogramRef.value) {
        d3.select(histogramRef.value).selectAll('*').remove();
        createHistogram(newData);
    }
});
</script>
<template>
    <div>
        <h1>Histogram</h1>
      
        <div ref="histogramRef">
        </div>
    </div>
</template>

<style>
/* Add any additional styles if needed */
</style>