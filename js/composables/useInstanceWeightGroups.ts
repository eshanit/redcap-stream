import { computed } from 'vue';
import { type IProjectData } from '@/types/IProject';
import useGroupByInstance from './useGroupByInstance';

export default function useInstanceWeightGroups(data: IProjectData[]) {

    const groupedByInstance = useGroupByInstance(data);

    const summary = computed(() => {
        const summaries: { [key: string]: { [weightGroup: string]: number } } = {};

        if (!groupedByInstance) {
            return summaries; // Return empty object if groupedByInstance is null or undefined
        }

        for (const instanceKey in groupedByInstance) {
            if (Object.prototype.hasOwnProperty.call(groupedByInstance, instanceKey)) {
                const items = groupedByInstance[instanceKey];

                if (!items) continue; // Skip if items is null or undefined

                const weightGroups: { [weightGroup: string]: number } = {
                    '0-10': 0,
                    '11-20': 0,
                    '21-30': 0,
                    '31-40': 0,
                    '41-50': 0,
                    '51-60': 0,
                    '61-70': 0,
                    '70+': 0
                };

                items.forEach(item => {
                    const weight = parseInt(item.value, 10); // Convert weight to number

                    if (isNaN(weight)) return; // Skip if weight is NaN

                    if (weight >= 0 && weight <= 10) {
                        weightGroups['0-10']++;
                    } else if (weight >= 11 && weight <= 20) {
                        weightGroups['11-20']++;
                    } else if (weight >= 21 && weight <= 30) {
                        weightGroups['21-30']++;
                    } else if (weight >= 31 && weight <= 40) {
                        weightGroups['31-40']++;
                    } else if (weight >= 41 && weight <= 50) {
                        weightGroups['41-50']++;
                    } else if (weight >= 51 && weight <= 60) {
                        weightGroups['51-60']++;
                    } else if (weight >= 61 && weight <= 70) {
                        weightGroups['61-70']++;
                    } else if (weight > 70) {
                        weightGroups['70+']++;
                    }
                });

                let key: string | number;
                if (instanceKey === 'null') { // Use strict equality
                    key = 1;
                } else {
                    key = instanceKey;
                }

                summaries[key] = weightGroups;
            }

        }
        return summaries;


    });

    return summary;
}