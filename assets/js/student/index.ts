import * as $ from 'jquery';
import '../app';
import defaultDefinitions from '../DataTables/defaultDefinitions';
import createLanguageSettings from '../DataTables/createLanguageSettings';
import styleRating from '../styleRating';
import agoDateFormat from "../datetime/agoDateFormat";

$('.activity').each((number: number, element): void => {
    const rating = $(element);

    rating.html(styleRating(parseInt(rating.html())));
});

let columnsData = Array(6).fill(null).map(() => ({}));
columnsData[3]['render'] = (startedLastAttemptAt: string): string => {
    if ('-' === startedLastAttemptAt) {
        return '-';
    }

    return agoDateFormat(parseInt(startedLastAttemptAt));
};

defaultDefinitions("table", {
    searching: true,
    language: createLanguageSettings({from: "учеников"}),
    columns: columnsData,
})
;
