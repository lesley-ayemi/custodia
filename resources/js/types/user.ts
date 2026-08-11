import type { UserRole } from './auth';

export interface StaffUser {
    id: number;
    name: string;
    email: string;
    role: UserRole;
    created_at: string | null;
}

export interface StaffUserFormData {
    name: string;
    email: string;
    password: string;
    role: UserRole;
}
