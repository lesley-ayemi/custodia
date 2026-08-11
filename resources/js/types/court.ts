export type CourtCaseStatus = 'open' | 'adjourned' | 'closed';
export type HearingStatus = 'scheduled' | 'completed' | 'adjourned' | 'cancelled';
export type HearingType = 'arraignment' | 'bail' | 'trial' | 'sentencing' | 'appeal';

export interface LegalRepresentative {
    id: number;
    name: string;
    firm_name: string | null;
    phone: string | null;
    email: string | null;
}

export interface CourtHearing {
    id: number;
    court_case_id: number;
    case_number?: string;
    prisoner_id?: number;
    prisoner_name?: string;
    type: HearingType;
    scheduled_at: string;
    location: string;
    status: HearingStatus;
    outcome: string | null;
    notes: string | null;
}

export interface CourtCase {
    id: number;
    case_number: string;
    prisoner_id: number;
    prisoner_name?: string;
    court_name: string;
    charge: string;
    status: CourtCaseStatus;
    opened_at: string;
    closed_at: string | null;
    legal_representative: LegalRepresentative | null;
    hearings: CourtHearing[];
}

export interface CourtCaseFormData {
    court_name: string;
    charge: string;
    legal_representative_id: number | null;
    opened_at: string;
}

export interface CourtHearingFormData {
    type: HearingType;
    scheduled_at: string;
    location: string;
    notes: string | null;
}
