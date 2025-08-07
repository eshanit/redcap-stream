<script setup lang="ts">
import useCalculateDifferences from '@/composables/useCalculateDifferences';

const props = defineProps<{
    counts: Record<string, number>
    diffTableTitle: string
    diffTableDescription: string
}>()

const differences = useCalculateDifferences(props.counts)
const keys = Object.keys(differences);

</script>
<template>
    <div class="p-4">
        <h1 class="text-xl font-bold mb-4">Differences Table</h1>
        <div class="py-2.5">
            {{ props.diffTableDescription }}
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse border border-gray-300">
                <thead>
                    <tr>
                        <th class="border border-gray-300 text-orange-500 px-4 py-2">{{ props.diffTableTitle }}</th>
                        <th v-for="key in keys" :key="key" class="border border-gray-300 px-4 py-2">
                            {{ key }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="key1 in keys" :key="key1">
                        <td class="border border-gray-300 px-4 py-2 font-bold">{{ key1 }}</td>
                        <td v-for="key2 in keys" :key="key2" class="border border-gray-300 px-4 py-2 text-center">
                            {{ differences[key1][key2] }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>