import $ from 'jquery';
import 'datatables.net-dt';

const DEFAULT_PARAMETERS={};

export default function defaultDefinitions(selector, customParameters) {
    let parameters = DEFAULT_PARAMETERS;

    for (let key in customParameters) {
        parameters[key] = customParameters[key];
    }

    $(selector).DataTable(parameters);

}