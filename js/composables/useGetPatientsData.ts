import { Ref, ref, watch, reactive } from 'vue';

const useGetPatientsData = (projectId: Ref<number>,type: Ref<string> ,condition: Ref<string>, tag: Ref<string>) => {
    const isLoading = ref(false);
    const itemData = ref<Record<string, any[]>>({});
    const error = ref<Error | null>(null);

    const fetchData = async () => {
        isLoading.value = true;
        error.value = null;

        try {

            const response = await fetch(`/packages/project/${projectId.value}/core_indicators/${type.value}/${condition.value}/${tag.value}/data`);

            if (!response.ok) throw new Error('Network response was not ok');

            const data = await response.json();
            
            itemData.value[condition.value] = data.data;

            console.log('data', itemData.value[condition.value] )

        } catch (err) {
            error.value = err instanceof Error ? err : new Error('Unknown error occurred');
            itemData.value[condition.value] = [];
        } finally {
            isLoading.value = false;
        }
    };

    // Auto-fetch when condition changes
    watch([projectId, condition], fetchData, { immediate: true });

    return {
        itemData,
        isLoading,
        error,
        fetchData
    };
};

export default useGetPatientsData;