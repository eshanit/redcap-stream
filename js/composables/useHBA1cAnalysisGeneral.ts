import { type IProjectData } from '@/types/IProject';
import * as ss from 'simple-statistics';
import { ref } from 'vue';

export function useHBA1cAnalysisGeneral(data: IProjectData[]) {
    const grouped = ref<{ [key: string]: IProjectData[] }>({});
    const categories = ref<{ [key: string]: IProjectData[] }>({
      Normal: [],
      Prediabetic: [],
      Diabetic: [],
    });
    
    // Counts for each category
    const categoryCounts = ref<{ [key: string]: number }>({
      Normal: 0,
      Prediabetic: 0,
      Diabetic: 0,
    });
    
    // Overall statistics
    const overallMean = ref<number>(0);
    const overallMedian = ref<number>(0);
    const overallStdDev = ref<number>(0);
  
    const values = data.map(item => parseFloat(item.value));
  
    // Analyze data
    data.forEach(item => {
      const value = parseFloat(item.value);
      
      // Group data by value
      if (!grouped.value[item.value]) {
        grouped.value[item.value] = [];
      }
      grouped.value[item.value].push(item);
  
      // Categorize based on HBA1c value
      if (value < 5.7) {
        categories.value.Normal.push(item);
        categoryCounts.value.Normal++;
      } else if (value >= 5.7 && value < 6.5) {
        categories.value.Prediabetic.push(item);
        categoryCounts.value.Prediabetic++;
      } else {
        categories.value.Diabetic.push(item);
        categoryCounts.value.Diabetic++;
      }
    });
  
    // Calculate overall statistics
    if (values.length > 0) {
      overallMean.value = ss.mean(values);
      overallMedian.value = ss.median(values);
      overallStdDev.value = ss.standardDeviation(values);
    }
  
    return {
      grouped,
      categories,
      categoryCounts,
      overallMean,
      overallMedian,
      overallStdDev,
    };
}