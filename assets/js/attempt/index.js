import  '../app';
import  PARAMETERS from  '../constants';
import  defaultDefinitions from  '../DataTables/defaultDefinitions';
import createLanguageSettings from '../DataTables/createLanguageSettings';

defaultDefinitions('table', {
    serverSide: true,
    ajax: PARAMETERS.getAttemptsUrl,
});
