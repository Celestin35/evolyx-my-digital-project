declare module 'luxon' {
    export class DateTime {
        static fromISO(text: string): DateTime;
        toFormat(format: string): string;
    }
}
