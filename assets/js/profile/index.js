import '../app';
import defaultDefinitions from '../DataTables/defaultDefinitions';
import createLanguageSettings from '../DataTables/createLanguageSettings';

defaultDefinitions("table", {
    pageLength: 25,
    language: createLanguageSettings({from: "профилей"}),
});
