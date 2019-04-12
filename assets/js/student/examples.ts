import '../app';
import {PARAMETERS} from '../constants';
import defaultDefinitions from '../DataTables/defaultDefinitions';
import createLanguageSettings from '../DataTables/createLanguageSettings';
import getTwoNumberDateParts from '../datetime/getTwoNumberDateParts';
import styleRating from '../styleRating';
import createColumnsSettingsByRenderList from '../DataTables/createColumnsSettingsByRenderList'
import standartDateFormatBySeconds from "../datetime/standartDateFormatBySeconds";

function agoDateFormat(time: number): string {
    let timeAgo = Math.floor(new Date(new Date().getTime() - time * 1000).getTime() / 1000);

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
    const AgoString = Object.keys(AgeData).reverse().reduce((agoString: string, age: string, key: number): string => {
        const AgeLength = AgeData[age]['length'];
        const AgeCount = GetAgeCount(timeAgo, AgeLength);
        timeAgo -= AgeCount * AgeLength;

        if (0 === AgeCount || 2 === notNullPartsCount) {
            return agoString;
        }

        notNullPartsCount++;

        return `${agoString} ${AgeCount} ${AgeData[age]['name']}`;
    }, '');

    if (0 === AgoString.length) {
        return 'Сейчас';
    }

    return `${AgoString} назад`;
}

defaultDefinitions('table', {
    serverSide: true,
    ajax: PARAMETERS.getExamplesUrl,
    language: createLanguageSettings({from: 'примеров'}),
    columns: createColumnsSettingsByRenderList([
        (data): string => data.number,
        (data): string => data.string,
        (data): string => data.answer,
        (data): string => data.isRight ? 'Да' : 'Нет',
        (data): string => data.solvingTime,
        (data): string => agoDateFormat(data.solvedAt),
        (data): string => `<a href="/attempt/${data.attempt.id}/show/">${data.attempt.title}</a>`,
        (data): string => `<a href="/attempt/${data.attempt.id}/settings/">${data.attempt.settings.description}</a>`,
    ]),
});
