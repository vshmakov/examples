export default function agoDateFormat(time: number): string {
    let timeAgo = Math.floor(new Date(new Date().getTime() - time * 1000).getTime() / 1000);

    if (15 >= timeAgo) {
        return 'Только что';
    }

    const SECOND = 1;
    const MINUTE = 60 * SECOND;
    const HOUR = 60 * MINUTE;
    const DAY = 24 * HOUR;
    const MONGTH = 30 * DAY;
    const YEAR = 365 * DAY;

    const GetAgeCount = (time: number, ageLength: number): number => Math.floor(time / ageLength);

    const AgeData = {
        second: {length: SECOND, name: 'сек'},
        minute: {length: MINUTE, name: 'мин'},
        hour: {length: HOUR, name: 'ч'},
        day: {length: DAY, name: 'дн'},
        month: {length: MONGTH, name: 'мес'},
        year: {length: YEAR, name: 'г'},
    };
    let notNullPartsCount = 0;

    return Object.keys(AgeData).reverse().reduce((agoString: string, age: string, key: number): string => {
        const AgeLength = AgeData[age]['length'];
        const AgeCount = GetAgeCount(timeAgo, AgeLength);
        timeAgo -= AgeCount * AgeLength;

        if (0 === AgeCount || 2 === notNullPartsCount) {
            return agoString;
        }

        notNullPartsCount++;

        return `${agoString} ${AgeCount} ${AgeData[age]['name']}`;
    }, '') + ' назад';
}