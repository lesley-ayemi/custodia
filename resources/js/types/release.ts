export type ReleaseReviewStatus = 'in_progress' | 'released' | 'cancelled';
export type ReleaseStepName = 'legal_verification' | 'sentence_verification' | 'property_verification' | 'documentation' | 'supervisor_approval';

export const RELEASE_STEPS: { value: ReleaseStepName; label: string; endpoint: string; gate: 'operational' | 'supervisor' }[] = [
    { value: 'legal_verification', label: 'Legal verification', endpoint: 'legal-verification', gate: 'operational' },
    { value: 'sentence_verification', label: 'Sentence verification', endpoint: 'sentence-verification', gate: 'operational' },
    { value: 'property_verification', label: 'Property verification', endpoint: 'property-verification', gate: 'operational' },
    { value: 'documentation', label: 'Release documentation', endpoint: 'documentation', gate: 'operational' },
    { value: 'supervisor_approval', label: 'Supervisor approval', endpoint: 'supervisor-approval', gate: 'supervisor' },
];

export interface ReleaseReviewStepEntry {
    id: number;
    step: ReleaseStepName;
    completed_by?: string;
    completed_at: string;
    notes: string | null;
}

export interface ReleaseReview {
    id: number;
    prisoner_id: number;
    prisoner_name?: string;
    initiated_by?: string;
    initiated_at: string;
    status: ReleaseReviewStatus;
    released_at: string | null;
    cancelled_at: string | null;
    cancellation_reason: string | null;
    next_step: ReleaseStepName | null;
    has_open_court_cases?: boolean;
    has_unreleased_property?: boolean;
    steps: ReleaseReviewStepEntry[];
}
