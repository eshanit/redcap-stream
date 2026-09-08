import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { SharedData, UserTier } from '@/types';

const RANK: Record<UserTier, number> = { basic: 1, pro: 2, pro_plus: 3 };

export function useTier() {
    const tier = computed(() => usePage<SharedData>().props.auth.user.tier);
    const isPro = computed(() => RANK[tier.value] >= RANK.pro);
    const isProPlus = computed(() => RANK[tier.value] >= RANK.pro_plus);

    return { tier, isPro, isProPlus, canDownload: isPro, canDownloadPdf: isProPlus };
}
