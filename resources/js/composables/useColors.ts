import { FeelingColor } from "@/types/feeling";

export const usePastelColors = (color: FeelingColor) => {
    const hexToPastel = (hex: string): string => {
        const h = hex.replace('#', '');
        const r = parseInt(h.substring(0, 2), 16);
        const g = parseInt(h.substring(2, 4), 16);
        const b = parseInt(h.substring(4, 6), 16);

        // Blend towards white for pastel effect
        const blendFactor = 0.6;
        const pastR = Math.round(r + (255 - r) * blendFactor);
        const pastG = Math.round(g + (255 - g) * blendFactor);
        const pastB = Math.round(b + (255 - b) * blendFactor);

        return `#${pastR.toString(16).padStart(2, '0')}${pastG.toString(16).padStart(2, '0')}${pastB.toString(16).padStart(2, '0')}`;
    };

    // Map FeelingColor enum to vibrant hex colors
    const colorMap: Record<FeelingColor, string> = {
        [FeelingColor.Red]: '#ef4444',
        [FeelingColor.Blue]: '#0ea5e9',
        [FeelingColor.Green]: '#22c55e',
        [FeelingColor.Yellow]: '#eab308',
    };

    const vibrantColor = colorMap[color];
    const pastelColor = hexToPastel(vibrantColor);

    return {
        vibrantColor,
        pastelColor,
        hexToPastel,
    };
};
