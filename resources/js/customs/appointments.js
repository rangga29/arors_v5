import 'flatpickr/dist/flatpickr.js';
import { Indonesian } from "flatpickr/dist/l10n/id.js";

$(document).ready(function () {
    let $scheduleDateInput = $('#appointment-date');
    let minDate = $scheduleDateInput.data('schedule-date-first');
    let maxDate = $scheduleDateInput.data('schedule-date-last');
    let todayDate = $scheduleDateInput.data('today');
    $scheduleDateInput.flatpickr({
        altInput: true,
        altFormat: "j F Y",
        dateFormat: "Y-m-d",
        defaultDate: todayDate,
        locale: Indonesian,
        minDate: minDate,
        maxDate: maxDate
    });
});
