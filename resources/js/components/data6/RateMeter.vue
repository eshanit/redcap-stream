<script setup lang="ts">
import { Download } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

// A single ratio against a limit reads best as a meter, not a bar or a bare
// number (dataviz skill: "a single ratio against a limit -> meter, same-ramp
// track"). radialBar in single-series mode is ApexCharts' native meter —
// reused here for every "X of Y" headline in the dashboard (VL coverage,
// HTS reconciliation gap rate, ...) instead of re-building a stat tile
// or a bar chart each time.
interface ApexChartHandle {
    dataURI(options?: { scale?: number }): Promise<{ imgURI?: string }>;
}

const props = withDefaults(defineProps<{
    label: string;
    value: number | null;
    caption?: string | null;
    color?: string;
    filename?: string;
}>(), {
    caption: null,
    color: '#2a78d6',
    filename: 'rate',
});

const gridHairline = '#e1e0d9';
const inkSecondary = '#52514e';

const chartRef = ref<ApexChartHandle | null>(null);
const displayValue = computed(() => props.value ?? 0);

const options = computed(() => ({
    chart: { type: 'radialBar', toolbar: { show: false }, fontFamily: 'system-ui, sans-serif', animations: { enabled: false } },
    colors: [props.color],
    plotOptions: {
        radialBar: {
            hollow: { size: '62%' },
            track: { background: gridHairline, strokeWidth: '100%' },
            dataLabels: {
                name: { show: true, offsetY: 18, color: inkSecondary, fontSize: '11px', fontWeight: 700 },
                value: {
                    show: true, offsetY: -14, color: '#0b2c2c', fontSize: '26px', fontWeight: 600, fontFamily: 'system-ui, sans-serif',
                    formatter: () => (props.value === null ? '—' : `${props.value}%`),
                },
            },
        },
    },
    labels: [props.label],
    stroke: { lineCap: 'round' },
}));
const series = computed(() => [displayValue.value]);

async function downloadImage(format: 'png' | 'jpg'): Promise<void> {
    const result = await chartRef.value?.dataURI({ scale: 2 });
    const pngUri = result?.imgURI ?? null;
    if (!pngUri) return;

    const filename = `${props.filename}.${format}`;
    if (format === 'png') {
        triggerDownload(pngUri, filename);

        return;
    }
    const img = new Image();
    img.onload = () => {
        const canvas = document.createElement('canvas');
        canvas.width = img.width;
        canvas.height = img.height;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;
        ctx.fillStyle = '#fcfcfb';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0);
        triggerDownload(canvas.toDataURL('image/jpeg', 0.95), filename);
    };
    img.src = pngUri;
}
function triggerDownload(dataUri: string, filename: string): void {
    const link = document.createElement('a');
    link.href = dataUri;
    link.download = filename;
    link.click();
}
</script>

<template>
    <div class="flex flex-wrap items-center gap-4">
        <VueApexCharts ref="chartRef" type="radialBar" height="180" width="180" :options="options" :series="series" />
        <div class="min-w-[160px]">
            <p v-if="caption" class="text-sm text-[#60716d]" style="font-variant-numeric: tabular-nums">{{ caption }}</p>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadImage('png')"><Download class="size-3" />PNG</button>
                <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadImage('jpg')"><Download class="size-3" />JPG</button>
            </div>
        </div>
    </div>
</template>
