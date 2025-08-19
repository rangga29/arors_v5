$(document).on('click', '.user-edit', function () {
    let user = $(this).data('user');
    $('#editForm').attr('action', '/administrator/users/' + user);
    $.ajax({
        url: '/administrator/users/' + user,
        type: 'GET',
        dataType: 'json',
        beforeSend: function() {
            $('#overlay').show();
        },
        success: function (response) {
            $('#edit_name').val(response.name);
            $('#edit_username').val(response.username);
            $.get('/administrator/users/' + user + '/getRole', function (response) {
                $('#edit_role').val(response.id);
                $('#overlay').hide();
            }).fail(function (error) {
                console.log('Error:', error);
                $('#overlay').hide();
            });
        },
        error: function (error) {
            console.log('Error:', error);
            $('#overlay').hide();
        }
    });
});
