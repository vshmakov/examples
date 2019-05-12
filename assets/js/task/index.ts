import '../app';
import defaultDefinitions from '../DataTables/defaultDefinitions';
import createLanguageSettings from '../DataTables/createLanguageSettings';

defaultDefinitions('.table-task', {
    searching: true,
    language: createLanguageSettings({from: 'заданий'}),
});
