<div class="">
    <form method="POST" action="{{ route('submissions.submit') }}">
        @csrf
        <div>
            <x-input-label for="project_id" :value="__('Select Project Before Uploading')" class="mb-2" />
            <select name="project_id" id="project_id"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-secondary-500 focus:border-secondary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-secondary-500 dark:focus:border-secondary-500">
                <option value="">Select Project</option>
                @foreach ($projects as $project)
                    @php
                        $haveBeenSubmitted = false;
                        $submssion = \App\Models\Submission::where('project_id', $project->id)
                            ->where('user_id', Auth::user()->id)
                            ->first();
                        if ($submssion) {
                            $haveBeenSubmitted = true;
                        }
                    @endphp
                    @if ($haveBeenSubmitted)
                        <option disabled>
                            {{ $project->title }} (HAVE BEEN SUBMITTED)
                        </option>
                    @else
                        <option value="{{ $project->id }}">
                            {{ $project->title }}
                        </option>
                    @endif
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('project_id')" class="mt-2" />
        </div>
        <div class="mt-4">
            <x-input-label for="folder" :value="__('Submit The Source Code')" class="mb-2" />
            <input type="file" name="folder_path" id="folder" data-allow-reorder="true"
                data-max-file-size="3MB" />
            <x-input-error :messages="$errors->get('folder')" class="mt-2" />
        </div>
        <div class="mt-4">
            <x-input-label for="github_url" :value="__('Or Github Link')" />
            <x-text-input id="github_url" class="block mt-1 w-full" type="text" name="github_url" :value="old('github_url')"
                placeholder="E.g. https://github.com/username/repository.git" />
            <x-input-error :messages="$errors->get('github_url')" class="mt-2" />
        </div>
        <div class="flex items-center justify-end mt-12">
            <x-primary-button class="ml-4">
                {{ __('Submit') }}
            </x-primary-button>
        </div>

    </form>
</div>
@section('scripts', 'Submit Project')
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        const github_url = document.querySelector('input[id="github_url"]');
        FilePond.registerPlugin(
            FilePondPluginFileValidateType,
        );
        const pond = FilePond.create(document.querySelector('input[id="folder"]'), {
            labelIdle: `Drag & Drop Your ZIP Project or <span class="filepond--label-action">Browse</span>`,
        });
        pond.disabled = true;
        github_url.disabled = true;
        const project_id = $('#project_id');
        var url = '/submissions/upload';
        project_id.on('change', function() {
            const project_id = $(this).val();
            if (project_id) {
                pond.disabled = false;
                github_url.disabled = false;
                url = '/submissions/upload/' + project_id;
            } else {
                pond.disabled = true;
                github_url.disabled = true;
                url = '/submissions/upload';
            }
            pond.setOptions({
                server: {
                    url: url,
                    process: {
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    },
                },
                allowMultiple: false,
                acceptedFileTypes: ['application/x-zip-compressed', 'application/zip'],
                fileValidateTypeDetectType: (source, type) =>
                    new Promise((resolve, reject) => {
                        resolve(type);
                    }),
            });
        });
        $('.filepond--credits').hide();
        $('.filepond--panel-root').addClass('bg-gray-900');
        $('.filepond--drop-label').addClass(
            'border-2 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-secondary-500 dark:focus:border-secondary-600 focus:ring-secondary-500 dark:focus:ring-secondary-600 rounded-md shadow-sm '
            );

        $('form').on('submit', function(e) {
            e.preventDefault();
            if (pond.getFiles().length > 0) {
                if (pond.getFiles()[0].status === 5) {
                    if ($('#project_id').val() === '') {
                        swal({
                            title: "Error!",
                            text: "Please choose a project!",
                            icon: "error",
                            button: "Ok",
                        });
                    }
                    swal({
                        title: "Are you sure?",
                        text: "You are about to submit this project!",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    }).then((willDelete) => {
                        if (willDelete) {
                            $.ajax({
                                url: $(this).attr('action'),
                                type: 'POST',
                                data: new FormData(this),
                                contentType: false,
                                cache: false,
                                processData: false,
                                success: function(data) {
                                    swal({
                                        title: "Success!",
                                        text: "Your project has been submitted!",
                                        icon: "success",
                                        button: "Ok",
                                    }).then(function() {
                                        const submission_id = data
                                            .submission.id;
                                        window.location =
                                            "/submissions/submission/" +
                                            submission_id;
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
                } else {
                    swal({
                        title: "Error!",
                        text: "Please wait for the file to be uploaded!",
                        icon: "error",
                        button: "Ok",
                    });
                }
            } else {
                if ($('#github_url').val() === '') {
                    swal({
                        title: "Error!",
                        text: "Please upload a file or enter a github link!",
                        icon: "error",
                        button: "Ok",
                    });
                } else {
                    if ($('#project_id').val() === '') {
                        swal({
                            title: "Error!",
                            text: "Please choose a project!",
                            icon: "error",
                            button: "Ok",
                        });
                    } else {
                        swal({
                            title: "Are you sure?",
                            text: "You are about to submit this project!",
                            icon: "warning",
                            buttons: true,
                            dangerMode: true,
                        }).then((willDelete) => {
                            if (willDelete) {
                                $.ajax({
                                    url: $(this).attr('action'),
                                    type: 'POST',
                                    data: new FormData(this),
                                    contentType: false,
                                    cache: false,
                                    processData: false,
                                    success: function(data) {
                                        swal({
                                            title: "Success!",
                                            text: "Your project has been submitted!",
                                            icon: "success",
                                            button: "Ok",
                                        }).then(function() {
                                            const submission_id = data
                                                .submission.id;
                                            window.location =
                                                "/submissions/submission/" +
                                                submission_id;
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
                    }
                }
            }
        });
    });
</script>
