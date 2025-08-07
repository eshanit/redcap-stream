import { Ref, ref, watch, reactive } from 'vue';

const useGetFieldNameData = (projectId: Ref<number>, fieldName: Ref<string>) => {
    const isLoading = ref(false);
    const itemData = ref<Record<string, any[]>>({});
    const error = ref<Error | null>(null);

    const fetchData = async () => {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch(`/project/${projectId.value}/questionnaire/${fieldName.value}/data`);

            if (!response.ok) throw new Error('Network response was not ok');

            const data = await response.json();
            itemData.value[fieldName.value] = data.data;
        } catch (err) {
            error.value = err instanceof Error ? err : new Error('Unknown error occurred');
            itemData.value[fieldName.value] = [];
        } finally {
            isLoading.value = false;
        }
    };

    // Auto-fetch when fieldName changes
    watch([projectId, fieldName], fetchData, { immediate: true });

    return {
        itemData,
        isLoading,
        error,
        fetchData
    };
};

export default useGetFieldNameData;