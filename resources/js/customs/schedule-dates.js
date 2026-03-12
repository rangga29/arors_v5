import 'flatpickr/dist/flatpickr.js';
import { Indonesian } from 'flatpickr/dist/l10n/id.js';

$(document).on('click', '.sd-add', function () {
    let maxDate = $(this).data('max-date');
    $('#download_date').flatpickr({
        altInput: true,
        altFormat: "j F Y",
        dateFormat: "Y-m-d",
        locale: Indonesian,
        minDate: maxDate
    });
});

$(document).on('click', '.sd-edit', function () {
    let scheduleDateUcode = $(this).data('schedule-date-ucode');
    $('#editForm').attr('action', '/administrator/schedules/dates/' + scheduleDateUcode);
    $.ajax({
        url: '/administrator/schedules/dates/' + scheduleDateUcode,
        type: 'GET',
        dataType: 'json',
        success: function (scheduleDate) {
            if (scheduleDate.sd_is_holiday === 1) {
                $('#edit_sd_holiday_on').prop('checked', true);
            } else {
                $('#edit_sd_holiday_off').prop('checked', true);
            }
            $('#edit_sd_holiday_desc').val(scheduleDate.sd_holiday_desc);
        },
        error: function (error) {
            console.log('Error:', error);
        }
    });
});

$(document).on('click', '.sd-download', function () {
    $('#overlay').show();
});
