$(document).on('click', '.bp-edit', function () {
    let partnerUcode = $(this).data('partner-ucode');
    $('#editForm').attr('action', '/administrator/businessPartners/' + partnerUcode);
    $.ajax({
        url: '/administrator/businessPartners/' + partnerUcode,
        type: 'GET',
        dataType: 'json',
        beforeSend: function() {
            $('#overlay').show(); // Show spinner
        },
        success: function (businessPartner) {
            $('#edit_bp_order').val(businessPartner.bp_order);
            $('#edit_bp_code').val(businessPartner.bp_code);
            $('#edit_bp_name').val(businessPartner.bp_name);
            $('#edit_bp_type').val(businessPartner.bp_type);
            $('#edit_bp_scheme').val(businessPartner.bp_scheme);
            $('#edit_bp_contract').val(businessPartner.bp_contract);
            if (businessPartner.bp_active === 1) {
                $('#edit_bp_active_on').prop('checked', true);
            } else {
                $('#edit_bp_active_off').prop('checked', true);
            }
            $('#overlay').hide(); // Hide spinner
        },
        error: function (error) {
            console.log('Error:', error);
            $('#overlay').hide(); // Hide spinner
        }
    });
});

$(document).on('click', '.bp-order', function () {
    $.ajax({
        url: '/administrator/businessPartners/lastOrder',
        type: 'GET',
        dataType: 'json',
        beforeSend: function() {
            $('#overlay').show(); // Show spinner
        },
        success: function (businessPartner) {
            $('#add_bp_order').val(businessPartner.bp_order + 1 );
            $('#overlay').hide(); // Hide spinner
        },
        error: function (error) {
            console.log('Error:', error);
            $('#overlay').hide(); // Hide spinner
        }
    });
});

