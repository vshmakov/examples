import $ from 'jquery';
import '../app';
import defaultDefinitions from '../DataTables/defaultDefinitions';
import createLanguageSettings from '../DataTables/createLanguageSettings';
import styleRating from '../styleRating';

$('.activity').each((number, element) => {
    const rating = $(element);

    rating.html(styleRating(rating.html()));
});
defaultDefinitions("table", {
    searching: true,
    language: createLanguageSettings({from: "учеников"}),
});
