export type VisitRequestStatus = 'pending' | 'approved' | 'rejected';
export type VisitStatus = 'scheduled' | 'checked_in' | 'completed' | 'cancelled';
export type VisitorIdType = 'passport' | 'driving_licence' | 'national_id' | 'other';

export interface Visitor {
    id: number;
    name: string;
    date_of_birth: string;
    id_type: VisitorIdType;
    id_number: string;
    phone: string;
    email: string | null;
    address: string | null;
    banned_at: string | null;
    ban_reason: string | null;
}

export interface Visit {
    id: number;
    visit_request_id: number;
    prisoner_id: number;
    prisoner_name?: string;
    visitor_id: number;
    visitor_name?: string;
    scheduled_at: string;
    status: VisitStatus;
    checked_in_at: string | null;
    checked_in_by?: string | null;
    checked_out_at: string | null;
    checked_out_by?: string | null;
    notes: string | null;
}

export interface VisitRequest {
    id: number;
    visitor_id: number;
    visitor_name?: string;
    prisoner_id: number;
    prisoner_name?: string;
    relationship: string;
    requested_by?: string;
    requested_visit_date: string;
    status: VisitRequestStatus;
    rejection_reason: string | null;
    visit: Visit | null;
}

export interface VisitorFormData {
    name: string;
    date_of_birth: string;
    id_type: VisitorIdType;
    id_number: string;
    phone: string;
    email?: string;
    address?: string;
}
