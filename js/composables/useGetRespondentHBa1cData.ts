import { Ref, ref, watch, reactive } from 'vue';

const useGetRespondentHBa1cData = (projectId: Ref<number>, record: Ref<string>) => {
    const isLoading = ref(false);
    const recordData = ref<Record<string, any[]>>({});
    const error = ref<Error | null>(null);

    const fetchData = async () => {
        isLoading.value = true;
        error.value = null;

        try {

            const response = await fetch(`/packages/project/${projectId.value}/hba1c_analytics/respondent/${record.value}/data`);

            const data = await response.json();
            if (!response.ok) throw new Error(data.message || "Failed to fetch HbA1c data");
            recordData.value = data;
        } catch (err) {
            error.value = err instanceof Error ? err : new Error('Unknown error occurred');
            recordData.value[record.value] = [];
        } finally {
            isLoading.value = false;
        }
    };

    // Auto-fetch when record changes
    watch([projectId, record], fetchData, { immediate: true });

    return {
        recordData,
        isLoading,
        error,
        fetchData
    };
};

export default useGetRespondentHBa1cData;