import { type IProjectData } from '@/types/IProject';
import useCountRespondents from './useCountRespondents';

export default function useCountYesNo(dataPoints: IProjectData[]) {

    let yesCount = 0;
    let noCount = 0;

    dataPoints.forEach(entry => {
        if (entry.value === "1") {
            yesCount++;
        } else if (entry.value === "0") {
            noCount++;
        }
    });

    const percYes = (100 * yesCount / useCountRespondents(dataPoints)).toFixed(2)
    const percNo = (100 * noCount / useCountRespondents(dataPoints)).toFixed(2)

    return {
        yes: yesCount,
        no: noCount,
        pyes: percYes,
        pno: percNo
    };

}