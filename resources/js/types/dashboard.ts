import type { Incident } from './incident';

export interface DashboardData {
    total_prisoners: number;
    in_custody_prisoners: number;
    occupancy_percent: number;
    open_incidents: number;
    available_beds: number;
    recent_incidents: Incident[];
    block_occupancy: { name: string; percent: number }[];
}
