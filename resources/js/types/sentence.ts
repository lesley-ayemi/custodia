export type SentenceType = 'custodial' | 'suspended' | 'life';
export type LegalStatus = 'convicted' | 'on_appeal' | 'discharged';

export interface Sentence {
    id: number;
    prisoner_id: number;
    prisoner_name?: string;
    case_number: string;
    court: string;
    offence: string;
    sentence_start: string;
    sentence_end: string | null;
    sentence_type: SentenceType;
    parole_eligibility_date: string | null;
    legal_status: LegalStatus;
}

export interface SentenceFormData {
    case_number: string;
    court: string;
    offence: string;
    sentence_start: string;
    sentence_end: string | null;
    sentence_type: SentenceType;
    parole_eligibility_date: string | null;
    legal_status: LegalStatus;
}
