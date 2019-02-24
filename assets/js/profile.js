import $ from 'jquery';
import 'datatables.net-dt';
import './app';
import {PARAMETERS} from './constants';
import createLanguageSettings from './DataTables/createLanguageSettings';

const chooseProfileRadioButtons = $("[type=radio]");
chooseProfileRadioButtons.click((event) => {
    event.stopPropagation();
    chooseProfileRadioButtons.attr("disabled", true);
    location.href = $(event.target).data("href");
});

$("table .actions").click((event) => {
    event.stopPropagation();
});

$("tr").click(() => {
    const targetRadioButton = $(this).find("[type=radio]");

    if (!targetRadioButton.attr("disabled")) {
        targetRadioButton.click();
    }
});

chooseProfileRadioButtons.filter(`[value=${PARAMETERS.current}]`).attr("checked", true);

if (!PARAMETERS.canAppoint) {
    chooseProfileRadioButtons.attr("disabled", true);
}

$("table").DataTable({
    pageLength: 25,
    language: createLanguageSettings({from: "профилей"}),
});
