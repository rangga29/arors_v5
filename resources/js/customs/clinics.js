$(document).on('click', '.cl-edit', function () {
    let clinicUcode = $(this).data('clinic-ucode');
    $('#editForm').attr('action', '/administrator/clinics/' + clinicUcode);
    $.ajax({
        url: '/administrator/clinics/' + clinicUcode,
        type: 'GET',
        dataType: 'json',
        beforeSend: function() {
            $('#overlay').show(); // Show spinner
        },
        success: function (clinic) {
            $('#edit_cl_code').val(clinic.cl_code);
            $('#edit_cl_code_bpjs').val(clinic.cl_code_bpjs);
            $('#edit_cl_name').val(clinic.cl_name);
            $('#edit_cl_order').val(clinic.cl_order);
            if (clinic.cl_umum === 1) {
                $('#edit_cl_umum').prop('checked', true);
            } else {
                $('#edit_cl_umum').prop('checked', false);
            }
            if (clinic.cl_bpjs === 1) {
                $('#edit_cl_bpjs').prop('checked', true);
            } else {
                $('#edit_cl_bpjs').prop('checked', false);
            }
            if (clinic.cl_active === 1) {
                $('#edit_cl_active_on').prop('checked', true);
            } else {
                $('#edit_cl_active_off').prop('checked', true);
            }
            $('#overlay').hide(); // Hide spinner
        },
        error: function (error) {
            console.log('Error:', error);
            $('#overlay').hide(); // Hide spinner
        }
    });
});

$(document).on('click', '.cl-order', function () {
    $.ajax({
        url: '/administrator/clinics/lastOrder',
        type: 'GET',
        dataType: 'json',
        beforeSend: function() {
            $('#overlay').show(); // Show spinner
        },
        success: function (clinic) {
            $('#add_cl_order').val(clinic.cl_order + 1 );
            $('#overlay').hide(); // Hide spinner
        },
        error: function (error) {
            console.log('Error:', error);
            $('#overlay').hide(); // Hide spinner
        }
    });
});
