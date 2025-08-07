import { computed } from 'vue';
import { type IProjectData } from '@/types/IProject';
import useGroupByInstance from './useGroupByInstance';

export default function useInstanceAgeGroups(data: IProjectData[]) {

    const groupedByInstance = useGroupByInstance(data);

    const summary = computed(() => {
        const summaries: { [key: string]: { [ageGroup: string]: number } } = {};

        if (!groupedByInstance) {
            return summaries; // Return empty object if groupedByInstance is null or undefined
        }

        for (const instanceKey in groupedByInstance) {
            if (Object.prototype.hasOwnProperty.call(groupedByInstance, instanceKey)) {
                const items = groupedByInstance[instanceKey];

                if (!items) continue; // Skip if items is null or undefined

                const ageGroups: { [ageGroup: string]: number } = {
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
                    const age = parseInt(item.value, 10); // Convert age to number

                    if (isNaN(age)) return; // Skip if age is NaN

                    if (age >= 0 && age <= 10) {
                        ageGroups['0-10']++;
                    } else if (age >= 11 && age <= 20) {
                        ageGroups['11-20']++;
                    } else if (age >= 21 && age <= 30) {
                        ageGroups['21-30']++;
                    } else if (age >= 31 && age <= 40) {
                        ageGroups['31-40']++;
                    } else if (age >= 41 && age <= 50) {
                        ageGroups['41-50']++;
                    } else if (age >= 51 && age <= 60) {
                        ageGroups['51-60']++;
                    } else if (age >= 61 && age <= 70) {
                        ageGroups['61-70']++;
                    } else if (age > 70) {
                        ageGroups['70+']++;
                    }
                });

                let key: string | number;
                if (instanceKey === 'null') { // Use strict equality
                    key = 1;
                } else {
                    key = instanceKey;
                }

                summaries[key] = ageGroups;
            }

        }
        return summaries;


    });

    return summary;
}