import { computed } from 'vue';
import { type IProjectData } from '@/types/IProject';
import useGroupByInstance from './useGroupByInstance';

export default function useInstanceFBSGroups(data: IProjectData[]) {

    const groupedByInstance = useGroupByInstance(data);

    const summary = computed(() => {
        const summaries: { [key: string]: { [FBSGroup: string]: number } } = {};

        if (!groupedByInstance) {
            return summaries; // Return empty object if groupedByInstance is null or undefined
        }

        for (const instanceKey in groupedByInstance) {
            if (Object.prototype.hasOwnProperty.call(groupedByInstance, instanceKey)) {
                const items = groupedByInstance[instanceKey];

                if (!items) continue; // Skip if items is null or undefined

                const FBSGroups: { [FBSGroup: string]: number } = {
                    'Normal': 0,
                    'Prediabetic': 0,
                    'Diabetic': 0
                };

                items.forEach(item => {
                    const FBS = parseInt(item.value, 10); // Convert FBS to number

                    if (isNaN(FBS)) return; // Skip if FBS is NaN

                    if (FBS < 5.7) {
                        FBSGroups['Normal']++;
                    } else if (FBS >= 5.7 && FBS < 6.5) {
                        FBSGroups['Prediabetic']++;
                    } else if (FBS >= 6.5) {
                        FBSGroups['Diabetic']++;
                    }
                });

                let key: string | number;
                if (instanceKey === 'null') { // Use strict equality
                    key = 1;
                } else {
                    key = instanceKey;
                }

                summaries[key] = FBSGroups;
            }

        }
        return summaries;


    });

    return summary;
}