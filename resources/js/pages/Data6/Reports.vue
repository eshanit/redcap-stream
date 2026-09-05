<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { AlertTriangle, ChevronDown, CircleAlert, Download, FileSpreadsheet, HelpCircle, RefreshCw } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { type BreadcrumbItem } from '@/types';

interface GroupMeta { key: string; label: string; }
interface Bucket { label: string; value: number | null; numerator?: number; denominator?: number; }
interface ReportRow {
    code: string; key: string; label: string; group: string; level: string;
    type: 'count' | 'percent' | 'sum'; status: string; note: string | null; no_period: boolean;
    total: { value: number | null; numerator?: number; denominator?: number } | null;
    by: Record<string, Bucket[]>;
}

const props = defineProps<{
    appTitle: string;
    registry: { groups: GroupMeta[]; methods?: Record<string, string>; method_common?: string; indicators?: { key: string; variables: string | null }[] };
}>();

const expanded = ref<Set<string>>(new Set());
function toggleMethod(code: string): void {
    const next = new Set(expanded.value);
    if (next.has(code)) next.delete(code); else next.add(code);
    expanded.value = next;
}
function methodFor(row: ReportRow): string | null {
    return props.registry.methods?.[row.key] ?? null;
}
function variablesFor(row: ReportRow): string | null {
    return props.registry.indicators?.find((i) => i.key === row.key)?.variables ?? null;
}
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'AHP overview', href: '/data6' },
    { title: 'Reports', href: '/data6/reports' },
];

// ---- period selection ------------------------------------------------------
function fmt(d: Date): string { return d.toISOString().slice(0, 10); }
function monthRange(offset: number): { from: string; to: string; label: string } {
    const now = new Date();
    const first = new Date(now.getFullYear(), now.getMonth() + offset, 1);
    const last = new Date(now.getFullYear(), now.getMonth() + offset + 1, 0);
    return { from: fmt(first), to: fmt(last), label: first.toLocaleString('en', { month: 'long', year: 'numeric' }) };
}
function quarterRange(offset: number): { from: string; to: string; label: string } {
    const now = new Date();
    const q = Math.floor(now.getMonth() / 3) + offset;
    const first = new Date(now.getFullYear(), q * 3, 1);
    const last = new Date(now.getFullYear(), q * 3 + 3, 0);
    return { from: fmt(first), to: fmt(last), label: `Q${((q % 4) + 4) % 4 + 1} ${first.getFullYear()}` };
}

const presets = [
    { key: 'this_month', ...monthRange(0) },
    { key: 'last_month', ...monthRange(-1) },
    { key: 'this_quarter', ...quarterRange(0) },
    { key: 'last_quarter', ...quarterRange(-1) },
];
const activePreset = ref('this_quarter');
const from = ref(quarterRange(0).from);
const to = ref(quarterRange(0).to);

function applyPreset(key: string): void {
    activePreset.value = key;
    const preset = presets.find((p) => p.key === key);
    if (preset) { from.value = preset.from; to.value = preset.to; load(); }
}

// ---- data --------------------------------------------------------------
const loading = ref(false);
const error = ref('');
const report = ref<ReportRow[]>([]);
const detailDim = ref<'facility' | 'district'>('facility');

async function load(): Promise<void> {
    loading.value = true;
    error.value = '';
    try {
        const response = await fetch(`/api/data6/reports?from=${from.value}&to=${to.value}`, { headers: { Accept: 'application/json' } });
        if (!response.ok) throw new Error(`Request failed (${response.status})`);
        report.value = (await response.json()).report ?? [];
    } catch {
        error.value = 'Could not compute the report. The first run for a period can take a minute or two — try Refresh.';
    } finally {
        loading.value = false;
    }
}
onMounted(load);

const excelUrl = computed(() => `/api/data6/reports/excel?from=${from.value}&to=${to.value}`);
const groups = computed(() =>
    props.registry.groups
        .map((g) => ({ ...g, rows: report.value.filter((r) => r.group === g.key) }))
        .filter((g) => g.rows.length > 0),
);

