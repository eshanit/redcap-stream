<script setup lang="ts">
/**
 * Flow (Sankey) diagram of patient pathways: one column per step, one bar per
 * service at that step, bands between steps sized by clients.
 *
 * Hand-drawn SVG - ApexCharts has no Sankey. Services are identified by their
 * direct text labels, not by colour: there are 9+ services (more than a
 * categorical palette can keep apart), so bars share one ink and bands one
 * blue. Every band and bar has a hover/focus tooltip; the pathways table on
 * the page is the table-view twin of this chart.
 *
 * Styling lives in SVG attributes, not CSS classes, so the serialised SVG is
 * self-contained and toImage() renders it faithfully for PNG/JPG download.
 */
import { computed, nextTick, ref } from 'vue';

interface FlowNode { id: string; stage: number; label: string; value: number; }
interface FlowLink { source: string; target: string; value: number; }

const props = defineProps<{ nodes: FlowNode[]; links: FlowLink[] }>();
/** Clicking (or Enter/Space on) a band asks the page to list its adolescents. */
const emit = defineEmits<{ selectLink: [link: FlowLink] }>();

const W = 1000;
const HEADER = 26;
const NODE_W = 14;
const GAP = 14;
const PLOT_H = 360;
const LABEL_MIN_H = 13;

interface PlacedNode extends FlowNode { x: number; y: number; h: number; }
interface PlacedLink extends FlowLink { d: string; key: string; }

const stages = computed(() => Math.max(0, ...props.nodes.map((n) => n.stage)) + 1);

const layout = computed(() => {
    const byStage: FlowNode[][] = Array.from({ length: stages.value }, () => []);
    for (const n of props.nodes) byStage[n.stage].push(n);
    for (const col of byStage) col.sort((a, b) => (a.label === 'Other' ? 1 : b.label === 'Other' ? -1 : b.value - a.value));

    const maxTotal = Math.max(1, ...byStage.map((col) => col.reduce((s, n) => s + n.value, 0)));
    const maxCount = Math.max(1, ...byStage.map((col) => col.length));
    const scale = (PLOT_H - GAP * (maxCount - 1)) / maxTotal;
    const colX = (stage: number) => (stages.value === 1 ? 0 : (stage * (W - NODE_W)) / (stages.value - 1));

    const placed = new Map<string, PlacedNode>();
    byStage.forEach((col, stage) => {
        let y = HEADER;
        for (const n of col) {
            const h = Math.max(2, n.value * scale);
            placed.set(n.id, { ...n, x: colX(stage), y, h });
            y += h + GAP;
        }
    });

    // Stack each band at its source (ordered by target position) and at its
    // target (ordered by source position) so bands never cross inside a bar.
    const outOffset = new Map<string, number>();
    const inOffset = new Map<string, number>();
    const sorted = [...props.links].sort((a, b) => {
        const sa = placed.get(a.source)!, sb = placed.get(b.source)!;
        const ta = placed.get(a.target)!, tb = placed.get(b.target)!;
        return sa.x - sb.x || sa.y - sb.y || ta.y - tb.y;
    });
    const sy = new Map<string, number>();
    for (const l of sorted) {
        const s = placed.get(l.source)!;
        const off = outOffset.get(l.source) ?? 0;
        sy.set(l.source + '>' + l.target, s.y + off);
        outOffset.set(l.source, off + l.value * scale);
    }
    const byTarget = [...props.links].sort((a, b) => placed.get(a.source)!.y - placed.get(b.source)!.y);
    const ty = new Map<string, number>();
    for (const l of byTarget) {
        const t = placed.get(l.target)!;
        const off = inOffset.get(l.target) ?? 0;
        ty.set(l.source + '>' + l.target, t.y + off);
        inOffset.set(l.target, off + l.value * scale);
    }

    const links: PlacedLink[] = props.links.map((l) => {
        const key = l.source + '>' + l.target;
        const s = placed.get(l.source)!, t = placed.get(l.target)!;
        const x0 = s.x + NODE_W, x1 = t.x, xm = (x0 + x1) / 2;
        const y0 = sy.get(key)!, y1 = ty.get(key)!, h = l.value * scale;
        const d = `M${x0},${y0} C${xm},${y0} ${xm},${y1} ${x1},${y1} L${x1},${y1 + h} C${xm},${y1 + h} ${xm},${y0 + h} ${x0},${y0 + h} Z`;
        return { ...l, d, key };
    });

    return { nodes: [...placed.values()], links, height: HEADER + PLOT_H + 4, colX };
});

const labelFor = (id: string) => props.nodes.find((n) => n.id === id)?.label ?? '';
const stageFor = (id: string) => Number(id.split('|')[0]) + 1;

// ---- hover / focus tooltip ------------------------------------------------
const wrap = ref<HTMLElement | null>(null);
const active = ref<string | null>(null);
const tip = ref<{ x: number; y: number; title: string; body: string } | null>(null);

