import { type IProjectData } from '@/types/IProject';

export default function useCountWeightGroups(dataPoints: IProjectData[]) {
    const weightGroups = {
        '0-10': 0,
        '11-20': 0,
        '21-30': 0,
        '31-40': 0,
        '41-50': 0,
        '51-60': 0,
        '61-70': 0,
        '70+': 0
    };

    dataPoints.forEach(entry => {

        let value = '';

        if(entry.value){
            value = entry.value
        }
        const weight = parseInt(value, 10); // Convert weight to number

        if (weight >= 0 && weight <= 10) {
            weightGroups['0-10']++;
        } else if (weight >= 11 && weight <= 20) {
            weightGroups['11-20']++;
        } else if (weight >= 21 && weight <= 30) {
            weightGroups['21-30']++;
        } else if (weight >= 31 && weight <= 40) {
            weightGroups['31-40']++;
        } else if (weight >= 41 && weight <= 50) {
            weightGroups['41-50']++;
        } else if (weight >= 51 && weight <= 60) {
            weightGroups['51-60']++;
        } else if (weight >= 61 && weight <= 70) {
            weightGroups['61-70']++;
        } else if (weight > 70) {
            weightGroups['70+']++;
        }
    });

    return weightGroups;

}