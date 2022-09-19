$(function() {

    $('#customersTable').DataTable();

    $(".edit-record-button").on('click', function (e) {
        e.preventDefault();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type:'GET',
            url:'/customer/' + $(this).data("id"),
            data: {},
            success:function(data) {

            }
        });
    });

    $(".delete-record-button").on('click', function (e) {
        e.preventDefault();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type:'DELETE',
            url:'/customer/' + $(this).data("id"),
            data: {},
            success:function(data) {

            }
        });
    });

});

