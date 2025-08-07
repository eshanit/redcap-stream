import { computed } from 'vue';
import { type IProjectData } from '@/types/IProject';
import useGroupByInstance from './useGroupByInstance';

export default function useInstanceYesNo(data: IProjectData[]) {

    const groupedByInstance = useGroupByInstance(data)
    
      const summary = computed(() => {
        const summaries: { [key: string]: { yes: number; no: number } } = {};
        let key ;

        for (const instanceKey in groupedByInstance) {
          if (Object.prototype.hasOwnProperty.call(groupedByInstance, instanceKey)) {
            const items = groupedByInstance[instanceKey];
            let yesCount = 0;
            let noCount = 0;
    
            items.forEach(item => {
              if (item.value === '1') {
                yesCount++;
              } else if (item.value === '0') {
                noCount++;
              }
            });
           
            if( instanceKey == 'null' ) {
                key = 1
            } else {
                key = instanceKey
            }

            summaries[key] = { yes: yesCount, no: noCount };

          }
        }
    
        return summaries;
      });
    
      return { groupedByInstance, summary };
  }