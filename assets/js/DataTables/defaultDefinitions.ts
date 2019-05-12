import * as $ from 'jquery';
import  DEFAULT_PARAMETERS from './defaultParameters';


export default function defaultDefinitions(selector, customParameters) {
    require('datatables.net-dt');
    let parameters = Object.assign({}, DEFAULT_PARAMETERS);

    for (let key in customParameters) {
        parameters[key] = customParameters[key];
    }

    $(selector).DataTable(parameters);
}