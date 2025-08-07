import { computed } from 'vue';
import { type IProjectData } from '@/types/IProject'
import { parseISO, getYear } from 'date-fns';
import useGroupByInstance from './useGroupByInstance';

export default function useInstanceCountRecordsPerYear(dataPoints: IProjectData[]) {

    const groupedByInstance = useGroupByInstance(dataPoints)

    const summary = computed(() => {
        const summaries: { [key: string]: any } = {};
        //
        for (const instanceKey in groupedByInstance) {
            if (Object.prototype.hasOwnProperty.call(groupedByInstance, instanceKey)) {
                const items = groupedByInstance[instanceKey];
                const counts: Record<number, number> = {};

                items.forEach(entry => {

                    let value = '';

                    if (entry.value) {
                        value = entry.value
                    }

                    const year = getYear(parseISO(value)); // Use date-fns to parse and get the year
                    counts[year] = (counts[year] || 0) + 1;

                });

                let key: string | number; // Define key outside the if/else block
                if (instanceKey == 'null') {
                    key = 1; // Changed to -1 to match "Visit -1"
                } else {
                    key = parseInt(instanceKey); // Changed to -1 to match "Visit -1"
                }

                summaries[key] = counts;

            }
        }

        //
        return summaries

    })

    return summary;
}