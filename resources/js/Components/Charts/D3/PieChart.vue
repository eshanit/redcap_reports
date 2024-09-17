<script setup>
import * as d3 from 'd3';
import { onMounted, ref, watch } from 'vue';
import TrayArrowDownIcon from 'vue-material-design-icons/TrayArrowDown.vue';
import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';

// Define the prop
const props = defineProps({
    dataObject: {
        type: Array,
        required: true
    },
    chartTitle: { 
    type: String,
     required: true
     }
});


const pieChartRef = ref(null);
const isDropdownOpen = ref(false);

// Toggle dropdown visibility
const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value;
};

const createPieChart = (data) => {
    const margin = { top: 50, right: 30, bottom: 30, left: 40 },
        width = 600 - margin.left - margin.right,
        height = 350 - margin.top - margin.bottom,
        radius = Math.min(width, height) / 2;

    // Clear any existing SVG elements
    d3.select(pieChartRef.value).selectAll('*').remove();

    const svg = d3.select(pieChartRef.value)
        .append('svg')
        .attr('width', width + margin.left + margin.right)
        .attr('height', height + margin.top + margin.bottom)
        .append('g')
        .attr('transform', `translate(${width / 2 + margin.left}, ${height / 2 + margin.top})`);

    const color = d3.scaleOrdinal(d3.schemeSet1);
    //const color = d3.scaleSequential(d3.interpolateRainbow);

    const pie = d3.pie()
        .value(d => d.value)
        .sort(null);

    const arc = d3.arc()
        .innerRadius(0)
        .outerRadius(radius);

    // Create gradients for each slice
    data.forEach((d, i) => {
        const gradient = svg.append("defs")
            .append("radialGradient")
            .attr("id", `gradient-${i}`)
            .attr("cx", "50%")
            .attr("cy", "50%")
            .attr("r", "50%")
            .attr("fx", "50%")
            .attr("fy", "50%");

        gradient.append("stop")
            .attr("offset", "0%")
            .attr("stop-color", color(i));

        gradient.append("stop")
            .attr("offset", "100%")
            .attr("stop-color", d3.rgb(color(i)).brighter(0.3).toString());
    });

    const slices = svg.selectAll('path')
        .data(pie(data))
        .enter().append('path')
        .attr('d', arc)
        .attr('fill', (d, i) => `url(#gradient-${i})`)
        .attr('stroke', '#fff')
        .attr('stroke-width', 1)
        .attr('class', 'transition-transform duration-200')
        .on('mouseover', function (event, d) {
            const index = d.index;
            d3.select(this)
                .transition()
                .duration(200)
                .attr('transform', 'scale(1.1)')
                .attr('opacity', 0.7);

            // Change line color on hover
            // Change line color and opacity on hover
            svg.selectAll('line')
                .filter((lineData, lineIndex) => lineIndex === index)
                .attr('stroke', color(index)) // Match line color to slice color
                .attr('opacity', 0.7); // Match line opacity to slice opacity
        })
        .on('mouseout', function (event, d) {
            const index = d.index;
            d3.select(this)
                .transition()
                .duration(200)
                .attr('transform', 'scale(1)')
                .attr('opacity', 1);

            // Reset line color on mouse out
            svg.selectAll('line')
                .filter((lineData, lineIndex) => lineIndex === index)
                .attr('stroke', color(index)) // Keep the line color matching the slice
                .attr('opacity', 1); // Reset line opacity
        });

    const labelOffset = 50; // Offset for labels outside the pie

    // Add lines pointing to the slices
    svg.selectAll('line')
        .data(pie(data))
        .enter()
        .append('line')
        .attr('x1', d => {
            const [x, y] = arc.centroid(d); // Start line at the center of the slice
            return x;
        })
        .attr('x1', d => arc.centroid(d)[0])
        .attr('y1', d => arc.centroid(d)[1])
        .attr('x2', d => {
            const outerArc = d3.arc().innerRadius(radius).outerRadius(radius + labelOffset);
            const centroid = outerArc.centroid(d);
            return centroid[0]; // End line at the circumference
        })
        .attr('y2', d => {
            const outerArc = d3.arc().innerRadius(radius).outerRadius(radius + labelOffset);
            const centroid = outerArc.centroid(d);
            return centroid[1];
        })
        .attr('stroke', (d, i) => color(i))// Line color
        .attr('stroke-width', 1)
        .attr('opacity', 1);

    // Add labels outside the pie
    svg.selectAll('text')
        .data(pie(data))
        .enter().append('text')
        .attr('transform', d => {
            const centroid = arc.centroid(d);
            const x = centroid[0] * 3; // Adjust the position
            const y = centroid[1] * 2.5;
            return `translate(${x}, ${y})`;
        })
        .attr('dy', '.35em')
        .attr('text-anchor', 'middle')
        .text(d => {
            const percentage = ((d.data.value / d3.sum(data, d => d.value)) * 100).toFixed(1);
            return `${d.data.name} ${percentage} %`;
        })
        .style('font-size', '12px')
        .style('fill', '#000'); // Text color
};

const downloadPNG = () => {
    html2canvas(pieChartRef.value).then((canvas) => {
        const link = document.createElement('a');
        link.href = canvas.toDataURL('image/png');
        link.download = 'pie-chart.png';
        link.click();
    });
};

const downloadJPEG = () => {
    html2canvas(pieChartRef.value).then((canvas) => {
        const link = document.createElement('a');
        link.href = canvas.toDataURL('image/jpeg');
        link.download = 'pie-chart.jpg';
        link.click();
    });
};

const downloadPDF = () => {
    html2canvas(pieChartRef.value).then((canvas) => {
        const pdf = new jsPDF();
        const imgData = canvas.toDataURL('image/png');
        const imgWidth = 190; // Width for PDF
        const pageHeight = pdf.internal.pageSize.height;
        const imgHeight = (canvas.height * imgWidth) / canvas.width;
        let heightLeft = imgHeight;

        let position = 0;

        pdf.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight);
        heightLeft -= pageHeight;

        while (heightLeft >= 0) {
            position = heightLeft - imgHeight;
            pdf.addPage();
            pdf.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;
        }

        pdf.save('pie-chart.pdf');
    });
};

onMounted(() => {
    if (Array.isArray(props.dataObject) && props.dataObject.length > 0) {
        createPieChart(props.dataObject);
    }
});

watch(() => props.dataObject, (newData) => {
    if (pieChartRef.value) {
        d3.select(pieChartRef.value).selectAll('*').remove();
        createPieChart(newData);
    }
});
</script>
<template>
    <div class="relative flex flex-col items-center">
        <h1 class="mb-4 text-xl font-bold">{{ chartTitle }}</h1>
        <div ref="pieChartRef" class="relative"></div>

        <div class="absolute top-4 right-4">
            <div class="relative inline-block text-left">
                <div>
                    <button @click="toggleDropdown" class="flex items-center p-2 bg-gray-200 rounded-md shadow-md">
                        <!-- <span class="mr-2">Download</span> -->
                        <TrayArrowDownIcon />
                    </button>
                </div>

                <div v-if="isDropdownOpen"
                    class="absolute right-0 w-48 mt-2 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5">
                    <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                        <button @click="downloadPNG"
                            class="block w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-100">Download as
                            PNG</button>
                        <button @click="downloadJPEG"
                            class="block w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-100">Download as
                            JPG</button>
                        <button @click="downloadPDF"
                            class="block w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-100">Download as
                            PDF</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
path {
    transition: transform 0.2s, opacity 0.2s;
}
</style>