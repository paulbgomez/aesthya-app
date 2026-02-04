export interface MoodboardType {
    id: number;
    feeling: string;
    userId: number;
    journalId: number;
    generationContext: string | null;
    bookIds: number[];
    musicTrackIds: number[];
    artworkIds: number[];
    createdAt: string;
    updatedAt: string;
    jobStatus: 'pending' | 'processing' | 'completed' | 'failed';
}