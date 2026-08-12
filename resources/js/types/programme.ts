export type ProgrammeCategory = 'education' | 'counselling' | 'vocational_training' | 'substance_misuse' | 'employment_training' | 'life_skills' | 'other';
export type ProgrammeStatus = 'active' | 'inactive';
export type EnrolmentStatus = 'enrolled' | 'completed' | 'withdrawn';

export interface Programme {
    id: number;
    name: string;
    category: ProgrammeCategory;
    description: string | null;
    capacity: number | null;
    status: ProgrammeStatus;
    enrolled_count?: number;
}

export interface ProgrammeAttendance {
    id: number;
    session_date: string;
    attended: boolean;
    notes: string | null;
    recorded_by?: string;
}

export interface ProgrammeEnrolment {
    id: number;
    programme_id: number;
    programme_name?: string;
    prisoner_id: number;
    prisoner_name?: string;
    enrolled_by?: string;
    enrolled_at: string;
    status: EnrolmentStatus;
    completed_at: string | null;
    withdrawal_reason: string | null;
    session_count: number;
    attended_count: number;
    attendance_rate: number | null;
    attendances: ProgrammeAttendance[];
}

export interface ProgrammeFormData {
    name: string;
    category: ProgrammeCategory;
    description: string;
    capacity: number | null;
}
