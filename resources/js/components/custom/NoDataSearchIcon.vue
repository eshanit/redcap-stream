<template>
  <svg
    xmlns="http://www.w3.org/2000/svg"
    :width="size"
    :height="size"
    viewBox="0 0 24 24"
    fill="none"
    :stroke="color"
    stroke-width="1.5"
    stroke-linecap="round"
    stroke-linejoin="round"
    class="no-data-search-icon"
    :class="{ 'has-hover': hoverEffect }"
  >
    <!-- Folded paper background -->
    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z" 
          :fill="paperColor" 
          stroke="#d1d5db" />
    <path d="M14 2v6h6" :stroke="paperFoldColor" />
    
    <!-- Magnifying glass -->
    <circle cx="11" cy="11" r="6" :stroke="iconColor" />
    <line x1="15" y1="15" x2="19" y2="19" :stroke="iconColor" />
    
    <!-- X mark -->
    <line x1="9" y1="9" x2="13" y2="13" :stroke="strikeColor" stroke-width="2" />
    <line x1="13" y1="9" x2="9" y2="13" :stroke="strikeColor" stroke-width="2" />
  </svg>
</template>

<script setup lang="ts">
interface Props {
  size?: number | string;
  color?: string;
  iconColor?: string;
  strikeColor?: string;
  paperColor?: string;
  paperFoldColor?: string;
  hoverEffect?: boolean;
  hoverColor?: string;
}

withDefaults(defineProps<Props>(), {
  size: 24,
  color: 'currentColor',
  iconColor: '#4b5563',    // gray-600
  strikeColor: '#ef4444',  // red-500
  paperColor: '#f9fafb',   // gray-50
  paperFoldColor: '#9ca3af', // gray-400
  hoverEffect: true,
  hoverColor: '#ef4444',   // red-500
});
</script>

<style scoped>
.no-data-search-icon {
  transition: all 0.2s ease-in-out;
}

.no-data-search-icon.has-hover:hover {
  stroke: v-bind(hoverColor);
}

.no-data-search-icon.has-hover:hover :is(circle, line) {
  stroke: v-bind(hoverColor);
}
</style>