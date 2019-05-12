import '../app';
import {PARAMETERS} from '../constants';
import defaultDefinitions from '../DataTables/defaultDefinitions';
import createLanguageSettings from '../DataTables/createLanguageSettings';
import createColumnsSettingsByRenderList from '../DataTables/createColumnsSettingsByRenderList'
import agoDateFormat from '../datetime/agoDateFormat';

function defaultIfNull(value: any, valueCallback: () => string = (): string => value): string {
    if (null === value) {
        return '-';
    }

    return valueCallback();
}

function styleBoolean(value: string): string {
    const Color = 'Да' === value ? 'green' : 'red';

    return `<span style="background: ${Color};">${value}</span>`;
}

defaultDefinitions('table', {
    serverSide: true,
    ajax: PARAMETERS.getExamplesUrl,
    language: createLanguageSettings({from: 'примеров'}),
    columns: createColumnsSettingsByRenderList([
        (data): string => data.number,
        (data): string => data.string,
        (data): string => defaultIfNull(data.answer),
        (data): string => styleBoolean(data.isRight ? 'Да' : 'Нет'),
        (data): string => defaultIfNull(data.solvingTime),
        (data): string => agoDateFormat(data.solvedAt),
        (data): string => `<a href="/attempt/${data.attempt.id}/show/">${data.attempt.title}</a>`,
        (data): string => `<a href="/attempt/${data.attempt.id}/settings/">${data.attempt.settings.description}</a>`,
    ]),
})
;
