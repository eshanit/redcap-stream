import { type IProjectData } from '@/types/IProject';
import * as ss from 'simple-statistics';

export default function useSimpleStatistics(dataPoints: IProjectData[]) {

    const dataValues = dataPoints.map(entry => parseInt(entry.value || '0', 10)).filter(dataValue => !isNaN(dataValue));

    // Descriptive Statistics
    const meanValue = ss.mean(dataValues);
    const medianValue = ss.median(dataValues);
    const modeValue = ss.mode(dataValues);

    return {
        mean: meanValue.toFixed(2),
        median: medianValue,
        mode: modeValue
    }

}