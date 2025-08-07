import { computed } from 'vue';
import { type IProjectData } from '@/types/IProject';
import useGroupByInstance from './useGroupByInstance';

export default function useInstanceBMIGroups(data: IProjectData[]) {

    const groupedByInstance = useGroupByInstance(data);

    const summary = computed(() => {
        const summaries: { [key: string]: { [BMIGroup: string]: number } } = {};

        if (!groupedByInstance) {
            return summaries; // Return empty object if groupedByInstance is null or undefined
        }

        for (const instanceKey in groupedByInstance) {
            if (Object.prototype.hasOwnProperty.call(groupedByInstance, instanceKey)) {
                const items = groupedByInstance[instanceKey];

                if (!items) continue; // Skip if items is null or undefined

                const BMIGroups: { [BMIGroup: string]: number } = {
                    'Underweight': 0,
                    'Healthy Weight': 0,
                    'Overweight': 0,
                    'Obese': 0
                };

                items.forEach(item => {
                    const BMI = parseInt(item.value, 10); // Convert BMI to number

                    if (isNaN(BMI)) return; // Skip if BMI is NaN

                    if (BMI < 18.5) {
                        BMIGroups['Underweight']++;
                    } else if (BMI >= 18.5 && BMI < 25) {
                        BMIGroups['Healthy Weight']++;
                    } else if (BMI >= 25 && BMI < 30) {
                        BMIGroups['Overweight']++;
                    } else if (BMI >= 30) {
                        BMIGroups['Obese']++;
                    }
                });

                let key: string | number;
                if (instanceKey === 'null') { // Use strict equality
                    key = 1;
                } else {
                    key = instanceKey;
                }

                summaries[key] = BMIGroups;
            }

        }
        return summaries;


    });

    return summary;
}