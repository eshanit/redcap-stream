<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { AlertTriangle, ArrowRight, FlaskConical, Info, Layers } from 'lucide-vue-next';
import { computed } from 'vue';

interface IndicatorMeta {
    id: number;
    code?: string;
    key: string;
    group: string;
    type: 'count' | 'percent' | 'sum';
    status: 'active' | 'provisional' | 'proxy' | 'blocked';
    label: string;
    definition: string;
    note: string | null;
    no_period?: boolean;
    variables?: string | null;
}
interface IndicatorValue { value: number | null; numerator?: number; denominator?: number; extra?: Record<string, number>; }

const props = withDefaults(
    defineProps<{
        meta: IndicatorMeta;
        value?: IndicatorValue;
        method?: string;
        /**
         * Paired-card styling, deliberately loud so the second card is never
         * mistaken for (or confused with) the one next to it:
         *  - 'proxy': an interim/pre-go-live stand-in for the real indicator
         *    it sits beside (e.g. AHP023a next to AHP023) - amber wash,
         *    dashed border, hatch pattern.
         *  - 'supplementary': a permanent, equally-valid alternate
         *    calculation shown alongside the official one, not a temporary
         *    substitute (e.g. AHP004a "all entry points" next to AHP004) -
         *    blue wash, solid border.
         */
        variant?: 'default' | 'proxy' | 'supplementary';
    }>(),
    { variant: 'default' },
);

const statusBadges: Record<string, { label: string; cls: string }> = {
    provisional: { label: 'Definition pending', cls: 'bg-[#e7eef4] text-[#31577a]' },
    blocked: { label: 'Not computable', cls: 'bg-[#f0efec] text-[#898781]' },
};

const VARIANT_STYLES = {
    proxy: {
        wrapper: 'border-2 border-dashed border-[#d8b872] bg-[#fbf3e3] bg-[repeating-linear-gradient(135deg,transparent,transparent_9px,rgba(143,97,21,0.07)_9px,rgba(143,97,21,0.07)_10px)]',
        badge: 'bg-[#8f6115]',
        badgeIcon: FlaskConical,
        badgeLabel: 'Interim proxy — pre-go-live',
        title: 'text-[#6b4a12]',
        code: 'text-[#9c8352]',
        value: 'text-[#8f6115]',
        divider: 'border-[#eadfc4]',
        link: 'bg-[#8f6115] hover:bg-[#a87524]',
    },
    supplementary: {
        wrapper: 'border border-[#a9c3e0] bg-[#eef3fa]',
        badge: 'bg-[#31577a]',
        badgeIcon: Layers,
        badgeLabel: 'Supplementary — alternate definition',
        title: 'text-[#1f3f5c]',
        code: 'text-[#5e7fa0]',
        value: 'text-[#264a6b]',
        divider: 'border-[#d7e3ef]',
        link: 'bg-[#31577a] hover:bg-[#3d6791]',
    },
} as const;

const style = computed(() => (props.variant === 'default' ? null : VARIANT_STYLES[props.variant]));

function fmt(n: number | null | undefined): string {
    if (n === null || n === undefined) return '—';
    return n.toLocaleString();
}
</script>

<template>
    <article
        class="flex h-full flex-col justify-between p-4 print:break-inside-avoid"
        :class="[style ? style.wrapper : 'border border-[#d9ded7] bg-[#fcfcfb]', meta.status === 'blocked' ? 'opacity-70' : '']"
    >
        <div>
            <div v-if="style" class="mb-2 inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-white" :class="style.badge">
                <component :is="style.badgeIcon" class="size-3" />{{ style.badgeLabel }}
            </div>
            <div class="flex items-start justify-between gap-2">
                <h3 class="text-[13px] font-bold leading-snug" :class="style ? style.title : 'text-[#244847]'">
                    <span class="mr-1.5 font-mono text-[10px]" :class="style ? style.code : 'text-[#898781]'">{{ meta.code ?? meta.id }}</span>{{ meta.label }}
                </h3>
                <span v-if="!style && statusBadges[meta.status]"
                    class="shrink-0 rounded-full px-2 py-0.5 text-[9.5px] font-bold uppercase tracking-wide"
                    :class="statusBadges[meta.status].cls">{{ statusBadges[meta.status].label }}</span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-semibold" :class="style ? style.value : 'text-[#0b2c2c]'">
                    {{ meta.type === 'percent' && value?.value !== null && value ? `${fmt(value?.value)}%` : fmt(value?.value) }}
                </span>
                <span v-if="meta.type === 'percent' && value?.denominator !== undefined"
                    class="text-xs text-[#788681]" style="font-variant-numeric: tabular-nums">
                    {{ fmt(value?.numerator) }} / {{ fmt(value?.denominator) }}
                </span>
                <span v-if="value?.extra?.bba !== undefined" class="text-xs text-[#788681]">
                    incl. {{ value?.extra?.bba }} BBA
                </span>
            </div>
        </div>
        <div class="mt-3 space-y-1 border-t pt-2" :class="style ? style.divider : 'border-[#eef0eb]'">
            <p class="flex items-start gap-1.5 text-[11px] leading-4 text-[#7d8b85]">
                <Info class="mt-0.5 size-3 shrink-0" />{{ meta.definition }}
            </p>
            <p v-if="meta.note" class="flex items-start gap-1.5 text-[11px] leading-4 text-[#a87524]">
                <AlertTriangle class="mt-0.5 size-3 shrink-0" />{{ meta.note }}
            </p>
            <p v-if="meta.no_period" class="flex items-start gap-1.5 text-[11px] leading-4 text-[#7d8b85]">
                <AlertTriangle class="mt-0.5 size-3 shrink-0 text-[#a87524]" />No date field on this instrument — value is all-time, the period filter does not apply.
            </p>
            <details v-if="method" class="pt-0.5">
                <summary class="cursor-pointer text-[11px] font-bold text-[#3c605b] underline decoration-[#cbd3cd] decoration-dotted underline-offset-2 hover:decoration-[#e2644b]">
                    How we calculated this
                </summary>
                <p class="mt-1 border-l-2 border-[#e2644b] pl-2 text-[11px] leading-4.5 text-[#52655f]">{{ method }}</p>
                <p v-if="meta.variables" class="mt-1 pl-2 font-mono text-[10px] text-[#7b8984]">{{ meta.variables }}</p>
            </details>
            <Link v-if="meta.code && meta.status !== 'blocked'" :href="`/data6/indicators/${meta.code}`"
                class="group mt-1.5 inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[11px] font-bold text-white transition"
                :class="style ? style.link : 'bg-[#173b3b] hover:bg-[#285655]'">
                Deeper analysis
                <ArrowRight class="size-3 transition group-hover:translate-x-0.5" />
            </Link>
        </div>
    </article>
</template>
