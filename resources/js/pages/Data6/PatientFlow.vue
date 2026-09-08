<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Activity, ArrowLeft, Baby, CalendarDays, CircleAlert, ClipboardList,
    Download, GitMerge, HeartPulse, HelpCircle, RefreshCw, Search, ShieldCheck, Users,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { type BreadcrumbItem } from '@/types';

type Role = 'registration' | 'baseline' | 'follow_up' | null;
interface TimelineEntry { instrument: string; family: string; role: Role; subject: string | null; date: string | null; instance: number; project_id: number; }
interface LifetimeFlag { instrument: string; family: string; projects: number[]; }
interface Patient {
    record: string;
    demographics: { facility: string | null; district: string | null; sex: string; age: number | null; age_band: string | null };
    projects: number[];
    timeline: TimelineEntry[];
    lifetime_flags: LifetimeFlag[];
    summary: { first_seen: string | null; last_seen: string | null; families_touched: number; total_encounters: number };
}

const props = defineProps<{ appTitle: string; record: string; patient: Patient | null }>();
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Data6 patient flow', href: '/data6/flow' },
    { title: props.record, href: `/data6/flow/${encodeURIComponent(props.record)}` },
];

const projectLabels: Record<number, { short: string; accent: string }> = {
    76: { short: 'FCH', accent: '#1f7a73' },
    78: { short: 'OI/ART', accent: '#c58a32' },
    79: { short: 'OPD', accent: '#3c6e91' },
};

const familyStyle: Record<string, { icon: typeof Activity; color: string }> = {
    'STI': { icon: Activity, color: '#d75f4a' },
    'Family planning': { icon: Users, color: '#3c6e91' },
    'ANC': { icon: CalendarDays, color: '#1f7a73' },
    'PNC': { icon: Baby, color: '#d48755' },
    'PrEP': { icon: ShieldCheck, color: '#7b5ea7' },
    'OI/ART': { icon: GitMerge, color: '#c58a32' },
    'HIV testing': { icon: Search, color: '#456b55' },
    'Peer support': { icon: Users, color: '#3c6e91' },
    'Outpatient': { icon: Activity, color: '#3c6e91' },
    'Mental health': { icon: HeartPulse, color: '#b06b45' },
    'Health education': { icon: ClipboardList, color: '#3c6e91' },
    'Counselling': { icon: ClipboardList, color: '#7b5ea7' },
};
function styleFor(family: string) { return familyStyle[family] ?? { icon: Activity, color: '#7b8984' }; }

const found = computed(() => props.patient !== null);
function refresh(): void { router.reload(); }

// ANC/PNC/PrEP/OI-ART have a real registration(+baseline)/follow-up
// structure - group their entries so that's what the page shows, instead
// of one flat list where a registration and a follow-up read identically.
// Everything else has no such structure (role is null on every entry), so
// it just gets its own per-service card with a flat chronological list.
interface FamilyGroup {
    family: string;
    hasStructure: boolean;
    registrationEntries: TimelineEntry[];
    followUpEntries: TimelineEntry[];
    plainEntries: TimelineEntry[];
    earliest: string | null;
}
const roleOrder: Record<string, number> = { registration: 0, baseline: 1 };
function byDate(a: TimelineEntry, b: TimelineEntry): number { return (a.date ?? '9999-99-99').localeCompare(b.date ?? '9999-99-99'); }

const familyGroups = computed<FamilyGroup[]>(() => {
    if (!props.patient) return [];
    const byFamily = new Map<string, TimelineEntry[]>();
    for (const e of props.patient.timeline) {
        if (!byFamily.has(e.family)) byFamily.set(e.family, []);
        byFamily.get(e.family)!.push(e);
    }
    return [...byFamily.entries()].map(([family, entries]) => {
        const hasStructure = entries.some((e) => e.role !== null);
        const dated = entries.map((e) => e.date).filter((d): d is string => d !== null);

        return {
            family,
            hasStructure,
            registrationEntries: entries.filter((e) => e.role === 'registration' || e.role === 'baseline')
                .sort((a, b) => (roleOrder[a.role ?? ''] ?? 9) - (roleOrder[b.role ?? ''] ?? 9) || byDate(a, b)),
            followUpEntries: entries.filter((e) => e.role === 'follow_up').sort(byDate),
            plainEntries: entries.filter((e) => e.role === null).sort(byDate),
            earliest: dated.length ? dated.reduce((a, b) => (a < b ? a : b)) : null,
        };
    }).sort((a, b) => {
        if (a.earliest === null) return b.earliest === null ? 0 : 1;
        if (b.earliest === null) return -1;

        return a.earliest.localeCompare(b.earliest);
    });
});
function roleLabel(role: Role): string { return role === 'baseline' ? 'Baseline' : 'Registered'; }

