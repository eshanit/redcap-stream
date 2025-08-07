import { computed } from 'vue';
import { type IProjectData } from '@/types/IProject';
import useGroupByInstance from './useGroupByInstance';

export default function useInstanceHBA1cGroups(data: IProjectData[]) {

    const groupedByInstance = useGroupByInstance(data);

    const summary = computed(() => {
        const summaries: { [key: string]: { [HBA1cGroup: string]: number } } = {};

        if (!groupedByInstance) {
            return summaries; // Return empty object if groupedByInstance is null or undefined
        }

        for (const instanceKey in groupedByInstance) {
            if (Object.prototype.hasOwnProperty.call(groupedByInstance, instanceKey)) {
                const items = groupedByInstance[instanceKey];

                if (!items) continue; // Skip if items is null or undefined

                const HBA1cGroups: { [HBA1cGroup: string]: number } = {
                    'Normal': 0,
                    'Prediabetic': 0,
                    'Diabetic': 0
                };

                items.forEach(item => {
                    const HBA1c = parseInt(item.value, 10); // Convert HBA1c to number

                    if (isNaN(HBA1c)) return; // Skip if HBA1c is NaN

                    if (HBA1c < 5.7) {
                        HBA1cGroups['Normal']++;
                    } else if (HBA1c >= 5.7 && HBA1c < 6.5) {
                        HBA1cGroups['Prediabetic']++;
                    } else if (HBA1c >= 6.5) {
                        HBA1cGroups['Diabetic']++;
                    }
                });

                let key: string | number;
                if (instanceKey === 'null') { // Use strict equality
                    key = 1;
                } else {
                    key = instanceKey;
                }

                summaries[key] = HBA1cGroups;
            }

        }
        return summaries;


    });

    return summary;
}