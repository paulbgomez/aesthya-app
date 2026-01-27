import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { FeelingType } from '@/types/feeling';

export const useFeelingStore = defineStore('feeling', () => {
    const selectedFeeling = ref<(FeelingType & { id: number }) | null>(null);

    const setSelectedFeeling = (feeling: (FeelingType & { id: number }) | null) => {
        selectedFeeling.value = feeling;
    };

    const getSelectedFeeling = () => selectedFeeling.value;

    const clearSelection = () => {
        selectedFeeling.value = null;
    };

    return {
        selectedFeeling,
        setSelectedFeeling,
        getSelectedFeeling,
        clearSelection,
    };
});
