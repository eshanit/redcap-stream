import { type IProjectData } from '@/types/IProject';

export default function useCountRespondents(dataPoints: IProjectData[]) {

    const allRecords = dataPoints.map(r => r.record)

    const uniqueRecords = new Set(allRecords)
 
    // Convert the Set back to an array
    const records = Array.from(uniqueRecords);

    return records.length;

}