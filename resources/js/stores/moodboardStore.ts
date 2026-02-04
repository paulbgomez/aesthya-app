import { defineStore } from 'pinia';
import { ref } from 'vue';
import { MoodboardType } from '@/types/moodboard';
import { JobStatus } from '@/types/jobStatus';

export const useMoodboardStore = defineStore('moodboard', () => {
    const activeMoodboard = ref<MoodboardType | null>(null);
    const jobStatus = ref<JobStatus>(JobStatus.Processing);

    const checkStatus = async (moodboardId: number) => {
        const response = await fetch(`/moodboards/${moodboardId}/status`);
        const data = await response.json();
        setJobStatus(data.status as JobStatus);

        return data.status;
    };
    /**
     * Getters
     */
    const getSelectedMoodboard = () => activeMoodboard.value;
    const getJobStatus = () => jobStatus.value;

    /**
     * Setters
     */
    const setSelectedMoodboard = (moodboard: MoodboardType) => {
        activeMoodboard.value = moodboard;
    };

    const setJobStatus = (status: JobStatus) => {
        jobStatus.value = status;
    }

    const clearSelection = () => {
        activeMoodboard.value = null;
    };

    return {
        activeMoodboard,
        jobStatus,
        checkStatus,
        setSelectedMoodboard,
        getSelectedMoodboard,
        getJobStatus,
        setJobStatus,
        clearSelection,
    };
});
