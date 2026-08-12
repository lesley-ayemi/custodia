export interface CellSummary {
    id: number;
    wing_id?: number;
    wing_name?: string;
    block_id?: number;
    block_name?: string;
    code: string;
    capacity: number;
    occupancy: number;
    available: number;
}

export interface Wing {
    id: number;
    name: string;
    cells: CellSummary[];
}

export interface Block {
    id: number;
    name: string;
    wings: Wing[];
}

export interface HousingAssignment {
    id: number;
    block_name: string;
    wing_name: string;
    cell_code: string;
    assigned_by: string;
    started_at: string;
    ended_at: string | null;
}
