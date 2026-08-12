export type UserRole = 'admin' | 'officer' | 'supervisor' | 'medical';

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    role: UserRole;
}
