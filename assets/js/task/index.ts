import '../app';
import defaultDefinitions from '../DataTables/defaultDefinitions';
import createLanguageSettings from '../DataTables/createLanguageSettings';

defaultDefinitions('table', {
    language: createLanguageSettings({from: 'заданий'}),
});

