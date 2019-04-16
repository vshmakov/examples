import '../app';
import defaultDefinitions from '../DataTables/defaultDefinitions';
import createLanguageSettings from '../DataTables/createLanguageSettings';
import  createRenderList from "../DataTables/createRenderList";
import agoDateFormat from "../datetime/agoDateFormat";

defaultDefinitions('.table-info', {
    searching: true,
    language: createLanguageSettings({from: 'учеников'}),
    columns:createRenderList(6, {
        2: (startedLastAttemptAt: string): string => {
            if ('-' === startedLastAttemptAt) {
                return '-';
            }

            return agoDateFormat(parseInt(startedLastAttemptAt));
        }
    })
});

