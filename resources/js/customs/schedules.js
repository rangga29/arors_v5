import 'flatpickr/dist/flatpickr.js';
import { Indonesian } from "flatpickr/dist/l10n/id.js";

$(document).ready(function () {
    let $scheduleDateInput = $('#schedule-date');
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

$(document).on('click', '.cl-quota', function () {
    let scheduleDate = $(this).data('schedule-date');
    let scheduleUcode = $(this).data('schedule-ucode');
    $('#editQuota').attr('action', '/administrator/schedules/' + scheduleDate + '/' + scheduleUcode + '/quota');
    $.ajax({
        url: '/administrator/schedules/' + scheduleUcode + '/quota',
        type: 'GET',
        dataType: 'json',
        beforeSend: function () {
            $('#overlay').show(); // Show spinner
        },
        success: function (scheduleDetail) {
            $('#edit_scd_online_umum').val(scheduleDetail.scd_online_umum);
            $('#edit_scd_online_bpjs').val(scheduleDetail.scd_online_bpjs);
            if (scheduleDetail.scd_umum === 1) {
                $('#edit_scd_umum').prop('checked', true);
            } else {
                $('#edit_scd_umum').prop('checked', false);
            }
            if (scheduleDetail.scd_bpjs === 1) {
                $('#edit_scd_bpjs').prop('checked', true);
            } else {
                $('#edit_scd_bpjs').prop('checked', false);
            }
            $('#overlay').hide(); // Hide spinner
        },
        error: function (error) {
            console.log('Error:', error);
            $('#overlay').hide(); // Hide spinner
        }
    });
});
