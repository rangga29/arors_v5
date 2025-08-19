$(document).on('click', '.qrc-edit', function () {
    let qrcUcode = $(this).data('qrc-ucode');
    $('#editForm').attr('action', '/administrator/qrcarolus/' + qrcUcode);
    $.ajax({
        url: '/administrator/qrcarolus/' + qrcUcode,
        type: 'GET',
        dataType: 'json',
        beforeSend: function() {
            $('#overlay').show(); // Show spinner
        },
        success: function (qrcarolus) {
            $('#edit_qrc_room').val(qrcarolus.qrc_room);
            if (qrcarolus.qrc_active === 1) {
                $('#edit_qrc_active_on').prop('checked', true);
            } else {
                $('#edit_qrc_active_off').prop('checked', true);
            }
            $('#overlay').hide(); // Hide spinner
        },
        error: function (error) {
            console.log('Error:', error);
            $('#overlay').hide(); // Hide spinner
        }
    });
});
