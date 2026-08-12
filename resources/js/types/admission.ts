export type AdmissionStatus = 'draft' | 'processing' | 'awaiting_medical' | 'awaiting_housing' | 'completed';
export type SecurityClassification = 'low' | 'medium' | 'high' | 'maximum';

export interface Admission {
    id: number;
    prisoner_id: number;
    prisoner_name?: string;
    admitted_by?: string;
    admission_date: string;
    admission_reason: string;
    legal_authority_reference: string | null;
    initial_assessment_notes: string | null;
    security_classification: SecurityClassification | null;
    status: AdmissionStatus;
    completed_at: string | null;
    has_property?: boolean;
    has_housing?: boolean;
}

export interface StartAdmissionFormData {
    first_name: string;
    last_name: string;
    date_of_birth: string;
    gender: 'male' | 'female';
    expected_release_date: string | null;
    admission_date: string;
    admission_reason: string;
}
