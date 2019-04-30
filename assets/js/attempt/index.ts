import '../app';
import {PARAMETERS} from '../constants';
import defaultDefinitions from '../DataTables/defaultDefinitions';
import createLanguageSettings from '../DataTables/createLanguageSettings';
import getTwoNumberDateParts from '../datetime/getTwoNumberDateParts';
import styleRating from '../styleRating';
import createColumnsSettingsByRenderList from '../DataTables/createColumnsSettingsByRenderList'
import agoDateFormat from '../datetime/agoDateFormat';

function minutesSecondsDateFormatBySeconds(seconds: number): string {
    let dateParts = getTwoNumberDateParts(new Date(seconds * 1000));

    return `${dateParts.minute}:${dateParts.second}`;
}

defaultDefinitions('.table-attempts', {
    serverSide: true,
    ajax: PARAMETERS.getAttemptsUrl,
    language: createLanguageSettings({from: 'попыток'}),
    columns: createColumnsSettingsByRenderList([
        (data): string => `<a href="/attempt/${data.id}/show/">${data.title}</a>`,
        (data): string => agoDateFormat(data.createdAt),
        (data): string => `${minutesSecondsDateFormatBySeconds(data.result.solvingTime)} из ${minutesSecondsDateFormatBySeconds(data.settings.duration)}`,
        (data): string => `<a href="/attempt/${data.id}/settings/">${data.settings.description}</a>`,
        (data): string => `${data.result.solvedExamplesCount} из ${data.settings.examplesCount}`,
        (data): string => data.result.errorsCount,
        (data): string => styleRating(data.result.rating),
    ]),
});
