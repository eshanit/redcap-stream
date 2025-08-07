import { computed } from 'vue';
import { type IProjectData } from '@/types/IProject';
import useGroupByInstance from './useGroupByInstance';

export default function useInstanceHeightGroups(data: IProjectData[]) {

    const groupedByInstance = useGroupByInstance(data);

    const summary = computed(() => {
        const summaries: { [key: string]: { [heightGroup: string]: number } } = {};

        if (!groupedByInstance) {
            return summaries; // Return empty object if groupedByInstance is null or undefined
        }

        for (const instanceKey in groupedByInstance) {
            if (Object.prototype.hasOwnProperty.call(groupedByInstance, instanceKey)) {
                const items = groupedByInstance[instanceKey];

                if (!items) continue; // Skip if items is null or undefined

                const heightGroups: { [heightGroup: string]: number } = {
                    '<1': 0,
                    '1-1.49': 0,
                    '1.5-1.99': 0,
                    '2+': 0
                };

                items.forEach(item => {
                    const height = parseInt(item.value, 10); // Convert height to number

                    if (isNaN(height)) return; // Skip if height is NaN

                    if (height >= 0 && height <= 1) {
                        heightGroups['<1']++;
                    } else if (height >= 1 && height <= 1.5) {
                        heightGroups['1-1.49']++;
                    } else if (height > 1.5 && height <= 2) {
                        heightGroups['1.5-1.99']++;
                    } else if (height > 2) {
                        heightGroups['2+']++;
                    }
                });

                let key: string | number;
                if (instanceKey === 'null') { // Use strict equality
                    key = 1;
                } else {
                    key = instanceKey;
                }

                summaries[key] = heightGroups;
            }

        }
        return summaries;


    });

    return summary;
}