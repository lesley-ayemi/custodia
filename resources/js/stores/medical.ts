import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '../services/api';
import type {
    MedicalAlert,
    MedicalAlertFormData,
    MedicalAppointment,
    MedicalAppointmentFormData,
    MedicalRecord,
    MedicalRecordFormData,
    Prescription,
    PrescriptionFormData,
} from '../types/medical';

export const useMedicalStore = defineStore('medical', () => {
    const records = ref<MedicalRecord[]>([]);
    const appointments = ref<MedicalAppointment[]>([]);
    const prescriptions = ref<Prescription[]>([]);
    const alerts = ref<MedicalAlert[]>([]);
    const upcomingAppointments = ref<MedicalAppointment[]>([]);
    const loading = ref(false);

    async function fetchRecords(prisonerId: number): Promise<void> {
        loading.value = true;
        try {
            const response = await api.get<MedicalRecord[]>(`/api/prisoners/${prisonerId}/medical-records`);
            records.value = response.data;
        } finally {
            loading.value = false;
        }
    }

    async function fetchAppointments(prisonerId: number): Promise<void> {
        const response = await api.get<MedicalAppointment[]>(`/api/prisoners/${prisonerId}/medical-appointments`);
        appointments.value = response.data;
    }

    async function fetchPrescriptions(prisonerId: number): Promise<void> {
        const response = await api.get<Prescription[]>(`/api/prisoners/${prisonerId}/prescriptions`);
        prescriptions.value = response.data;
    }

    async function fetchAlerts(prisonerId: number): Promise<void> {
        const response = await api.get<MedicalAlert[]>(`/api/prisoners/${prisonerId}/medical-alerts`);
        alerts.value = response.data;
    }

    async function fetchUpcomingAppointments(): Promise<void> {
        loading.value = true;
        try {
            const response = await api.get<MedicalAppointment[]>('/api/medical-appointments/upcoming');
            upcomingAppointments.value = response.data;
        } finally {
            loading.value = false;
        }
    }

    async function addRecord(prisonerId: number, data: MedicalRecordFormData): Promise<MedicalRecord> {
        const response = await api.post<MedicalRecord>(`/api/prisoners/${prisonerId}/medical-records`, data);
        return response.data;
    }

    async function scheduleAppointment(prisonerId: number, data: MedicalAppointmentFormData): Promise<MedicalAppointment> {
        const response = await api.post<MedicalAppointment>(`/api/prisoners/${prisonerId}/medical-appointments`, data);
        return response.data;
    }

    async function completeAppointment(id: number): Promise<void> {
        await api.post(`/api/medical-appointments/${id}/complete`);
    }

    async function cancelAppointment(id: number): Promise<void> {
        await api.post(`/api/medical-appointments/${id}/cancel`);
    }

    async function prescribe(prisonerId: number, data: PrescriptionFormData): Promise<Prescription> {
        const response = await api.post<Prescription>(`/api/prisoners/${prisonerId}/prescriptions`, data);
        return response.data;
    }

    async function discontinuePrescription(id: number): Promise<void> {
        await api.post(`/api/prescriptions/${id}/discontinue`);
    }

    async function addAlert(prisonerId: number, data: MedicalAlertFormData): Promise<MedicalAlert> {
        const response = await api.post<MedicalAlert>(`/api/prisoners/${prisonerId}/medical-alerts`, data);
        return response.data;
    }

    async function updateAlert(id: number, data: Partial<MedicalAlertFormData>): Promise<MedicalAlert> {
        const response = await api.put<MedicalAlert>(`/api/medical-alerts/${id}`, data);
        return response.data;
    }

    async function resolveAlert(id: number): Promise<void> {
        await api.post(`/api/medical-alerts/${id}/resolve`);
    }

    return {
        records,
        appointments,
        prescriptions,
        alerts,
        upcomingAppointments,
        loading,
        fetchRecords,
        fetchAppointments,
        fetchPrescriptions,
        fetchAlerts,
        fetchUpcomingAppointments,
        addRecord,
        scheduleAppointment,
        completeAppointment,
        cancelAppointment,
        prescribe,
        discontinuePrescription,
        addAlert,
        updateAlert,
        resolveAlert,
    };
});