// "Download PDF" = browser print (same mechanism as the other Data6 pages).
// A long-standing OI/ART patient can have years of follow-up rows, so each
// family card doesn't get whole-card break-inside-avoid (could force a huge
// card to overflow a page) - only the header (glued to what follows) and
// each individual row get it, so a long history can span pages naturally.
const generatedOn = new Date().toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
function downloadPdf(): void {
    window.print();
}
</script>

<template>
    <Head :title="`Patient ${record}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-[#f5f3ee] text-[#173b3b] print:bg-white print:text-black">
            <div class="mx-auto max-w-[1100px] px-5 py-7 sm:px-8 lg:px-10 print:max-w-none print:px-0 print:py-0">

                <Link href="/data6/flow" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#55706a] transition hover:text-[#173b3b] print:hidden">
                    <ArrowLeft class="size-3.5" />All patient flow
                </Link>

                <header class="mt-3 flex items-start justify-between gap-4 border-b border-[#d9ded7] pb-6 print:break-inside-avoid">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#e2644b]">Patient flow</p>
                        <h1 class="mt-2 max-w-3xl break-all font-serif text-3xl leading-tight tracking-tight sm:text-4xl">{{ record }}</h1>
                        <p class="mt-2 hidden text-xs text-[#788681] print:block">Report generated {{ generatedOn }}</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-2 print:hidden">
                        <button class="inline-flex items-center gap-2 rounded-full border border-[#bdc9c3] px-4 py-2.5 text-xs font-bold text-[#3c605b] transition hover:bg-white" title="Opens the print dialog — choose &quot;Save as PDF&quot; as the destination" @click="downloadPdf">
                            <Download class="size-4" />Download PDF
                        </button>
                        <button class="rounded-full border border-[#bdc9c3] p-2.5 text-[#3c605b] transition hover:bg-white" title="Refresh" @click="refresh">
                            <RefreshCw class="size-4" />
                        </button>
                    </div>
                </header>

                <div v-if="!found" class="mt-6 flex items-center gap-3 border border-dashed border-[#e3b3a8] bg-[#fff1ed] px-5 py-6 text-sm text-[#b74f3d]">
                    <CircleAlert class="size-5 shrink-0" />
                    <div>
                        <p class="font-bold">No record found for "{{ record }}"</p>
                        <p class="mt-1 text-xs leading-5 text-[#c07161]">Check the record ID and try again — search is exact-match against the record identifier used across FCH, OI/ART and OPD.</p>
                    </div>
                </div>

                <template v-else-if="patient">
                    <!-- Summary card -->
                    <section class="mt-6 border border-[#d9ded7] bg-[#fcfcfb] p-6 print:break-inside-avoid">
                        <div class="flex flex-wrap items-start justify-between gap-6">
                            <div class="grid grid-cols-2 gap-x-8 gap-y-3 text-sm sm:grid-cols-4">
                                <div><p class="text-[10px] font-bold uppercase tracking-wider text-[#82908a]">Facility</p><p class="mt-0.5 font-semibold text-[#173b3b]">{{ patient.demographics.facility ?? '—' }}</p></div>
                                <div><p class="text-[10px] font-bold uppercase tracking-wider text-[#82908a]">District</p><p class="mt-0.5 font-semibold text-[#173b3b]">{{ patient.demographics.district ?? '—' }}</p></div>
                                <div><p class="text-[10px] font-bold uppercase tracking-wider text-[#82908a]">Sex</p><p class="mt-0.5 font-semibold text-[#173b3b]">{{ patient.demographics.sex }}</p></div>
                                <div><p class="text-[10px] font-bold uppercase tracking-wider text-[#82908a]">Age</p><p class="mt-0.5 font-semibold text-[#173b3b]">{{ patient.demographics.age ?? '—' }}<span v-if="patient.demographics.age_band" class="ml-1 text-xs font-normal text-[#788681]">({{ patient.demographics.age_band }})</span></p></div>
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                <span v-for="p in patient.projects" :key="p" class="rounded-full px-2.5 py-1 text-[11px] font-bold text-white" :style="{ backgroundColor: projectLabels[p]?.accent ?? '#7b8984' }">
                                    {{ projectLabels[p]?.short ?? `Project ${p}` }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-5 grid grid-cols-2 gap-3 border-t border-[#eef0eb] pt-4 sm:grid-cols-4">
                            <div><p class="text-[10px] font-bold uppercase tracking-wider text-[#82908a]">First seen</p><p class="mt-0.5 text-sm font-semibold text-[#173b3b]">{{ patient.summary.first_seen ?? '—' }}</p></div>
                            <div><p class="text-[10px] font-bold uppercase tracking-wider text-[#82908a]">Last seen</p><p class="mt-0.5 text-sm font-semibold text-[#173b3b]">{{ patient.summary.last_seen ?? '—' }}</p></div>
                            <div><p class="text-[10px] font-bold uppercase tracking-wider text-[#82908a]">Services touched</p><p class="mt-0.5 text-sm font-semibold text-[#173b3b]">{{ patient.summary.families_touched }}</p></div>
                            <div><p class="text-[10px] font-bold uppercase tracking-wider text-[#82908a]">Total encounters</p><p class="mt-0.5 text-sm font-semibold text-[#173b3b]">{{ patient.summary.total_encounters }}</p></div>
                        </div>
                    </section>

                    <!-- Service timeline: one card per family, registration/baseline
                         separated from follow-ups for ANC/PNC/PrEP/OI-ART -->
                    <section v-if="familyGroups.length" class="mt-4 space-y-4">
                        <h2 class="text-sm font-bold text-[#244847]">Service timeline</h2>
                        <div v-for="group in familyGroups" :key="group.family" class="border border-[#d9ded7] bg-[#fcfcfb] p-5">
                            <div class="flex items-center gap-3 print:break-after-avoid">
                                <span class="grid size-8 shrink-0 place-items-center rounded-full text-white" :style="{ backgroundColor: styleFor(group.family).color }">
                                    <component :is="styleFor(group.family).icon" class="size-4" />
                                </span>
                                <h3 class="text-sm font-bold text-[#244847]">{{ group.family }}</h3>
                                <span class="ml-auto text-[11px] text-[#82908a]">{{ group.registrationEntries.length + group.followUpEntries.length + group.plainEntries.length }} encounter(s)</span>
                            </div>

                            <template v-if="group.hasStructure">
                                <!-- Registration / baseline: pinned, visually distinct from follow-ups -->
                                <div v-for="r in group.registrationEntries" :key="`${r.instrument}-${r.project_id}`"
                                    class="mt-3 flex items-center gap-3 border-l-2 border-[#1f7a73] bg-[#e7f0e9] px-3 py-2 print:break-inside-avoid">
                                    <span class="shrink-0 rounded-full bg-[#1f7a73] px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white">{{ roleLabel(r.role) }}</span>
                                    <span class="min-w-0 flex-1 truncate text-xs text-[#286057]">{{ r.instrument }}</span>
                                    <span class="shrink-0 rounded-full bg-white px-2 py-0.5 text-[10px] font-bold text-[#59726b]">{{ projectLabels[r.project_id]?.short ?? r.project_id }}</span>
                                    <span class="w-24 shrink-0 text-right text-xs font-semibold text-[#173b3b]" style="font-variant-numeric: tabular-nums">{{ r.date ?? 'no date on this form' }}</span>
                                </div>
                                <p v-if="!group.registrationEntries.length" class="mt-3 flex items-center gap-1.5 text-[11px] text-[#a87524]">
                                    <CircleAlert class="size-3.5 shrink-0" />No registration record found — showing follow-ups only.
                                </p>

                                <ol v-if="group.followUpEntries.length" class="mt-1">
                                    <li v-for="(f, i) in group.followUpEntries" :key="`${f.instrument}-${f.date}-${f.instance}`"
                                        class="flex items-center gap-3 border-b border-[#eef0eb] py-2.5 print:break-inside-avoid" :class="i === 0 ? 'border-t' : ''">
                                        <span class="grid size-6 shrink-0 place-items-center rounded-full bg-[#eef0eb] text-[10px] font-bold text-[#59726b]">{{ i + 1 }}</span>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-semibold text-[#365652]">
                                                Follow-up {{ i + 1 }}<span v-if="f.subject" class="ml-1 font-normal text-[#82908a]">· {{ f.subject }}</span><span v-if="f.instance > 1" class="ml-1 font-normal text-[#82908a]">· instance {{ f.instance }}</span>
                                            </p>
                                        </div>
                                        <span class="shrink-0 rounded-full bg-[#e9eeea] px-2 py-0.5 text-[10px] font-bold text-[#59726b]">{{ projectLabels[f.project_id]?.short ?? f.project_id }}</span>
                                        <span class="w-24 shrink-0 text-right text-xs font-semibold text-[#173b3b]" style="font-variant-numeric: tabular-nums">{{ f.date }}</span>
                                    </li>
                                </ol>
                                <p v-else class="mt-2 py-3 text-center text-xs text-[#898781]">No follow-up visits recorded yet.</p>
                            </template>

                            <!-- No registration/follow-up structure - a flat list of this family's own visits -->
                            <ol v-else class="mt-1">
                                <li v-for="(e, i) in group.plainEntries" :key="`${e.instrument}-${e.date}-${e.instance}`"
                                    class="flex items-center gap-3 border-b border-[#eef0eb] py-2.5 print:break-inside-avoid" :class="i === 0 ? 'border-t' : ''">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs text-[#82908a]">{{ e.instrument }}<span v-if="e.instance > 1"> · visit {{ e.instance }}</span></p>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-[#e9eeea] px-2 py-0.5 text-[10px] font-bold text-[#59726b]">{{ projectLabels[e.project_id]?.short ?? e.project_id }}</span>
                                    <span class="w-24 shrink-0 text-right text-xs font-semibold text-[#173b3b]" style="font-variant-numeric: tabular-nums">{{ e.date }}</span>
                                </li>
                            </ol>
                        </div>
                    </section>
                    <section v-else class="mt-4 border border-[#d9ded7] bg-[#fcfcfb] p-6">
                        <h2 class="text-sm font-bold text-[#244847]">Service timeline</h2>
                        <p class="mt-4 py-6 text-center text-xs text-[#898781]">No dated encounters for this record.</p>
                    </section>

                    <!-- Lifetime flags -->
                    <section v-if="patient.lifetime_flags.length" class="mt-4 border border-[#d9ded7] bg-[#fcfcfb] p-6 print:break-inside-avoid">
                        <h2 class="text-sm font-bold text-[#244847]">Lifetime access flags</h2>
                        <p class="mt-0.5 text-[11px] text-[#788681]">These forms have no date field — access is recorded, not when. A flag can be set at more than one project.</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <span v-for="f in patient.lifetime_flags" :key="f.instrument" class="inline-flex items-center gap-1.5 rounded-full border border-[#cbd3cd] bg-white py-1.5 pl-3 pr-1.5 text-xs font-semibold text-[#3c605b]">
                                <component :is="styleFor(f.family).icon" class="size-3.5" :style="{ color: styleFor(f.family).color }" />{{ f.family }}
                                <span v-for="p in f.projects" :key="p" class="rounded-full px-2 py-0.5 text-[10px] font-bold text-white" :style="{ backgroundColor: projectLabels[p]?.accent ?? '#7b8984' }">{{ projectLabels[p]?.short ?? p }}</span>
                            </span>
                        </div>
                    </section>

                    <!-- How this is built -->
                    <details class="mt-4 border border-[#d9ded7] bg-[#fbfaf7] px-4 py-3">
                        <summary class="flex cursor-pointer items-center gap-2 text-xs font-bold text-[#244847]">
                            <HelpCircle class="size-4 text-[#e2644b]" />How this timeline is built
                        </summary>
                        <div class="mt-3 space-y-2 text-[12px] leading-5 text-[#52655f]">
                            <p><code>record</code> is the shared identifier across the FCH, OI/ART and OPD projects — the same record can appear in more than one project's source data, and this page follows it across all of them without any manual matching step.</p>
                            <p>Each encounter is attributed to its <strong>home project</strong>: ANC and mother-baby forms only exist in FCH, OI/ART care forms only exist in OI/ART, outpatient visits only exist in OPD. Shared instruments (STI, PrEP, HIV testing, peer support) can be entered at any of the three sites, so a duplicate copy of the same visit mirrored into another project is collapsed to one entry here, not counted twice.</p>
                            <p>ANC, PNC, PrEP and OI/ART each start with a one-time registration (OI/ART also has a one-time baseline assessment), then every later visit is a follow-up under it — the timeline shows that structure directly instead of listing a registration and a follow-up as if they were the same kind of thing. PNC follow-ups are also tagged Mother or Baby, since the same "PNC" instrument family covers both. Every other service (STI, HIV testing, family planning, peer support, outpatient) has no registration step — each visit just stands on its own.</p>
                            <p>Mental health, health education and counselling have no visit date on their forms — they record that a client was ever seen for that service, not when, so they're listed separately rather than slotted into the timeline.</p>
                        </div>
                    </details>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
