export interface PropertyItem {
    id: number;
    prisoner_id: number;
    property_number: string;
    description: string;
    quantity: number;
    storage_location: string;
    notes: string | null;
    received_by?: string;
    received_at: string;
    released_by: string | null;
    released_to: string | null;
    released_at: string | null;
}

export interface PropertyItemDraft {
    description: string;
    quantity: number;
    storage_location: string;
    notes?: string | null;
}
