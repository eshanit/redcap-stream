<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Activity, Baby, CalendarDays, Check, ChevronRight, CircleAlert,
    ClipboardList, Database, Filter, GitMerge, HeartPulse, RefreshCw,
    Search, ShieldCheck, Users,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { type BreadcrumbItem } from '@/types';

interface ProjectSummary { project_id: number | null; app_title: string; project_name: string; }
interface ProjectScope { id: number | null; short: string; label: string; accent: string; description: string; }
interface Service { key: string; label: string; prefix: string; icon: typeof Activity; color: string; projects: string[]; stage: string; }
interface ReportRow { facility: string | null; service: string; unique_patients: number; projects_represented: number; }

const props = defineProps<{ project: ProjectSummary; recordCount: number; recordsByProject: Record<string, number>; }>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }, { title: 'Data6 patient flow', href: '/data6' }];

const scopes: ProjectScope[] = [
    { id: null, short: 'All', label: 'All three projects', accent: '#e86d52', description: 'A unified view across FCH, OI/ART and OPD' },
    { id: 76, short: 'FCH', label: 'Family & Child Health', accent: '#1f7a73', description: 'ANC, PNC and family planning services' },
    { id: 78, short: 'OI/ART', label: 'OI/ART', accent: '#c58a32', description: 'Registration, baseline and ART follow-ups' },
    { id: 79, short: 'OPD', label: 'Outpatient', accent: '#3c6e91', description: 'General outpatient encounters' },
];
const services: Service[] = [
    { key: 'demog', label: 'Demographics', prefix: 'demog_', icon: Users, color: '#1f7a73', projects: ['FCH', 'OI/ART', 'OPD'], stage: 'Profile' },
    { key: 'sti', label: 'STI register', prefix: 'sti_', icon: Activity, color: '#d75f4a', projects: ['FCH', 'OI/ART', 'OPD'], stage: 'Visits' },
    { key: 'prep', label: 'PrEP', prefix: 'prepr_ / prep_', icon: ShieldCheck, color: '#7b5ea7', projects: ['FCH', 'OI/ART', 'OPD'], stage: 'Initial + follow-up' },
    { key: 'mh', label: 'Mental health', prefix: 'mh_', icon: HeartPulse, color: '#b06b45', projects: ['FCH', 'OI/ART', 'OPD'], stage: 'Visits' },
    { key: 'support', label: 'Education & support', prefix: 'he_ / couns_ / pls_', icon: ClipboardList, color: '#3c6e91', projects: ['FCH', 'OI/ART', 'OPD'], stage: 'Visits' },
    { key: 'hts', label: 'HIV testing', prefix: 'hts_', icon: Search, color: '#456b55', projects: ['FCH', 'OI/ART', 'OPD'], stage: 'Visits' },
    { key: 'anc', label: 'ANC', prefix: 'ancr_ / anc_', icon: CalendarDays, color: '#1f7a73', projects: ['FCH'], stage: 'Registration + follow-up' },
    { key: 'pnc', label: 'Mother & baby', prefix: 'pncr_ / pncm_ / pncb_', icon: Baby, color: '#d48755', projects: ['FCH'], stage: 'Pair + follow-up' },
    { key: 'art', label: 'OI/ART care', prefix: 'artr_ / artib_ / art_', icon: GitMerge, color: '#c58a32', projects: ['OI/ART'], stage: 'Registration + baseline + follow-up' },
    { key: 'opd', label: 'Outpatient', prefix: 'opd_', icon: Activity, color: '#3c6e91', projects: ['OPD'], stage: 'Visits' },
];

const selectedScope = ref<number | null>(props.project.project_id);
const search = ref('');
const reportRows = ref<ReportRow[]>([]);
const reportLoading = ref(false);
const reportError = ref('');
const reportService = ref('');
const reportFacility = ref('');
const reportFrom = ref('');
const reportTo = ref('');
const activeScope = computed(() => scopes.find((scope) => scope.id === selectedScope.value) ?? scopes[0]);
const visibleServices = computed(() => services.filter((service) => activeScope.value.short === 'All' || service.projects.includes(activeScope.value.short)));
const sourceRecords = computed(() => selectedScope.value === null ? Object.values(props.recordsByProject).reduce((total, count) => total + count, 0) : props.recordsByProject[String(selectedScope.value)] ?? 0);
const filteredSearch = computed(() => search.value.trim());
const reportFacilities = computed(() => [...new Set(reportRows.value.map((row) => row.facility).filter(Boolean))].sort());
const filteredReportRows = computed(() => reportRows.value.filter((row) => (!reportService.value || row.service === reportService.value) && (!reportFacility.value || row.facility === reportFacility.value)));
const timelineHref = computed(() => filteredSearch.value ? `/api/data6/patient/${encodeURIComponent(filteredSearch.value)}/timeline` : '#');
function scopeHref(scope: ProjectScope): string { return scope.id === null ? '/data6' : `/data6/project/${scope.id}`; }
function selectScope(scope: ProjectScope): void { selectedScope.value = scope.id; }

