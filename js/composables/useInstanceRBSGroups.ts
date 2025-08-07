import { computed } from 'vue';
import { type IProjectData } from '@/types/IProject';
import useGroupByInstance from './useGroupByInstance';

export default function useInstanceRBSGroups(data: IProjectData[]) {

    const groupedByInstance = useGroupByInstance(data);

    const summary = computed(() => {
        const summaries: { [key: string]: { [RBSGroup: string]: number } } = {};

        if (!groupedByInstance) {
            return summaries; // Return empty object if groupedByInstance is null or undefined
        }

        for (const instanceKey in groupedByInstance) {
            if (Object.prototype.hasOwnProperty.call(groupedByInstance, instanceKey)) {
                const items = groupedByInstance[instanceKey];

                if (!items) continue; // Skip if items is null or undefined

                const RBSGroups: { [RBSGroup: string]: number } = {
                    'Normal': 0,
                    'Prediabetic': 0,
                    'Diabetic': 0
                };

                items.forEach(item => {
                    const RBS = parseInt(item.value, 10); // Convert RBS to number

                    if (isNaN(RBS)) return; // Skip if RBS is NaN

                    if (RBS < 6.1) {
                        RBSGroups['Normal']++;
                    } else if (RBS >= 6.1 && RBS < 6.9) {
                        RBSGroups['Prediabetic']++;
                    } else if (RBS >= 6.9) {
                        RBSGroups['Diabetic']++;
                    }
                });

                let key: string | number;
                if (instanceKey === 'null') { // Use strict equality
                    key = 1;
                } else {
                    key = instanceKey;
                }

                summaries[key] = RBSGroups;
            }

        }
        return summaries;


    });

    return summary;
}