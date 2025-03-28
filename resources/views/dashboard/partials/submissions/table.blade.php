<table class="table" id="submissions_table">
    <thead>
        <tr>
            <th>Title</th>
            <th>Submission Count</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>
@section('scripts')
<script type="text/javascript">
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
        ajax: "{{ route('dashboard') }}",
        columns: [
            {data: 'title', name: 'title', orderable: true, searchable: true},
            {data: 'submission_count', name: 'submission_count', orderable: true, searchable: false, className: "text-center"}
        ]
    });
  });

  $('#submissions_table_paginate > span > a').addClass('bg-gray-900 text-gray-300');
</script>
@endsection