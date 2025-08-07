import { type IProjectData } from '@/types/IProject';

export default function useCountAgeGroups(dataPoints: IProjectData[]) {
    const ageGroups = {
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
        const age = parseInt(value, 10); // Convert age to number

        if (age >= 0 && age <= 10) {
            ageGroups['0-10']++;
        } else if (age >= 11 && age <= 20) {
            ageGroups['11-20']++;
        } else if (age >= 21 && age <= 30) {
            ageGroups['21-30']++;
        } else if (age >= 31 && age <= 40) {
            ageGroups['31-40']++;
        } else if (age >= 41 && age <= 50) {
            ageGroups['41-50']++;
        } else if (age >= 51 && age <= 60) {
            ageGroups['51-60']++;
        } else if (age >= 61 && age <= 70) {
            ageGroups['61-70']++;
        } else if (age > 70) {
            ageGroups['70+']++;
        }
    });

    return ageGroups;

}