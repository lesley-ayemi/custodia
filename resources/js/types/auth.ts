export type UserRole = 'admin' | 'officer' | 'supervisor';

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    role: UserRole;
}
