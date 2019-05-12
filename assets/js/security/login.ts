import '../app';
import 'jquery-ui/themes/base/accordion.css';
import * as $ from 'jquery';
import 'jquery-ui/ui/widgets/accordion';
import {PARAMETERS} from '../constants';

const OPTIONS = PARAMETERS.isDevelopmentEnvironment ? {active: 1} : {};

$("#accordion").accordion(OPTIONS);