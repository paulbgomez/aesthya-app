import { router } from "@inertiajs/vue3";

export const moodboardService = {

    generateMoodboard(feelingId: number): void {
        router.post(
            '/feelings/generate-content',
            { feeling_id: feelingId },
            {
                onError: (errors) => {
                    console.error('Failed to generate moodboard:', errors);
                },
            }
        );
    }
    
}