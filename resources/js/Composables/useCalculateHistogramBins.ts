import { ref, computed } from 'vue';

const useCalculateHistogramBins = (arr: number[]) => 
    {
        const data = ref(arr);

        const numberOfBins = computed(() => calculateNumberOfBins(data.value));
        const binWidth = computed(() => calculateBinWidth(data.value, numberOfBins.value));
        const bins = computed(() => createBins(data.value, numberOfBins.value, binWidth.value));

        const calculateNumberOfBins = (data: any[]) => {
            return Math.ceil(Math.sqrt(data.length));
        };

        const calculateBinWidth = (data: Array<number>, numberOfBins: number) => {
            const min = Math.min(...data);
            const max = Math.max(...data);
            return (max - min) / numberOfBins;
        };

        const createBins = (data: Array<number>, numberOfBins: number, binWidth: number) => {
            const min = Math.min(...data);
            const bins = Array.from({ length: numberOfBins }, (_, i) => ({
                min: min + i * binWidth,
                max: min + (i + 1) * binWidth,
                count: 0,
            }));

            data.forEach(value => {
                const binIndex = Math.min(
                    Math.floor((value - min) / binWidth),
                    numberOfBins - 1
                );
                bins[binIndex].count += 1;
            });

            return bins;
        }

        return bins
    }

    export  default useCalculateHistogramBins