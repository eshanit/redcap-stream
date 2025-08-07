<script setup lang="ts">
import { defineProps } from 'vue';

const props = defineProps<{
  genderData: Record<string, number>;
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
            <th class="py-2 px-4 border-b text-left">Gender</th>
            <th class="py-2 px-4 border-b text-left">Count</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(count, gender) in genderData" :key="gender">
            <td class="py-2 px-4 border-b">{{ gender }}</td>
            <td class="py-2 px-4 border-b">{{ count }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </template>
  
