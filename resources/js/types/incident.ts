export type IncidentType = 'property_damage' | 'rule_violation' | 'accident' | 'altercation' | 'contraband_found' | 'medical_emergency';
export type IncidentSeverity = 'low' | 'medium' | 'high';
export type IncidentStatus = 'reported' | 'under_review' | 'resolved';

export interface Incident {
    id: number;
    incident_number: string;
    prisoner_id: number;
    prisoner_number: string;
    prisoner_name: string;
    officer_name: string;
    type: IncidentType;
    severity: IncidentSeverity;
    location: string;
    description: string;
    occurred_at: string;
    status: IncidentStatus;
    resolved_by: string | null;
    resolved_at: string | null;
}

export interface IncidentFormData {
    prisoner_id: number | null;
    type: IncidentType;
    severity: IncidentSeverity;
    location: string;
    description: string;
    occurred_at: string;
}
