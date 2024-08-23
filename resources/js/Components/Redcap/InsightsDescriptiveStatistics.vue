<script setup>
import { ref, onMounted } from 'vue';
import { mean, median, mode, max, min } from 'simple-statistics';


const props = defineProps({
    dataArray: {
        type: Array,
        default: [],
    }
});
const numbersFloat = ref([]);
const numbersInt = ref([]);
const meanValue = ref(0);
const medianValue = ref(0);
const modeValue = ref(0);
const maxValue = ref(0);
const minValue = ref(0);

props.dataArray.forEach((value)=> {
    numbersFloat.value.push(parseFloat(value));
    numbersInt.value.push(parseInt(value));
})

const calculateStatistics = () => {
    meanValue.value = mean(numbersFloat.value).toFixed(4);
    medianValue.value = median(numbersFloat.value);
    modeValue.value = mode(numbersFloat.value);
    maxValue.value = max(numbersFloat.value);
    minValue.value = min(numbersFloat.value);
};

onMounted(() => {
    calculateStatistics();
});
</script>

<template>
    <div class="grid grid-cols-2 gap-5">
        <div class="font-semibold ">
            Mean
        </div>
        <div>
            {{ meanValue }}
        </div>
        <div class="font-semibold ">
            Median
        </div>
        <div>
            {{ medianValue }}
        </div>
        <div class="font-semibold ">
            Mode
        </div>
        <div>
            {{ modeValue }}
        </div>
        <div class="font-semibold ">
            Min
        </div>
        <div>
            {{ minValue }}
        </div>
        <div class="font-semibold ">
            max
        </div>
        <div>
            {{ maxValue }}
        </div>
    </div>
</template>