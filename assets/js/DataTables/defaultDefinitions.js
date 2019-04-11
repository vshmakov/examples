import $ from 'jquery';
import 'datatables.net-dt';

//TODO page length list
const DEFAULT_PARAMETERS={
    searching:false,
    ordering:false,
    deferRender:true,
    processing:true,
    pageLength: 50,
    lengthMenu: [25, 50, 100, 250],
};

export default function defaultDefinitions(selector, customParameters) {
    let parameters = DEFAULT_PARAMETERS;

    for (let key in customParameters) {
        parameters[key] = customParameters[key];
    }

    $(selector).DataTable(parameters);

}