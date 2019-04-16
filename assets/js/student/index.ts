import * as $ from 'jquery';
import '../app';
import defaultDefinitions from '../DataTables/defaultDefinitions';
import createLanguageSettings from '../DataTables/createLanguageSettings';
import styleRating from '../styleRating';
import agoDateFormat from "../datetime/agoDateFormat";
import  createRenderList from '../DataTables/createRenderList';

$('.activity').each((number: number, element): void => {
    const rating = $(element);

    rating.html(styleRating(parseInt(rating.html())));
});

defaultDefinitions("table", {
    searching: true,
    language: createLanguageSettings({from: "учеников"}),
    columns: createRenderList(6, {
        3: (startedLastAttemptAt: string): string => {
            if ('-' === startedLastAttemptAt) {
                return '-';
            }

            return agoDateFormat(parseInt(startedLastAttemptAt));
        }
    }),
})
;
