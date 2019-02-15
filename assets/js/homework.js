import  $ from  'jquery'
import 'datatables.net-dt'
import './app'
import {hideHomeworkBlock} from './homeworkBlock'

hideHomeworkBlock();

$("table").DataTable({
    pageLength: 25,
    lengthMenu: [25, 50, 100, 250],
})
;
