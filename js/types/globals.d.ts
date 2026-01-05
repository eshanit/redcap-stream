import type { route as routeFn } from 'ziggy-js';
import type { SharedData } from './index';

declare global {
    const route: typeof routeFn;
}

declare module '@inertiajs/vue3' {
    interface PageProps extends SharedData {}
}
