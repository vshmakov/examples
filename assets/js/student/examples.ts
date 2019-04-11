import '../app';
import {PARAMETERS} from '../constants';
import defaultDefinitions from '../DataTables/defaultDefinitions';
import createLanguageSettings from '../DataTables/createLanguageSettings';
import getTwoNumberDateParts from '../datetime/getTwoNumberDateParts';
import styleRating from '../styleRating';
import createColumnsSettingsByRenderList from '../DataTables/createColumnsSettingsByRenderList'
import standartDateFormatBySeconds from "../datetime/standartDateFormatBySeconds";

function minutesSecondsDateFormatBySeconds(seconds: number): string {
    let dateParts = getTwoNumberDateParts(new Date(seconds * 1000));

    return `${dateParts.minute}:${dateParts.second}`;
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
        (data): string => standartDateFormatBySeconds(data.solvedAt),
        (data): string => `<a href="/attempt/${data.attempt.id}/show/">${data.attempt.title}</a>`,
        (data): string => `<a href="/attempt/${data.attempt.id}/settings/">${data.attempt.settings.description}</a>`,
    ]),
    });
