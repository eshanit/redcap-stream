<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 42" :class="className" v-bind="$attrs">
        <defs>
            <linearGradient id="mainGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#4A90E2" /> <!-- Blue -->
                <stop offset="100%" stop-color="#50E3C2" /> <!-- Teal -->
            </linearGradient>
        </defs>

        <!-- Base shape with gradient -->
        <rect width="40" height="42" rx="10" fill="url(#mainGradient)"/>

        <!-- Central geometric shape -->
        <polygon points="20,8 25,32 15,32" fill="#FFF" />

        <!-- Accent circle -->
        <circle cx="20" cy="20" r="4" fill="#4A90E2" />
    </svg>
</template>