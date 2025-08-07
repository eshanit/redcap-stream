import { ref } from 'vue';
import { type IProjectData } from '@/types/IProject';
import useTransformSelect from './useTransformSelect';

// Define the type for the labels object
interface LabelsCount {
    [key: string]: number;
}

export default function useCountMultiSelect(dataPoints: IProjectData[]) {
    if (dataPoints.length > 0 && dataPoints[0].element_enum) {
        const countsPerValue = ref<{ [key: string]: number }>({});
        const countsPerCombination = ref<{ [key: string]: number }>({});

        const labelsArray = useTransformSelect(dataPoints[0].element_enum);

        // Count respondents per value
        dataPoints.forEach(item => {
            if (item.value) {
                const key = labelsArray.find(l => l.value.trim() == item.value.trim())?.name;

                countsPerValue.value[key || ''] = (countsPerValue.value[key || ''] || 0) + 1;
            }
        });

        // Count combinations of values
        const groupedByRecord = dataPoints.reduce((acc, item) => {
            const key = labelsArray.find(l => l.value.trim() == item.value.trim())?.name;
            if (!acc[item.record]) {
                acc[item.record] = [];
            }
            acc[item.record].push(key || '');
            return acc;
        }, {} as Record<string, string[]>);

        // Count combinations
        for (const values of Object.values(groupedByRecord)) {
            const combinationKey = values.sort().join(','); // Sort the values to create a consistent key
            countsPerCombination.value[combinationKey] = (countsPerCombination.value[combinationKey] || 0) + 1;
        }

        return {
            countsPerValue,
            countsPerCombination,
        };
    }
    return {}; // Return an empty object if no data points are available
}