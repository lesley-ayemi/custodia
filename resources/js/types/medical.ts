export type MedicalAppointmentStatus = 'scheduled' | 'completed' | 'cancelled';
export type PrescriptionStatus = 'active' | 'discontinued';
export type MedicalAlertSeverity = 'low' | 'medium' | 'high';

export interface MedicalRecord {
    id: number;
    condition: string;
    notes: string | null;
    recorded_by?: string;
    recorded_at: string;
}

export interface MedicalAppointment {
    id: number;
    prisoner_id: number;
    prisoner_name?: string;
    appointment_type: string;
    provider: string | null;
    location: string;
    scheduled_at: string;
    status: MedicalAppointmentStatus;
    notes: string | null;
    scheduled_by?: string;
}

export interface Prescription {
    id: number;
    medication_name: string;
    dosage: string;
    frequency: string;
    administration_time: string | null;
    start_date: string;
    end_date: string | null;
    status: PrescriptionStatus;
    notes: string | null;
    prescribed_by?: string;
}

export interface MedicalAlert {
    id: number;
    prisoner_id: number;
    message: string;
    severity: MedicalAlertSeverity;
    active: boolean;
    created_by?: string;
    created_at: string;
}

export interface MedicalRecordFormData {
    condition: string;
    notes: string;
}

export interface MedicalAppointmentFormData {
    appointment_type: string;
    provider: string;
    location: string;
    scheduled_at: string;
    notes: string;
}

export interface PrescriptionFormData {
    medication_name: string;
    dosage: string;
    frequency: string;
    administration_time: string;
    start_date: string;
    notes: string;
}

export interface MedicalAlertFormData {
    message: string;
    severity: MedicalAlertSeverity;
}
