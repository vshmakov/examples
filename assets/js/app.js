import 'bootstrap';
import $ from 'jquery';
import {needHideHomeworkBlock, hideHomeworkBlock} from './homeworkBlock';

const hideHomeworkButton = $('.hide-homework');
hideHomeworkButton.click(hideHomeworkBlock);

if (needHideHomeworkBlock()) {
    hideHomeworkBlock();
}
