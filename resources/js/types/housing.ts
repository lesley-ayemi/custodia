export interface CellSummary {
    id: number;
    code: string;
    capacity: number;
    occupancy: number;
    available: number;
}

export interface Block {
    id: number;
    name: string;
    cells: CellSummary[];
}

export interface HousingAssignment {
    id: number;
    block_name: string;
    cell_code: string;
    assigned_by: string;
    started_at: string;
    ended_at: string | null;
}
