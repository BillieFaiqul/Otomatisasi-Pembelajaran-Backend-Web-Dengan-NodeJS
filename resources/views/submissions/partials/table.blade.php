<table class="table pb-20" id="submissions_table">
    <thead>
        <tr>
            <th>Title</th>
            {{-- <th>Submission Count</th> --}}
            <th class="text-center">Attempts Count</th>
            <th class="text-center">Status</th>
            <th class="text-center">Action</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>
@section('scripts')
<script type="text/javascript">
    function requestServer(element){
        let submission_id = element.attr('data-submission-id');
        let request_type = element.attr('data-request-type');

        switch (request_type) {
            case "delete":
                swal({
                    title: "Are you sure?",
                    text: "You are about to delete this submission!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        $.ajax({
                            url: '/submissions/delete/submission',
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token()}}'
                            },
                            data: {
                                submission_id: submission_id,
                                _token: '{{ csrf_token() }}',
                            },
                            success: function(data) {
                                swal({
                                    title: "Success!",
                                    text: "Your submission has been deleted!",
                                    icon: "success",
                                    button: "Ok",
                                }).then(function() {
                                    window.location = "/submissions";
                                });
                            },
                            error: function(data) {
                                swal({
                                    title: "Error!",
                                    text: "Something went wrong!",
                                    icon: "error",
                                    button: "Ok",
                                });
                            }
                        });
                    }
                });
                break;
            case "restart":
                swal({
                    title: "Are you sure?",
                    text: "You are about to restart this submission!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willRestart) => {
                    if (willRestart) {
                        $.ajax({
                            url: '/submissions/restart/submission',
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token()}}'
                            },
                            data: {
                                submission_id: submission_id,
                                _token: '{{ csrf_token() }}',
                            },
                            success: function(data) {
                                swal({
                                    title: "Success!",
                                    text: "Your submission has been restarted!",
                                    icon: "success",
                                    button: "Ok",
                                }).then(function() {
                                    window.location = "/submissions";
                                });
                            },
                            error: function(data) {
                                swal({
                                    title: "Error!",
                                    text: "Something went wrong!",
                                    icon: "error",
                                    button: "Ok",
                                });
                                console.log(data);
                            }
                        });
                    }
                });
                break;
            case "change-source-code":
                // redirect to change source code page
                window.location = '/submissions/change/' + submission_id;
                break;
            default:
                break;
        }
    }


    $(function () {
    var table = $('#submissions_table').DataTable({
        "processing": true,
        "retrieve": true,
        "serverSide": true,
        'paginate': true,
        "bDeferRender": true,
        "responsive": true,
        "autoWidth": false,
        "bLengthChange" : false,
        "aaSorting": [],
        "lengthMenu": [5],
        "searching": false,
        "info" : false,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search",
            "paginate": {
                "previous": "<",
                "next": ">",
            },
        },
        ajax: "{{ route('submissions') }}",
        columns: [
            {data: 'title', name: 'title', orderable: true, searchable: true},
            // {data: 'submission_count', name: 'submission_count', orderable: true, searchable: false, className: "text-center"},
            {data: 'attempts_count', name: 'attempts_count', orderable: true, searchable: false, className: "text-center"},
            {data: 'submission_status', name: 'submission_status', orderable: true, searchable: true, className: "text-center"},
            {data: 'action', name: 'action', orderable: false, searchable: false, className: "text-center"},
        ]
    });
  });

  $('#submissions_table_paginate > span > a').addClass('bg-gray-900 text-gray-300');
</script>
@endsection