async function loadReport(): Promise<void> {
    reportLoading.value = true;
    reportError.value = '';
    const params = new URLSearchParams();
    if (selectedScope.value !== null) params.set('project_id', String(selectedScope.value));
    if (reportFrom.value) params.set('from', reportFrom.value);
    if (reportTo.value) params.set('to', reportTo.value);

    try {
        const response = await fetch(`/api/data6/report?${params.toString()}`, { headers: { Accept: 'application/json' } });
        if (!response.ok) throw new Error('Report unavailable');
        reportRows.value = (await response.json()).rows;
    } catch {
        reportError.value = 'Run the Data6 synchronization before generating reports.';
        reportRows.value = [];
    } finally {
        reportLoading.value = false;
    }
}

onMounted(loadReport);
</script>

<template>
    <Head title="Data6 patient flow" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-[#f5f3ee] text-[#173b3b]">
            <div class="mx-auto max-w-[1500px] px-5 py-7 sm:px-8 lg:px-10">
                <header class="flex flex-col justify-between gap-6 border-b border-[#d9ded7] pb-7 lg:flex-row lg:items-end">
                    <div>
                        <div class="mb-4 flex items-center gap-3 text-[11px] font-bold uppercase tracking-[0.22em] text-[#e2644b]"><span class="h-2 w-2 rounded-full bg-[#e2644b]" />REDCap data6 / patient flow</div>
                        <h1 class="max-w-3xl font-serif text-4xl leading-[0.98] tracking-tight text-[#173b3b] sm:text-5xl">Follow one patient across every service.</h1>
                        <p class="mt-4 max-w-2xl text-sm leading-6 text-[#60716d]">Three projects, one reviewable patient history. Source records stay traceable while encounters become a shared service timeline.</p>
                    </div>
                    <div class="flex items-center gap-3 text-sm"><span class="inline-flex items-center gap-2 rounded-full border border-[#c7d8ce] bg-[#e7f0e9] px-3 py-2 font-semibold text-[#286057]"><span class="h-2 w-2 rounded-full bg-[#3b9a70]" />Tracking layer ready</span><button class="rounded-full border border-[#bdc9c3] p-2.5 text-[#3c605b] transition hover:bg-white" title="Refresh tracking data"><RefreshCw class="size-4" /></button></div>
                </header>

                <div class="mt-7 grid gap-6 xl:grid-cols-[1fr_310px]">
                    <main>
                        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div class="flex flex-wrap gap-2"><Link v-for="scope in scopes" :key="scope.short" :href="scopeHref(scope)" class="rounded-full border px-4 py-2 text-xs font-bold transition" :class="activeScope.id === scope.id ? 'border-[#173b3b] bg-[#173b3b] text-white' : 'border-[#cbd3cd] bg-transparent text-[#55706a] hover:bg-white'" @click="selectScope(scope)">{{ scope.short }}</Link></div><span class="text-xs font-semibold text-[#7b8984]">{{ activeScope.description }}</span></div>

                        <section class="grid gap-3 sm:grid-cols-3">
                            <div class="border-l-4 border-[#e86d52] bg-white px-5 py-4 shadow-[0_4px_20px_rgba(23,59,59,0.05)]"><div class="flex items-center justify-between text-[#76827e]"><span class="text-xs font-bold uppercase tracking-wider">Source records</span><Database class="size-4" /></div><div class="mt-3 font-serif text-3xl text-[#173b3b]">{{ sourceRecords.toLocaleString() }}</div><div class="mt-1 text-xs text-[#788681]">{{ activeScope.short === 'All' ? 'Across all project sources' : `Project ${activeScope.id}` }}</div></div>
                            <div class="border-l-4 border-[#1f7a73] bg-white px-5 py-4 shadow-[0_4px_20px_rgba(23,59,59,0.05)]"><div class="flex items-center justify-between text-[#76827e]"><span class="text-xs font-bold uppercase tracking-wider">Services mapped</span><Activity class="size-4" /></div><div class="mt-3 font-serif text-3xl text-[#173b3b]">{{ visibleServices.length }}</div><div class="mt-1 text-xs text-[#788681]">Instrument families in scope</div></div>
                            <div class="border-l-4 border-[#c58a32] bg-white px-5 py-4 shadow-[0_4px_20px_rgba(23,59,59,0.05)]"><div class="flex items-center justify-between text-[#76827e]"><span class="text-xs font-bold uppercase tracking-wider">Identity review</span><GitMerge class="size-4" /></div><div class="mt-3 font-serif text-3xl text-[#173b3b]">Manual</div><div class="mt-1 text-xs text-[#788681]">Cross-project links stay reviewable</div></div>
                        </section>

                        <section class="mt-8 border border-[#d9ded7] bg-white p-5"><div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#e2644b]">Program report</p><h2 class="mt-1 font-serif text-2xl text-[#173b3b]">Unique patients by facility and service</h2><p class="mt-1 text-xs text-[#788681]">Patients appearing in multiple projects count once when their source records share a canonical link.</p></div><button class="inline-flex items-center justify-center gap-2 bg-[#173b3b] px-4 py-2.5 text-xs font-bold text-white transition hover:bg-[#285655]" @click="loadReport"><RefreshCw class="size-3.5" :class="reportLoading ? 'animate-spin' : ''" />Refresh report</button></div><div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4"><select v-model="reportService" class="border border-[#cbd3cd] bg-[#fbfaf7] px-3 py-2 text-xs text-[#45645e]"><option value="">All services</option><option v-for="service in visibleServices" :key="service.key" :value="service.label">{{ service.label }}</option></select><select v-model="reportFacility" class="border border-[#cbd3cd] bg-[#fbfaf7] px-3 py-2 text-xs text-[#45645e]"><option value="">All facilities</option><option v-for="facility in reportFacilities" :key="facility" :value="facility">{{ facility }}</option></select><input v-model="reportFrom" type="date" class="border border-[#cbd3cd] bg-[#fbfaf7] px-3 py-2 text-xs text-[#45645e]" /><input v-model="reportTo" type="date" class="border border-[#cbd3cd] bg-[#fbfaf7] px-3 py-2 text-xs text-[#45645e]" /></div><div v-if="reportError" class="mt-4 flex items-center gap-2 bg-[#fff1ed] px-3 py-2 text-xs text-[#b74f3d]"><CircleAlert class="size-4" />{{ reportError }}</div><div v-else-if="reportLoading" class="mt-6 py-8 text-center text-xs text-[#788681]">Loading distinct-patient counts...</div><div v-else-if="filteredReportRows.length" class="mt-5 overflow-x-auto"><table class="w-full min-w-[520px] text-left text-xs"><thead class="border-b border-[#d9ded7] text-[10px] font-bold uppercase tracking-wider text-[#82908a]"><tr><th class="pb-3">Facility</th><th class="pb-3">Service</th><th class="pb-3 text-right">Unique patients</th><th class="pb-3 text-right">Projects</th></tr></thead><tbody><tr v-for="row in filteredReportRows" :key="`${row.facility}-${row.service}`" class="border-b border-[#eef0eb] text-[#365652]"><td class="py-3 font-semibold">{{ row.facility || 'Unknown' }}</td><td class="py-3">{{ row.service }}</td><td class="py-3 text-right font-bold text-[#173b3b]">{{ row.unique_patients.toLocaleString() }}</td><td class="py-3 text-right text-[#788681]">{{ row.projects_represented }}</td></tr></tbody></table></div><div v-else-if="!reportError" class="mt-6 border border-dashed border-[#cbd3cd] px-4 py-8 text-center text-xs text-[#788681]">No report rows match these filters.</div></section>

                        <section class="mt-8"><div class="mb-4 flex items-end justify-between"><div><p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#e2644b]">Service map</p><h2 class="mt-1 font-serif text-2xl text-[#173b3b]">What can appear in the report</h2></div><span class="hidden text-xs text-[#81908a] sm:block">{{ visibleServices.length }} instrument families</span></div><div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3"><article v-for="service in visibleServices" :key="service.key" class="group flex min-h-[126px] flex-col justify-between border border-[#d9ded7] bg-[#fbfaf7] p-4 transition hover:-translate-y-0.5 hover:border-[#9ab4a8] hover:bg-white"><div class="flex items-start justify-between gap-3"><div class="flex items-center gap-3"><span class="grid size-9 place-items-center rounded-full text-white" :style="{ backgroundColor: service.color }"><component :is="service.icon" class="size-4" /></span><div><h3 class="text-sm font-bold text-[#244847]">{{ service.label }}</h3><p class="mt-0.5 font-mono text-[10px] text-[#82908a]">{{ service.prefix }}</p></div></div><ChevronRight class="size-4 text-[#a6b1aa] transition group-hover:translate-x-1 group-hover:text-[#e2644b]" /></div><div class="flex items-center justify-between text-[11px] text-[#788681]"><span>{{ service.stage }}</span><span class="flex gap-1"><span v-for="projectName in service.projects" :key="projectName" class="rounded bg-[#e9eeea] px-1.5 py-0.5 font-semibold text-[#59726b]">{{ projectName }}</span></span></div></article></div></section>
                    </main>

                    <aside class="space-y-5"><section class="border border-[#d9ded7] bg-[#173b3b] p-5 text-[#f6f4ed]"><div class="flex items-center justify-between"><div><p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#e9a18e]">Patient lookup</p><h2 class="mt-1 font-serif text-2xl">Find a history</h2></div><Search class="size-5 text-[#e9a18e]" /></div><div class="mt-5 flex items-center gap-2 border-b border-[#55736c] pb-2"><Search class="size-4 text-[#9cb5ad]" /><input v-model="search" class="w-full border-0 bg-transparent text-sm text-white outline-none placeholder:text-[#91aaa2]" placeholder="Canonical patient ID" /></div><div v-if="filteredSearch" class="mt-4 flex items-start gap-2 text-xs leading-5 text-[#d2e2dc]"><CircleAlert class="mt-0.5 size-4 shrink-0 text-[#e9a18e]" />Timeline lookup is ready for the synchronized patient index.</div><div v-else class="mt-4 text-xs leading-5 text-[#abc1b9]">Search uses the canonical patient ID while keeping each project record visible as a source.</div><Link :href="timelineHref" class="mt-5 flex w-full items-center justify-between px-4 py-3 text-xs font-bold transition" :class="filteredSearch ? 'bg-[#e86d52] text-white hover:bg-[#d65d48]' : 'cursor-not-allowed bg-[#315655] text-[#78948c]'" :aria-disabled="!filteredSearch" @click="!filteredSearch && $event.preventDefault()"><span>Open patient timeline</span><ChevronRight class="size-4" /></Link></section>
                        <section class="border border-[#d9ded7] bg-white p-5"><div class="flex items-center gap-2"><Filter class="size-4 text-[#e2644b]" /><h2 class="text-sm font-bold text-[#244847]">Timeline logic</h2></div><div class="mt-5 space-y-4"><div class="flex gap-3"><span class="grid size-6 shrink-0 place-items-center rounded-full bg-[#e7f0e9] text-[#286057]"><Check class="size-3.5" /></span><div><p class="text-xs font-bold text-[#365652]">Initial registration</p><p class="mt-1 text-[11px] leading-4 text-[#7d8b85]">ANC, PrEP, mother-baby and OI/ART start forms.</p></div></div><div class="flex gap-3"><span class="grid size-6 shrink-0 place-items-center rounded-full bg-[#f9e7e1] text-[#c75e49]"><CalendarDays class="size-3.5" /></span><div><p class="text-xs font-bold text-[#365652]">Repeated encounter</p><p class="mt-1 text-[11px] leading-4 text-[#7d8b85]">Instance stays form-specific; dates order the clinical history.</p></div></div><div class="flex gap-3"><span class="grid size-6 shrink-0 place-items-center rounded-full bg-[#f5eddc] text-[#a87524]"><GitMerge class="size-3.5" /></span><div><p class="text-xs font-bold text-[#365652]">Reviewed identity link</p><p class="mt-1 text-[11px] leading-4 text-[#7d8b85]">Records from another project join only with evidence.</p></div></div></div></section><section class="border-t border-[#cfd8d1] pt-4 text-xs leading-5 text-[#7d8b85]"><p class="font-bold text-[#365652]">Source traceability</p><p class="mt-1">Every encounter retains project, record, event, instrument and raw instance context.</p></section></aside>
                </div>
            </div>
        </div>
    </AppLayout>
</template>