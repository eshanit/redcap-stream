<script setup lang="ts">
/**
 * Side panel listing the adolescents behind one number on the pathways page
 * (a pathway, same-day combination, where-next cell or flow band), each
 * linking to their timeline on the patient flow page, with a CSV download.
 */
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { useTier } from '@/composables/useTier';
import { CircleAlert, Download, ExternalLink } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface MembersRequest {
    title: string;
    /** Short slug for the CSV file name, e.g. "path_ANC-PNC". */
    slug: string;
    selector: Record<string, string>;
}
interface Member { record: string; facility: string; district: string; sex: string; age_at_entry: number | null; first_visit: string; pathway: string; }

const props = defineProps<{ request: MembersRequest | null; filterQuery: string; fileStem: string; subtitle: string }>();
const emit = defineEmits<{ close: [] }>();
const { isProPlus } = useTier();

const loading = ref(false);
const error = ref('');
const rows = ref<Member[]>([]);

watch(() => props.request, async (req) => {
    rows.value = [];
    error.value = '';
    if (!req) return;
    loading.value = true;
    try {
        const response = await fetch(`/api/data6/pathways/members?${props.filterQuery}&${new URLSearchParams(req.selector).toString()}`, { headers: { Accept: 'application/json' } });
        if (!response.ok) throw new Error(`Request failed (${response.status})`);
        rows.value = (await response.json()).rows;
    } catch {
        error.value = 'Could not load the adolescents - try again.';
    } finally {
        loading.value = false;
    }
});

const open = computed({
    get: () => props.request !== null,
    set: (v) => { if (!v) emit('close'); },
});

// Record IDs contain slashes ("MUS/2026/00058"); the flow route takes the
// whole remainder of the path, so encode each segment, not the slashes.
const timelineHref = (record: string) => `/data6/flow/${record.split('/').map(encodeURIComponent).join('/')}`;

function downloadCsv(): void {
    const escape = (v: string | number | null): string => {
        if (v === null || v === undefined) return '';
        const s = String(v);
        return /[",\n]/.test(s) ? `"${s.replace(/"/g, '""')}"` : s;
    };
    const headers = ['Record', 'Facility', 'District', 'Sex', 'Age at first visit', 'First visit', 'Pathway (date each service was first reached)'];
    const lines = [headers, ...rows.value.map((r) => [r.record, r.facility, r.district, r.sex, r.age_at_entry, r.first_visit, r.pathway])].map((r) => r.map(escape).join(','));
    const url = URL.createObjectURL(new Blob([lines.join('\r\n')], { type: 'text/csv;charset=utf-8;' }));
    const link = document.createElement('a');
    link.href = url;
    link.download = `${props.fileStem}_adolescents_${props.request?.slug ?? 'list'}.csv`;
    link.click();
    URL.revokeObjectURL(url);
}
</script>

<template>
    <Sheet v-model:open="open">
        <SheetContent side="right" class="w-full overflow-y-auto bg-[#fcfcfb] sm:max-w-4xl">
            <SheetHeader class="border-b border-[#d9ded7] pb-4">
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#e2644b]">Adolescents behind this number</p>
                <SheetTitle class="pr-8 font-serif text-2xl font-normal text-[#173b3b]">{{ request?.title }}</SheetTitle>
                <SheetDescription class="text-xs text-[#788681]">{{ subtitle }}</SheetDescription>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <span v-if="!loading && !error" class="text-sm font-semibold text-[#0b2c2c]">{{ rows.length }} adolescent{{ rows.length === 1 ? '' : 's' }}</span>
                    <button v-if="isProPlus && rows.length" class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadCsv">
                        <Download class="size-3" />CSV
                    </button>
                </div>
            </SheetHeader>

            <div class="px-4 pb-6">
                <p v-if="loading" class="py-12 text-center text-xs text-[#788681]">Finding the adolescents…</p>
                <p v-else-if="error" class="flex items-center gap-2 bg-[#fff1ed] px-3 py-2 text-xs text-[#b74f3d]"><CircleAlert class="size-4" />{{ error }}</p>
                <div v-else class="overflow-x-auto">
                    <table class="w-full min-w-[720px] text-xs">
                        <thead class="border-b border-[#d9ded7] text-[10px] font-bold uppercase tracking-wider text-[#82908a]">
                            <tr>
                                <th class="py-2 text-left">Record</th>
                                <th class="py-2 text-left">Facility</th>
                                <th class="py-2 text-left">District</th>
                                <th class="py-2 text-left">Sex</th>
                                <th class="py-2 text-right">Age</th>
                                <th class="py-2 pl-3 text-left">First visit</th>
                                <th class="py-2 pl-3 text-left">Pathway (date each service was first reached)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="r in rows" :key="r.record" class="border-b border-[#eef0eb] align-top">
                                <td class="py-2">
                                    <a :href="timelineHref(r.record)" target="_blank" rel="noopener" class="inline-flex items-center gap-1 font-bold text-[#285655] underline decoration-[#cbd3cd] underline-offset-2 hover:decoration-[#e2644b]"
                                        :title="`Open ${r.record}'s timeline in a new tab`">
                                        {{ r.record }}<ExternalLink class="size-3" />
                                    </a>
                                </td>
                                <td class="py-2 text-[#52514e]">{{ r.facility }}</td>
                                <td class="py-2 text-[#52514e]">{{ r.district }}</td>
                                <td class="py-2 text-[#52514e]">{{ r.sex }}</td>
                                <td class="py-2 text-right text-[#52514e]" style="font-variant-numeric: tabular-nums">{{ r.age_at_entry ?? '—' }}</td>
                                <td class="py-2 pl-3 text-[#52514e]" style="font-variant-numeric: tabular-nums">{{ r.first_visit }}</td>
                                <td class="py-2 pl-3 text-[#244847]">{{ r.pathway }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="mt-3 text-[11px] text-[#898781]">Each record opens its full cross-service timeline on the patient flow page in a new tab, so this list stays open.</p>
                </div>
            </div>
        </SheetContent>
    </Sheet>
</template>
