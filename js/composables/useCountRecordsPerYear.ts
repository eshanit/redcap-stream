import { type IProjectData } from '@/types/IProject'
import { parseISO, getYear } from 'date-fns';

export default function useCountRecordsPerYear(dataPoints: IProjectData[]) {

    const counts: Record<number, number> = {};

    dataPoints.forEach(entry => {

        let value = '';

        if(entry.value){
            value = entry.value
        }

        const year = getYear(parseISO(value)); // Use date-fns to parse and get the year
        counts[year] = (counts[year] || 0) + 1;

  });

  return counts;
  
}