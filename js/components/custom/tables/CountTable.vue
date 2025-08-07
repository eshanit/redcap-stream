<script setup lang="ts">
import { defineProps } from 'vue';

const props = defineProps<{
  tableData: any;
  headerText: string
}>();
</script>

<style scoped>
/* Add any additional styles here */
</style>
<template>
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white border border-gray-300">
        <thead>
          <tr class="bg-gray-200">
            <th class="py-2 px-4 border-b text-left">{{ props.headerText }}</th>
            <th class="py-2 px-4 border-b text-left">Count</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(count, data) in props.tableData" :key="data">
            <td class="py-2 px-4 border-b">{{ data }}</td>
            <td class="py-2 px-4 border-b">{{ count }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </template>
  
