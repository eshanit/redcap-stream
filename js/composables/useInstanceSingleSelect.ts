import { computed } from 'vue';
import { type IProjectData } from '@/types/IProject';
import useGroupByInstance from './useGroupByInstance';
import useTransformSelect from './useTransformSelect';

// Define the type for the labels object
interface LabelsCount {
    [key: string]: number;
}

export default function useInstanceSingleSelect(dataPoints: IProjectData[]) {

    const groupedByInstance = useGroupByInstance(dataPoints)

    const summary = computed(() => {
        if (dataPoints.length > 0 && dataPoints[0].element_enum) {
            const summaries: { [key: string]: any } = {};

            const labelsArray = useTransformSelect(dataPoints[0].element_enum)

            for (const instanceKey in groupedByInstance) { // Access groupedByInstance.value
                if (Object.prototype.hasOwnProperty.call(groupedByInstance, instanceKey)) {

                    const items = groupedByInstance[instanceKey]; // Access groupedByInstance.value

                    // Create an initial object with counts set to 0
                    const labelsObject: LabelsCount = labelsArray.reduce((acc: LabelsCount, item) => {
                        acc[item.name] = 0; // Initialize the value to 0
                        return acc;
                    }, {});

                    // Collect answers from labelsArray
                    const answers = labelsArray.map(v => v.value);

                    items.forEach(item => {
                        const value = item.value || ''; // Get the value or default to an empty string

                        // Increment the count for matching answers
                        if (answers.includes(value)) {
                            const key = labelsArray.find(l => l.value === value)?.name;
                            if (key) {
                                labelsObject[key] += 1; // Increment the count
                            }
                        }

                    });

                    let key: string | number; // Define key outside the if/else block
                    if (instanceKey == 'null') {
                        key = 1; // Changed to -1 to match "Visit -1"
                    } else {
                        key = parseInt(instanceKey); // Changed to -1 to match "Visit -1"
                    }

                    summaries[key] = labelsObject;

                }
            }

            return summaries; // Return summaries after the loop
        }

        return {}; // Return an empty object if the condition is not met
    })

    return summary

}