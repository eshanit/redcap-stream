import { type IProjectData } from '@/types/IProject';

export default function useCountMarital(dataPoints: IProjectData[]) {
 

    let marriedCount = 0;
    let neverMarriedCount = 0;
    let widowedCount = 0;
    let divocedCount = 0;
    let togetherCount = 0;
    let minorCount = 0;



    dataPoints.forEach(entry => {
        if (entry.value === "1") {
            marriedCount++;
        } else if (entry.value === "2") {
            neverMarriedCount++;
        } else if (entry.value === "3") {
            widowedCount++
        } else if (entry.value === "4") {
            divocedCount++;
        }else if (entry.value === "5") {
            togetherCount++;
        }else if (entry.value === "6") {
            minorCount++;
        }
    });

    return { 
        "Married": marriedCount,
        "Never Married": neverMarriedCount,
        "Widowed": widowedCount,
        "Divorced/Seperated": divocedCount,
        "Staying Together": togetherCount,
        "Minor": minorCount
     };
}