export type MovementStatus = 'requested' | 'approved' | 'departed' | 'arrived' | 'returned' | 'cancelled';

export interface Movement {
    id: number;
    prisoner_id: number;
    prisoner_name?: string;
    from_location: string;
    to_location: string;
    reason: string;
    requested_by?: string;
    approved_by?: string | null;
    scheduled_at: string;
    departed_at: string | null;
    arrived_at: string | null;
    returned_at: string | null;
    status: MovementStatus;
}

export interface MovementFormData {
    from_location: string;
    to_location: string;
    reason: string;
    scheduled_at: string;
}
