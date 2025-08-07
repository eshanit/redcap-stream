import { type IProjectData } from '@/types/IProject';

export default function useGroupByInstance(dataPoints: IProjectData[]){
    const groups: { [key: string]: IProjectData[] } = {};
    
    dataPoints.forEach(item => {
      const instanceKey = item.instance === null ? 'null' : item.instance.toString(); // Use 'null' as key for null instances
      if (!groups[instanceKey]) {
        groups[instanceKey] = [];
      }
      groups[instanceKey].push(item);
    });

    return groups;
  };
