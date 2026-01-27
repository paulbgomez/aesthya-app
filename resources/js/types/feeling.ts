export interface FeelingType {
    id: number;
    name: string;
    description: string;
    color: FeelingColor;
    energyAxis: EnergyAxis;
    pleasantnessAxis: PleasantnessAxis;
}

export enum FeelingColor {
    Red = 'red',
    Blue = 'blue',
    Green = 'green',
    Yellow = 'yellow',
}

export enum EnergyAxis {
    High = 'high',
    Low = 'low',
}

export enum PleasantnessAxis {
    Pleasant = 'pleasant',
    Unpleasant = 'unpleasant',
}
