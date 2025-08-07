import { type IProjectData } from '@/types/IProject';

export default function useCountGender(dataPoints: IProjectData[]) {

    let maleCount = 0;
    let femaleCount = 0;

    dataPoints.forEach(entry => {
        if (entry.value === "1") {
            maleCount++;
        } else if (entry.value === "2") {
            femaleCount++;
        }
    });

    return { males: maleCount, females: femaleCount };

}