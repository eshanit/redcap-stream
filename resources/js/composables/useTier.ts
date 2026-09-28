import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { SharedData, UserTier } from '@/types';

const RANK: Record<UserTier, number> = { basic: 1, pro: 2, pro_plus: 3 };

/**
 * Download rights per tier (page access is enforced by the `tier:` route
 * middleware; these gate the buttons):
 *  - Basic:  view only - no downloads of any kind.
 *  - Pro:    `canDownload` - one chart or table at a time (PNG, JPG and that
 *            chart's own CSV).
 *  - Pro+:   everything above plus `canDownloadPdf` (whole-page PDF) and
 *            `canExportAll` (whole-report/page-wide exports: Excel workbooks,
 *            all-indicator CSV, client record-ID lists).
 */
export function useTier() {
    const tier = computed(() => usePage<SharedData>().props.auth.user.tier);
    const isPro = computed(() => RANK[tier.value] >= RANK.pro);
    const isProPlus = computed(() => RANK[tier.value] >= RANK.pro_plus);

    return { tier, isPro, isProPlus, canDownload: isPro, canDownloadPdf: isProPlus, canExportAll: isProPlus };
}
