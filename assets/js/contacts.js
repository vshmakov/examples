import './app';
import $ from 'jquery';

$('#sendEmailLink').click(function (event) {
    event.preventDefault();

    //$('<a href="mailto:support@exmasters.ru"></a>').click();
    location.href = 'mailto:support@exmasters.ru';
});