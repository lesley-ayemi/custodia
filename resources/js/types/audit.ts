export interface AuditLog {
    id: number;
    user_name: string;
    action: string;
    entity_type: string;
    entity_id: number;
    old_values: Record<string, string | null> | null;
    new_values: Record<string, string | null> | null;
    created_at: string;
}
