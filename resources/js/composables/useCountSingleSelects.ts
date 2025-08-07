import { type IProjectData } from '@/types/IProject';
import useTransformSelect from './useTransformSelect';

// Define the type for the labels object
interface LabelsCount {
    [key: string]: number;
}

export default function useCountSingleSelect(dataPoints: IProjectData[]): LabelsCount {
    if (dataPoints.length > 0 && dataPoints[0].element_enum) {
        const labelsArray = useTransformSelect(dataPoints[0].element_enum);

        // Create an initial object with counts set to 0
        const labelsObject: LabelsCount = labelsArray.reduce((acc: LabelsCount, item) => {
            acc[item.name] = 0; // Initialize the value to 0
            return acc;
        }, {});

        // Collect answers from labelsArray
        const answers = labelsArray.map(v => v.value.trim());

        // Count occurrences of each answer in dataPoints
        dataPoints.forEach(entry => {
            const value = entry.value.trim() || ''; // Get the value or default to an empty string

            // Increment the count for matching answers
            if (answers.includes(value)) {
                const key = labelsArray.find(l => l.value.trim() == value)?.name;
                if (key) {
                    labelsObject[key] += 1; // Increment the count
                }
            }
        });

        return labelsObject; // Return the final counts object
    }

    return {}; // Return an empty object if no data points are available
}