function show(key: string, title: string, body: string, ev: MouseEvent | FocusEvent): void {
    active.value = key;
    const box = wrap.value!.getBoundingClientRect();
    if (ev instanceof MouseEvent) {
        tip.value = { x: ev.clientX - box.left, y: ev.clientY - box.top, title, body };
    } else {
        const r = (ev.target as Element).getBoundingClientRect();
        tip.value = { x: r.left + r.width / 2 - box.left, y: r.top + r.height / 2 - box.top, title, body };
    }
}
function hide(): void {
    active.value = null;
    tip.value = null;
}
function showLink(l: PlacedLink, ev: MouseEvent | FocusEvent): void {
    show(l.key, `${labelFor(l.source)} → ${labelFor(l.target)}`, `${l.value} adolescents · step ${stageFor(l.source)} → ${stageFor(l.target)} · click to list them`, ev);
}
function selectLink(l: PlacedLink): void {
    emit('selectLink', { source: l.source, target: l.target, value: l.value });
}
function showNode(n: PlacedNode, ev: MouseEvent | FocusEvent): void {
    show(n.id, n.label, `${n.value} adolescents at step ${n.stage + 1}`, ev);
}
function linkOpacity(l: PlacedLink): number {
    if (!active.value) return 0.3;
    return active.value === l.key || l.source === active.value || l.target === active.value ? 0.62 : 0.1;
}

// ---- image export ---------------------------------------------------------
const FONT = 'system-ui, -apple-system, "Segoe UI", sans-serif';
const svgEl = ref<SVGSVGElement | null>(null);

/** Renders the diagram (with a title band) to a PNG/JPG data URI at 2x. */
async function toImage(format: 'png' | 'jpg', title: string, subtitle: string): Promise<string | null> {
    hide();
    await nextTick();
    if (!svgEl.value) return null;

    const w = W, h = layout.value.height, pad = 24, titleH = 48, scale = 2;
    const clone = svgEl.value.cloneNode(true) as SVGSVGElement;
    clone.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
    clone.setAttribute('width', String(w));
    clone.setAttribute('height', String(h));
    const img = new Image();
    img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(new XMLSerializer().serializeToString(clone));
    await img.decode();

    const canvas = document.createElement('canvas');
    canvas.width = (w + pad * 2) * scale;
    canvas.height = (h + titleH + pad * 2) * scale;
    const ctx = canvas.getContext('2d');
    if (!ctx) return null;
    ctx.scale(scale, scale);
    ctx.fillStyle = '#fcfcfb';
    ctx.fillRect(0, 0, w + pad * 2, h + titleH + pad * 2);
    ctx.fillStyle = '#0b2c2c';
    ctx.font = `700 18px ${FONT}`;
    ctx.fillText(title, pad, pad + 16);
    ctx.fillStyle = '#52514e';
    ctx.font = `400 12px ${FONT}`;
    ctx.fillText(subtitle, pad, pad + 36);
    ctx.drawImage(img, pad, pad + titleH, w, h);

    return canvas.toDataURL(format === 'png' ? 'image/png' : 'image/jpeg', 0.95);
}
defineExpose({ toImage });
</script>

<template>
    <div ref="wrap" class="relative overflow-x-auto print:overflow-visible">
        <svg ref="svgEl" :viewBox="`0 0 ${W} ${layout.height}`" class="block w-full min-w-[860px] print:min-w-0" role="img" aria-label="Flow of adolescents between services, step by step" @mouseleave="hide">
            <text v-for="s in stages" :key="`h${s}`" :x="layout.colX(s - 1) + (s === stages && stages > 1 ? NODE_W : 0)" y="14"
                :text-anchor="s === stages && stages > 1 ? 'end' : 'start'" fill="#898781" font-size="11" font-weight="700" letter-spacing="0.8" :font-family="FONT">
                {{ s === 1 ? 'STEP 1 · ENTRY' : `STEP ${s}` }}
            </text>
            <g>
                <path v-for="l in layout.links" :key="l.key" :d="l.d" fill="#2a78d6" :fill-opacity="linkOpacity(l)" class="cursor-pointer outline-none transition-[fill-opacity] duration-150"
                    tabindex="0" role="button" :aria-label="`${labelFor(l.source)} to ${labelFor(l.target)}, ${l.value} adolescents - list them`"
                    @mousemove="showLink(l, $event)" @focus="showLink(l, $event)" @blur="hide"
                    @click="selectLink(l)" @keydown.enter.prevent="selectLink(l)" @keydown.space.prevent="selectLink(l)" />
            </g>
            <g>
                <rect v-for="n in layout.nodes" :key="n.id" :x="n.x" :y="n.y" :width="NODE_W" :height="n.h" rx="2"
                    :fill="n.label === 'Other' ? '#b9b8b0' : '#173b3b'" class="cursor-default outline-none" tabindex="0"
                    @mousemove="showNode(n, $event)" @focus="showNode(n, $event)" @blur="hide" />
            </g>
            <g class="pointer-events-none">
                <text v-for="n in layout.nodes.filter((n) => n.h >= LABEL_MIN_H)" :key="`l${n.id}`"
                    :x="n.stage === stages - 1 && stages > 1 ? n.x - 6 : n.x + NODE_W + 6" :y="n.y + n.h / 2" dy="0.35em"
                    :text-anchor="n.stage === stages - 1 && stages > 1 ? 'end' : 'start'"
                    fill="#0b2c2c" font-size="12" font-weight="600" :font-family="FONT" stroke="#fcfcfb" stroke-width="4" paint-order="stroke" stroke-linejoin="round">
                    {{ n.label }} · {{ n.value }}
                </text>
            </g>
        </svg>
        <div v-if="tip" class="pointer-events-none absolute z-10 max-w-64 -translate-x-1/2 -translate-y-[calc(100%+10px)] rounded border border-[rgba(11,11,11,0.10)] bg-white px-3 py-2 text-xs shadow-lg"
            :style="{ left: `${tip.x}px`, top: `${tip.y}px` }">
            <p class="font-bold text-[#0b2c2c]">{{ tip.title }}</p>
            <p class="mt-0.5 text-[#52514e]">{{ tip.body }}</p>
        </div>
    </div>
</template>
