export type PrisonerStatus = 'in_custody' | 'released' | 'transferred';
export type Gender = 'male' | 'female';

export interface Prisoner {
    id: number;
    prisoner_number: string;
    first_name: string;
    last_name: string;
    full_name: string;
    date_of_birth: string;
    gender: Gender;
    admission_date: string;
    expected_release_date: string | null;
    status: PrisonerStatus;
    photo_path: string | null;
    archived_at: string | null;
}

export interface PaginatedResponse<T> {
    data: T[];
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

export interface PrisonerFormData {
    first_name: string;
    last_name: string;
    date_of_birth: string;
    gender: Gender;
    admission_date: string;
    expected_release_date: string | null;
}
