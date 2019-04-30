import '../app';
import defaultDefinitions from '../DataTables/defaultDefinitions';
import createLanguageSettings from '../DataTables/createLanguageSettings';

defaultDefinitions(".table-profiles", {
    searching: true,
    language: createLanguageSettings({from: "профилей"}),
});