function bucket(row: ReportRow, dim: string, label: string): number | string {
    const b = row.by?.[dim]?.find((x) => x.label === label);
    if (!b || b.value === null || b.value === undefined) return '—';
    return row.type === 'percent' ? `${b.value}%` : b.value.toLocaleString();
}
function totalText(row: ReportRow): string {
    if (!row.total || row.total.value === null || row.total.value === undefined) return '—';
    return row.type === 'percent' ? `${row.total.value}%` : row.total.value.toLocaleString();
}
const gridLabels = computed(() => {
    const labels = new Set<string>();
    for (const row of report.value) for (const b of row.by?.[detailDim.value] ?? []) labels.add(b.label);
    return [...labels].sort();
});

const statusBadges: Record<string, { label: string; cls: string }> = {
    proxy: { label: 'Proxy', cls: 'bg-[#f7efdd] text-[#8f6115]' },
    blocked: { label: 'Not computable', cls: 'bg-[#f0efec] text-[#898781]' },
};
</script>

<template>
    <Head title="AHP Reports" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-[#f5f3ee] text-[#173b3b]">
            <div class="mx-auto max-w-[1500px] px-5 py-7 sm:px-8 lg:px-10">

                <header class="flex flex-col justify-between gap-4 border-b border-[#d9ded7] pb-6 lg:flex-row lg:items-end">
                    <div>
                        <div class="mb-3 flex items-center gap-3 text-[11px] font-bold uppercase tracking-[0.22em] text-[#e2644b]">
                            <span class="h-2 w-2 rounded-full bg-[#e2644b]" />{{ appTitle }}
                        </div>
                        <h1 class="font-serif text-4xl leading-tight tracking-tight">M&amp;E indicator report</h1>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-[#60716d]">
                            All 45 AHP indicators for one reporting period, disaggregated by age band, sex, facility
                            and district — matching the kpq indicator matrix. Download the Excel workbook for submission.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a :href="excelUrl" class="inline-flex items-center gap-2 rounded-full bg-[#173b3b] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#285655]">
                            <FileSpreadsheet class="size-4" />Download Excel
                        </a>
                        <button class="rounded-full border border-[#bdc9c3] p-2.5 text-[#3c605b] transition hover:bg-white" title="Refresh" @click="load">
                            <RefreshCw class="size-4" :class="loading ? 'animate-spin' : ''" />
                        </button>
                    </div>
                </header>

                <!-- Period -->
                <section class="mt-5 flex flex-wrap items-end gap-3">
                    <div class="flex flex-wrap gap-1 rounded-full border border-[#cbd3cd] bg-white p-1">
                        <button v-for="preset in presets" :key="preset.key"
                            class="rounded-full px-3 py-1.5 text-xs font-bold transition"
                            :class="activePreset === preset.key ? 'bg-[#173b3b] text-white' : 'text-[#55706a] hover:bg-[#eef0eb]'"
                            @click="applyPreset(preset.key)">{{ preset.label }}</button>
                        <button class="rounded-full px-3 py-1.5 text-xs font-bold transition"
                            :class="activePreset === 'custom' ? 'bg-[#173b3b] text-white' : 'text-[#55706a] hover:bg-[#eef0eb]'"
                            @click="activePreset = 'custom'">Custom</button>
                    </div>
                    <template v-if="activePreset === 'custom'">
                        <label class="text-xs text-[#55706a]">From
                            <input v-model="from" type="date" class="ml-1 border border-[#cbd3cd] bg-white px-2 py-1.5 text-xs" @change="load" />
                        </label>
                        <label class="text-xs text-[#55706a]">To
                            <input v-model="to" type="date" class="ml-1 border border-[#cbd3cd] bg-white px-2 py-1.5 text-xs" @change="load" />
                        </label>
                    </template>
                    <span class="text-xs font-semibold text-[#7b8984]">{{ from }} → {{ to }}</span>
                </section>

                <details v-if="registry.method_common" class="mt-4 border border-[#d9ded7] bg-[#fcfcfb] px-4 py-3">
                    <summary class="flex cursor-pointer items-center gap-2 text-xs font-bold text-[#244847]">
                        <HelpCircle class="size-4 text-[#e2644b]" />How this report is calculated — rules that apply to every indicator
                    </summary>
                    <p class="mt-2 max-w-4xl text-xs leading-5 text-[#60716d]">{{ registry.method_common }}</p>
                    <p class="mt-1 text-xs text-[#7b8984]">Each indicator row has its own <strong>“How we calculated this”</strong> toggle showing the exact fields and rule used.</p>
                </details>

                <div v-if="error" class="mt-5 flex items-center gap-2 bg-[#fff1ed] px-4 py-3 text-sm text-[#b74f3d]">
                    <CircleAlert class="size-4 shrink-0" />{{ error }}
                </div>
                <div v-else-if="loading" class="mt-8 py-16 text-center text-sm text-[#788681]">
                    Computing the disaggregated report for {{ from }} → {{ to }}… first run for a period takes a minute.
                </div>

                <template v-else-if="report.length">
                    <!-- Summary tables by group -->
                    <section v-for="group in groups" :key="group.key" class="mt-7">
                        <h2 class="mb-2 font-serif text-xl text-[#173b3b]">{{ group.label }}</h2>
                        <div class="overflow-x-auto border border-[#d9ded7] bg-[#fcfcfb]">
                            <table class="w-full min-w-[860px] text-left text-xs" style="font-variant-numeric: tabular-nums">
                                <thead class="border-b border-[#d9ded7] bg-[#f0f2ec] text-[10px] font-bold uppercase tracking-wider text-[#5a6f69]">
                                    <tr>
                                        <th class="px-3 py-2.5">Code</th>
                                        <th class="px-3 py-2.5">Indicator</th>
                                        <th class="px-3 py-2.5 text-right">Value</th>
                                        <th class="px-3 py-2.5 text-right">Num / Den</th>
                                        <th class="px-3 py-2.5 text-right">10–14</th>
                                        <th class="px-3 py-2.5 text-right">15–19</th>
                                        <th class="px-3 py-2.5 text-right">Male</th>
                                        <th class="px-3 py-2.5 text-right">Female</th>
                                        <th class="px-3 py-2.5"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-for="row in group.rows" :key="row.code">
                                    <tr class="border-b border-[#eef0eb] text-[#365652]" :class="expanded.has(row.code) ? 'border-b-0 bg-[#f7f8f4]' : ''">
                                        <td class="px-3 py-2 font-mono text-[10.5px] text-[#82908a]">{{ row.code }}</td>
                                        <td class="max-w-[340px] px-3 py-2 font-semibold text-[#244847]">
                                            <button v-if="methodFor(row)" class="group flex items-start gap-1 text-left" :aria-expanded="expanded.has(row.code)" @click="toggleMethod(row.code)">
                                                <span class="underline decoration-[#cbd3cd] decoration-dotted underline-offset-2 group-hover:decoration-[#e2644b]">{{ row.label }}</span>
                                                <ChevronDown class="mt-0.5 size-3 shrink-0 text-[#a6b1aa] transition" :class="expanded.has(row.code) ? 'rotate-180' : ''" />
                                            </button>
                                            <template v-else>{{ row.label }}</template>
                                            <p v-if="row.note" class="mt-0.5 flex items-start gap-1 text-[10px] font-normal leading-3.5 text-[#a87524]">
                                                <AlertTriangle class="mt-px size-2.5 shrink-0" />{{ row.note }}
                                            </p>
                                        </td>
                                        <td class="px-3 py-2 text-right text-sm font-bold text-[#0b2c2c]">{{ totalText(row) }}</td>
                                        <td class="px-3 py-2 text-right text-[#788681]">
                                            {{ row.total?.numerator !== undefined ? `${row.total.numerator} / ${row.total.denominator}` : '' }}
                                        </td>
                                        <td class="px-3 py-2 text-right">{{ bucket(row, 'age_band', '10-14') }}</td>
                                        <td class="px-3 py-2 text-right">{{ bucket(row, 'age_band', '15-19') }}</td>
                                        <td class="px-3 py-2 text-right">{{ bucket(row, 'sex', 'Male') }}</td>
                                        <td class="px-3 py-2 text-right">{{ bucket(row, 'sex', 'Female') }}</td>
                                        <td class="px-3 py-2">
                                            <span v-if="statusBadges[row.status]" class="rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide" :class="statusBadges[row.status].cls">
                                                {{ statusBadges[row.status].label }}
                                            </span>
                                            <span v-if="row.no_period" class="ml-1 rounded-full bg-[#e7eef4] px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide text-[#31577a]" title="Instrument has no date field — value is all-time">All-time</span>
                                        </td>
                                    </tr>
                                    <tr v-if="expanded.has(row.code)" class="border-b border-[#eef0eb] bg-[#f7f8f4]">
                                        <td></td>
                                        <td colspan="8" class="px-3 pb-3 pt-0">
                                            <div class="border-l-2 border-[#e2644b] py-1 pl-3">
                                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#82908a]">How we calculated this</p>
                                                <p class="mt-1 max-w-4xl text-[11.5px] leading-5 text-[#52655f]">{{ methodFor(row) }}</p>
                                                <p v-if="variablesFor(row)" class="mt-1.5 text-[10.5px] text-[#7b8984]">
                                                    <span class="font-bold uppercase tracking-wider">Fields:</span>
                                                    <span class="ml-1 font-mono">{{ variablesFor(row) }}</span>
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <!-- Facility / district grid -->
                    <section class="mt-9">
                        <div class="mb-2 flex items-center justify-between">
                            <h2 class="font-serif text-xl text-[#173b3b]">Indicator × {{ detailDim }}</h2>
                            <div class="flex gap-1 rounded-full border border-[#cbd3cd] bg-white p-1">
                                <button v-for="dim in (['facility', 'district'] as const)" :key="dim"
                                    class="rounded-full px-3 py-1.5 text-xs font-bold capitalize transition"
                                    :class="detailDim === dim ? 'bg-[#173b3b] text-white' : 'text-[#55706a] hover:bg-[#eef0eb]'"
                                    @click="detailDim = dim">{{ dim }}</button>
                            </div>
                        </div>
                        <div class="overflow-x-auto border border-[#d9ded7] bg-[#fcfcfb]">
                            <table class="w-full text-left text-xs" style="font-variant-numeric: tabular-nums">
                                <thead class="border-b border-[#d9ded7] bg-[#f0f2ec] text-[10px] font-bold uppercase tracking-wider text-[#5a6f69]">
                                    <tr>
                                        <th class="sticky left-0 bg-[#f0f2ec] px-3 py-2.5">Indicator</th>
                                        <th v-for="label in gridLabels" :key="label" class="px-3 py-2.5 text-right">{{ label }}</th>
                                        <th class="px-3 py-2.5 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in report" :key="row.code" class="border-b border-[#eef0eb] text-[#365652] last:border-0">
                                        <td class="sticky left-0 max-w-[280px] bg-[#fcfcfb] px-3 py-2 font-semibold text-[#244847]">
                                            <span class="mr-1 font-mono text-[10px] font-normal text-[#82908a]">{{ row.code }}</span>{{ row.label }}
                                        </td>
                                        <td v-for="label in gridLabels" :key="label" class="px-3 py-2 text-right">{{ bucket(row, detailDim, label) }}</td>
                                        <td class="px-3 py-2 text-right font-bold text-[#0b2c2c]">{{ totalText(row) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <p class="mt-5 flex items-start gap-2 text-xs leading-5 text-[#7d8b85]">
                        <Download class="mt-0.5 size-3.5 shrink-0" />
                        The Excel download contains these tables as separate sheets (Summary, By facility, By district)
                        plus a Definitions sheet documenting the exact rule applied for every indicator, including proxies.
                    </p>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
