import './bootstrap';
import $ from 'jquery';
import toastr from 'toastr';
// AGREGA ESTAS DOS LÍNEAS:
import * as bootstrap from 'bootstrap'; 
window.bootstrap = bootstrap;

window.$ = window.jQuery = $;
window.toastr = toastr;

toastr.options = {
    "closeButton": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
};