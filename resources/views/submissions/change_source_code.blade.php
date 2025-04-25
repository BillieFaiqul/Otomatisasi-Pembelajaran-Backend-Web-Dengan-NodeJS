<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __("Change submission's code for project: ") . $submission->project->title }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('submissions.update') }}">
                        @csrf
                        <input type="hidden" name="submission_id" value="{{ $submission->id }}" />
                        <div class="mt-4">
                            <x-input-label for="folder" :value="__('Submit The Source Code')" class="mb-2" />
                            <input type="file" name="folder_path" id="folder" />
                            <x-input-error :messages="$errors->get('folder')" class="mt-2" />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="github_url" :value="__('Or Github Link')" />
                            <x-text-input id="github_url" class="block mt-1 w-full" type="text" name="github_url"
                                :value="old('github_url')" placeholder="E.g. https://github.com/username/repository.git" />
                            <x-input-error :messages="$errors->get('github_url')" class="mt-2" />
                        </div>
                        <div class="flex items-center justify-end mt-12">
                            <x-primary-button class="ml-4">
                                {{ __('Submit') }}
                            </x-primary-button>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>
    @section('scripts', 'Submit Project')
    <script type="text/javascript">
        const inputElement = document.querySelector('input[id="folder"]');
        const github_url = document.querySelector('input[id="github_url"]');
        FilePond.registerPlugin(FilePondPluginFileValidateType);
        const pond = FilePond.create(inputElement);
        pond.disabled = false;
        github_url.disabled = false;
        let url = '/submissions/upload';
        const project_id = '{{ $submission->project->id }}';
        // pond has value then disable github_url and vice versa
        url = '/submissions/upload/' + project_id;
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
        pond.on('addfile', function() {
            if (pond.getFiles().length > 0) {
                github_url.disabled = true;
                url = '/submissions/upload/' + project_id;
            } else {
                github_url.disabled = false;
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

        github_url.addEventListener('input', function() {
            if (github_url.value !== '') {
                pond.disabled = true;
                url = '/submissions/upload/' + project_id;
            } else {
                pond.disabled = false;
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
        $('.filepond--panel-root').addClass('bg-gray-900 ');
        $('.filepond--drop-label').addClass(
            'border-2 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-secondary-500 dark:focus:border-secondary-600 focus:ring-secondary-500 dark:focus:ring-secondary-600 rounded-md shadow-sm '
            );

        $('form').on('submit', function(e) {
            e.preventDefault();
            if (pond.getFiles().length > 0) {
                if (pond.getFiles()[0].status === 5) {
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
                                        const submission_id = data.submission.id;
                                        window.location = "/submissions/submission/" +
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
                                        const submission_id = data.submission.id;
                                        window.location = "/submissions/submission/" +
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
                                    console.log(data);
                                }
                            });
                        }
                    });
                }
            }
        });
    </script>
</x-app-layout>
