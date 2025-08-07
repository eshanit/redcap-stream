import { computed } from 'vue';
import { type IProjectData } from '@/types/IProject';
import useGroupByInstance from './useGroupByInstance';

export default function useInstanceCreatineGroups(data: IProjectData[]) {

    const groupedByInstance = useGroupByInstance(data);

    const summary = computed(() => {
        const summaries: { [key: string]: { [CreatineGroup: string]: number } } = {};

        if (!groupedByInstance) {
            return summaries; // Return empty object if groupedByInstance is null or undefined
        }

        for (const instanceKey in groupedByInstance) {
            if (Object.prototype.hasOwnProperty.call(groupedByInstance, instanceKey)) {
                const items = groupedByInstance[instanceKey];

                if (!items) continue; // Skip if items is null or undefined

                const CreatineGroups: { [CreatineGroup: string]: number } = {
                    '0-44': 0,
                    '45-104': 0,
                    '105+': 0,
                };

                items.forEach(item => {
                    const Creatine = parseInt(item.value, 10); // Convert Creatine to number

                    if (isNaN(Creatine)) return; // Skip if Creatine is NaN

                    if (Creatine < 44.9) {
                        CreatineGroups['0-44']++;
                    } else if (Creatine >= 44.9 && Creatine < 104.9) {
                        CreatineGroups['45-104']++;
                    } else if (Creatine >= 105) {
                        CreatineGroups['105+']++;
                    }
                });

                let key: string | number;
                if (instanceKey === 'null') { // Use strict equality
                    key = 1;
                } else {
                    key = instanceKey;
                }

                summaries[key] = CreatineGroups;
            }

        }
        return summaries;


    });

    return summary;
}