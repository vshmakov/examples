import '../app';
import {PARAMETERS} from '../constants';
import defaultDefinitions from '../DataTables/defaultDefinitions';
import createLanguageSettings from '../DataTables/createLanguageSettings';
import getTwoNumberDateParts from '../datetime/getTwoNumberDateParts';

function standartDateFormatBySeconds(seconds: number): string {
    let date = new Date(seconds * 1000);
    let dateParts = getTwoNumberDateParts(date);

    return `${dateParts.day}.${dateParts.month}.${date.getFullYear()} ${dateParts.hour}:${dateParts.minute}:${dateParts.second}`;
}

function minutesSecondsDateFormatBySeconds(seconds: number): string {
    let dateParts  =getTwoNumberDateParts(new Date(seconds * 1000));

    return `${dateParts .minute}:${dateParts .second}`;
}

function styleRating(rating: number): string {
    let color = 'red';

    switch (rating) {
        case  5:
            color = 'green';
            break;
        case            4        :
            color = 'yellow';
            break;
        case            3:
            color = 'orange';
            break;
    }

    return `<span style="background: ${color};">${rating}</span>`;
}

let columnsRender = [
    (data): string => `<a href="/attempt/${data.id}/show/">${data.title}</a>`,
    (data): string => standartDateFormatBySeconds(data.createdAt),
    (data): string => standartDateFormatBySeconds(data.result.finishedAt),
    (data): string => `<a href="/attempt/${data.id}/settings/">${data.settings.description}</a>`,
    (data): string => `${minutesSecondsDateFormatBySeconds(data.result.solvingTime)} из ${minutesSecondsDateFormatBySeconds(data.settings.duration)}`,
    (data): string => `${data.result.solvedExamplesCount} из ${data.settings.examplesCount}`,
    (data): string => data.result.errorsCount,
    (data): string => styleRating(data.result.rating),
];

let columns = columnsRender.map(value => {
    return {
        data: null,
        render: value,
    };
});

defaultDefinitions('table', {
    serverSide: true,
    ajax: PARAMETERS.getAttemptsUrl,
    columns: columns,
    language: createLanguageSettings({from: 'попыток'}),
});
