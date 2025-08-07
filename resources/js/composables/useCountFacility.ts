import { type IProjectData } from '@/types/IProject';

export default function useCountFacility(dataPoints: IProjectData[]) {
    // Define a type for the facility counts
    type IFacilityCounts = Record<string, number>;

    const facilityCounts: IFacilityCounts = dataPoints.reduce((acc: IFacilityCounts, entry) => {
        const facility = entry.value || ''; // Use an empty string if value is not present

        // Initialize the count for the facility
        acc[facility] = (acc[facility] || 0) + 1;

        return acc;
    }, {});

    return facilityCounts; // Return the counts
}