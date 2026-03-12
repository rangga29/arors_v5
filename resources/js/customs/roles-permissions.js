$(document).on('click', '.role-edit', function () {
    let roleName = $(this).data('role');
    $('#editForm').attr('action', '/administrator/users/roles/' + roleName);
    $.ajax({
        url: '/administrator/users/roles/' + roleName,
        type: 'GET',
        dataType: 'json',
        success: function (role) {
            if (role.cl_active === 1) {
                $('#edit_cl_active_on').prop('checked', true);
            } else {
                $('#edit_cl_active_off').prop('checked', true);
            }
        },
        error: function (error) {
            console.log('Error:', error);
        }
    });
});
