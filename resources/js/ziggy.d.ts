import { Config as ZiggyConfig } from 'ziggy-js';

export interface Ziggy {
    default?: ZiggyConfig;
    routes?: ZiggyConfig['routes'];
    url?: string;
    port?: number | null;
}

declare global {
    interface Window {
        Ziggy?: Ziggy;
        route?: (name: string, params?: any, absolute?: boolean) => string;
    }
}

