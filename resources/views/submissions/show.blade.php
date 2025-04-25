<x-app-layout>
    @if (request()->routeIs('submissions.showAll'))
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('All submissions for project: ') . $project->title }}
            </h2>
        </x-slot>

        <div class="py-5">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    @php
                        $no = 0;
                    @endphp
                    @forelse ($submissions as $submission)
                        @php
                            $no += 1;
                        @endphp
                        <div class="relative overflow-x-auto">
                            <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                    <caption
                                        class="p-5 text-lg font-semibold text-left text-gray-900 bg-white dark:text-white dark:bg-gray-800">
                                        {{ $submission->project->title }} - submission number #{{ $no }}
                                        <p class="mt-1 text-sm font-normal text-gray-500 dark:text-gray-400">Here is the
                                            the
                                            list of attempts for this submission</p>
                                        <p
                                            class="mt-1 text-sm font-normal text-gray-500 dark:text-gray-400 float-right">
                                            Total
                                            Attempts: {{ $submission->getTotalAttemptsCount() }}</p>
                                    </caption>
                                    <thead
                                        class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                        <tr>
                                            <th scope="col" class="px-6 py-3">Attempt NO#</th>
                                            <th scope="col" class="px-6 py-3">Status</th>
                                            <th scope="col" class="px-6 py-3">Time Spent</th>
                                            <th scope="col" class="px-6 py-3">Description</th>
                                            <th scope="col" class="px-6 py-3"><span class="sr-only">Edit</span></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                            <td scope="row"
                                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white text-center">
                                                <span
                                                    class="inline-flex items-center justify-center px-2 py-1 rounded-lg text-xs font-bold leading-none bg-secondary-100 text-secondary-800">
                                                    {{ $submission->attempts }}
                                                </span>
                                            </td>
                                            <td scope="row"
                                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                @php
                                                    $statusClass = '';
                                                    if ($submission->status === 'completed') {
                                                        $statusClass = 'bg-green-100 text-green-800';
                                                    } elseif ($submission->status === 'failed') {
                                                        $statusClass = 'bg-red-100 text-red-800';
                                                    } elseif ($submission->status === 'processing') {
                                                        $statusClass = 'bg-secondary-100 text-secondary-800';
                                                    } elseif ($submission->status === 'pending') {
                                                        $statusClass = 'bg-blue-100 text-blue-800';
                                                    }
                                                @endphp
                                                <span
                                                    class="inline-flex items-center justify-center px-2 py-1 rounded-lg text-xs font-bold leading-none {{ $statusClass }}">
                                                    {{ ucfirst($submission->status) }}
                                                </span>
                                            </td>
                                            <td scope="row"
                                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                {{-- get the difference between start and end time from submission using carbon and if end is null then use current time --}}
                                                @php
                                                    $start = Carbon\Carbon::parse($submission->start);
                                                    $end = Carbon\Carbon::parse($submission->end ?? now());
                                                    $time = $end->diff($start)->format('%H:%I:%S');
                                                @endphp
                                                {{ $time }}
                                            </td>
                                            <td scope="row"
                                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                <span
                                                    class="inline-flex items-center justify-center px-2 py-1 rounded-lg text-xs font-bold leading-none bg-secondary-100 text-secondary-800">
                                                    Current Attempt
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <a href="/submissions/submission/{{ $submission->id }}"
                                                    class="font-medium text-blue-600 dark:text-blue-500 hover:underline">View</a>
                                                @if ($submission->status === 'completed' || $submission->status === 'failed')
                                                    |
                                                    <a href="/submissions/submission/{{ $submission->id }}/download?type=current"
                                                        class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Download
                                                        Results</a>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                            <td scope="row"
                                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                Past Attempts
                                            </td>
                                        </tr>
                                        @forelse ($submission->history->sortByDesc('attempts') as $history)
                                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                                <td scope="row"
                                                    class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white text-center">
                                                    {{ $history->attempts }}
                                                </td>
                                                <td scope="row"
                                                    class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                    @php
                                                        $statusClass = '';
                                                        if ($history->status === 'completed') {
                                                            $statusClass = 'bg-green-100 text-green-800';
                                                        } elseif ($history->status === 'failed') {
                                                            $statusClass = 'bg-red-100 text-red-800';
                                                        } elseif ($history->status === 'processing') {
                                                            $statusClass = 'bg-secondary-100 text-secondary-800';
                                                        } elseif ($history->status === 'pending') {
                                                            $statusClass = 'bg-blue-100 text-blue-800';
                                                        }
                                                    @endphp
                                                    <span
                                                        class="inline-flex items-center justify-center px-2 py-1 rounded-lg text-xs font-bold leading-none {{ $statusClass }}">
                                                        {{ ucfirst($history->status) }}
                                                    </span>
                                                </td>
                                                <td scope="row"
                                                    class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                    @php
                                                        $history_start = Carbon\Carbon::parse($history->start);
                                                        $history_end = Carbon\Carbon::parse($history->end ?? now());
                                                        $history_time = $history_end
                                                            ->diff($history_start)
                                                            ->format('%H:%I:%S');
                                                    @endphp
                                                    {{ $history_time }}
                                                </td>
                                                <td scope="row"
                                                    class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                    {{ $history->description }}
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <a href="/submissions/submission/history/{{ $history->id }}"
                                                        class="font-medium text-blue-600 dark:text-blue-500 hover:underline">View</a>
                                                    |
                                                    <a href="/submissions/submission/{{ $history->id }}/download?type=history"
                                                        class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Download
                                                        Results</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <div class="p-5">
                                                <x-not-found message="No Past Attempts Found" />
                                            </div>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @empty
                            <div class="p-5">
                                <x-not-found message="No Submissions Found" />
                            </div>
                    @endforelse
                </div>
            </div>
        </div>
        </div>
    @elseif(request()->routeIs('submissions.show') || request()->routeIs('submissions.history'))
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Submission NO#') . $submission->id . __(' of Project: ') . $submission->project->title . __(' attempt NO#: ') . $submission->attempts }}
            </h2>
        </x-slot>

        <div class="py-5">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3 md:gap-8">
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg md:col-span-1 md:row-span-1 md:rounded-md md:shadow-md md:py-1 md:px-3 lg:px-5 xl:px-7">
                        <!-- content for the smaller column -->
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <ol class="list-disc list-inside">
                                <li class="list-none">
                                    <div class="flex justify-start gap-2">
                                        <x-pending_icon class="w-[20px] h-[20px] pt-[0.10rem]" id="start_pending_icon"
                                            svgWidth='20px' svgHeight='20px' />
                                        <x-success_icon class="hidden w-[20px] h-[20px] pt-[0.10rem]"
                                            id="start_success_icon" svgWidth='20px' svgHeight='20px' />
                                        <x-failed_icon class="hidden w-[20px] h-[20px] pt-[0.10rem]"
                                            id="start_failed_icon" svgWidth='20px' svgHeight='20px' />
                                        <span class="text-gray-400 font-semiblid stepNames">Start</span>
                                    </div>
                                </li>
                                @forelse ($steps as $step)
                                    <li class="list-none">
                                        <div class="flex justify-start gap-2">
                                            <x-pending_icon class="w-[20px] h-[20px] pt-[0.10rem]"
                                                id="{{ $step->id }}_pending_icon" svgWidth='20px'
                                                svgHeight='20px' />
                                            <x-success_icon class="hidden w-[20px] h-[20px] pt-[0.10rem]"
                                                id="{{ $step->id }}_success_icon" svgWidth='20px'
                                                svgHeight='20px' />
                                            <x-failed_icon class="hidden w-[20px] h-[20px] pt-[0.10rem]"
                                                id="{{ $step->id }}_failed_icon" svgWidth='20px'
                                                svgHeight='20px' />
                                            <span
                                                class="text-gray-400 font-semiblid stepNames">{{ $step->executionStep->name }}</span>
                                        </div>
                                    </li>
                                    @if ($step->executionStep->name == 'NPM Run Tests')
                                        @forelse ($step->variables as $testCommandValue)
                                            @php
                                                $command = implode(' ', $step->executionStep->commands);
                                                $key = explode('=', $testCommandValue)[0];
                                                $value = explode('=', $testCommandValue)[1];
                                                $testStep = str_replace($key, $value, $command);
                                                $iconID = str_replace(' ', '_', $testStep);
                                                $iconID = str_replace('.', '_', $iconID);
                                            @endphp
                                            <li class="list-none pl-5">
                                                <div class="flex justify-start gap-2">
                                                    <x-pending_icon class="w-[20px] h-[20px] pt-[0.10rem]"
                                                        id="{{ $step->id }}_pending_icon_{{ $iconID }}"
                                                        svgWidth='20px' svgHeight='20px' />
                                                    <x-success_icon class="hidden w-[20px] h-[20px] pt-[0.10rem]"
                                                        id="{{ $step->id }}_success_icon_{{ $iconID }}"
                                                        svgWidth='20px' svgHeight='20px' />
                                                    <x-failed_icon class="hidden w-[20px] h-[20px] pt-[0.10rem]"
                                                        id="{{ $step->id }}_failed_icon_{{ $iconID }}"
                                                        svgWidth='20px' svgHeight='20px' />
                                                    <span
                                                        class="text-gray-400 font-semiblid stepTestNames">{{ $testStep }}</span>
                                                </div>
                                            </li>
                                        @empty
                                            <x-not-found message="No Tests Found" />
                                        @endforelse
                                    @endif
                                @empty
                                    <x-not-found message="No Steps Found" />
                                @endforelse
                                <li class="list-none">
                                    <div class="flex justify-start gap-2">
                                        <x-pending_icon class="w-[20px] h-[20px] pt-[0.10rem]" id="done_pending_icon"
                                            svgWidth='20px' svgHeight='20px' />
                                        <x-success_icon class="hidden w-[20px] h-[20px] pt-[0.10rem]"
                                            id="done_success_icon" svgWidth='20px' svgHeight='20px' />
                                        <x-failed_icon class="hidden w-[20px] h-[20px] pt-[0.10rem]"
                                            id="done_failed_icon" svgWidth='20px' svgHeight='20px' />
                                        <span class="text-gray-400 font-semiblid stepNames">Done</span>
                                    </div>
                                </li>
                            </ol>
                        </div>
                    </div>
                    <div
                        class="bg-gray-200 dark:bg-gray-900 border-secondary border-2 overflow-hidden shadow-sm sm:rounded-lg md:col-span-2 md:row-span-1 md:rounded-md md:shadow-md md:py-6 md:px-8 lg:px-12 xl:px-16">
                        <!-- content for the bigger column -->

                        <div class="m-5 w-100 bg-gray-600 sm:rounded-lg md:rounded-md" id="progress">
                            <div class="text-center w-[1%] h-5 bg-secondary text-white sm:rounded-lg md:rounded-md text-sm"
                                id="bar">0%</div>
                        </div>
                        <div id="loader"
                            class="mx-5 mt-1 float-right hidden h-8 w-8 animate-spin rounded-full border-4 border-solid border-current border-r-transparent align-[-0.125em] text-secondary motion-reduce:animate-[spin_1.5s_linear_infinite]"
                            role="status">
                            <span
                                class="!absolute !-m-px !h-px !w-px !overflow-hidden !whitespace-nowrap !border-0 !p-0 ![clip:rect(0,0,0,0)]">Loading...</span>
                        </div>
                        <div id="refresh"
                            class="hidden float-right cursor-pointer mx-5 mt-1 motion-reduce:animate-[spin_1.5s_linear_infinite]">
                            <x-refresh_icon class="h-8 w-8" svgWidth='2rem' svgHeight='2rem' />
                        </div>
                        <div class="mx-5">
                            <p class="text-white text-xl" id="submission_message"></p>
                            <p class="text-white text-xl mt-5" id="submission_status"></p>
                        </div>
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <div class="text-white mt-5" id="submission_results">
                                Waitting for the submission to progress...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        @section('scripts')
            <script>
                $(document).ready(function() {
                    // Left side steps
                    const startElement = $(`.stepNames:contains(Start)`).closest('li');
                    const stepNames = $('.stepNames');
                    const stepTestNames = $('.stepTestNames');
                    const doneElement = $(`.stepNames:contains(Done)`).closest('li');
                    // Right side submission results
                    const submission_message = $('#submission_message');
                    const submission_status = $('#submission_status');
                    const submission_results = $('#submission_results');
                    // Right side progress indicators
                    const barElement = $('#bar');
                    const loaderElement = $('#loader');
                    const refreshElement = $('#refresh');
                    // varibale for faild submission, to be used in the refresh button
                    let allowedToRefresh = true;
                    // variable for the progress bar
                    let completion_percentage = 0;
                    // variable for the response
                    let old_response = {
                        status: 'pending',
                        message: 'Submission is pending',
                        results: {}
                    };
                    let error_server = false;
                    // the history page
                    let isNotHistory = `{{ !request()->routeIs('submissions.history') }}`;
                    isNotHistory = isNotHistory == 1 ? true : false;
                    // variable for checking if all the steps are completed
                    let checkResponse = false;
                    // retry mechanism variables
                    let maxRetries = 5;
                    let currentRetryCount = 0;
                    let retryDelay = 500;

                    // animate the progess bar
                    function move(start = 0, end = 100) {
                        var width = start;
                        barElement.width(width + '%').html(width + '%');
                        var duration = 500; // duration of the animation in ms
                        barElement.stop().animate({
                            width: end + '%'
                        }, {
                            duration: duration * (end - start) / 100,
                            step: function(now, fx) {
                                var val = Math.round(now);
                                barElement.html(val + '%');
                            }
                        });
                    }

                    async function getSubmissionStatus() {
                        try {
                            await $.ajax({
                                url: `/submissions/status/submission/{{ $submission->id }}`,
                                method: 'GET',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                data: {
                                    isNotHistory: isNotHistory,
                                },
                                success: function(response) {
                                    // Reset retry count on successful request
                                    currentRetryCount = 0;
                                    retryDelay = 2000;

                                    if (response.status === 'processing' || response.status === 'pending') {
                                        try {
                                            checkSubmissionProgress();
                                            if (response.step !== null && error_server == false) {
                                                setTimeout(() => {
                                                    window.requestAnimationFrame(
                                                        getSubmissionStatus);
                                                }, 2000);
                                            };
                                        } catch (error) {
                                            console.log(error);
                                            retrySubmissionStatus();
                                        }
                                    } else {
                                        if (response.status === 'failed') {
                                            // failed status
                                            updateUIFailedStatus(response);
                                        } else if (response.status === 'completed') {
                                            // completed status
                                            loaderElement.addClass('hidden');
                                            updateUICompletedStatus(response);
                                        }
                                        updateUI(response);
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error('Status request failed:', error);
                                    retrySubmissionStatus();
                                }
                            })
                        } catch (error) {
                            console.log(error);
                            retrySubmissionStatus();
                        }
                    }

                    function retrySubmissionStatus() {
                        if (currentRetryCount < maxRetries) {
                            currentRetryCount++;
                            console.log(
                                `Retrying status request (${currentRetryCount}/${maxRetries}) in ${retryDelay/1000}s...`
                            );

                            submission_message.text("Submssion Message: Network error. Retrying connection...");

                            setTimeout(() => {
                                retryDelay = Math.min(retryDelay * 1.5, 10000);
                                window.requestAnimationFrame(getSubmissionStatus);
                            }, retryDelay);
                        } else {
                            const error_response = {
                                status: 'failed',
                                message: 'Network connection failed after multiple attempts. Please refresh the page and try again.'
                            };
                            error_server = true;
                            updateUIFailedStatus(error_response);
                        }
                    }

                    // send request to the server to check the submission progress
                    async function checkSubmissionProgress() {
                        try {
                            // get the response from the server
                            const response = await $.ajax({
                                url: `/submissions/process/submission`,
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                data: {
                                    isNotHistory: isNotHistory,
                                    submission_id: '{{ $submission->id }}',
                                    _token: '{{ csrf_token() }}',
                                },
                                error: function(xhr, status, error) {
                                    if (xhr.status === 504) {
                                        location.reload();
                                    }
                                    console.error('Process request failed:', error);
                                    throw new Error('Process request failed');
                                }
                            });

                            // Reset retry count on successful request
                            currentRetryCount = 0;
                            retryDelay = 2000;

                            checkResponse = JSON.stringify(old_response) === JSON.stringify(response);
                            if (!checkResponse) {
                                updateUI(response);
                                old_response = response;
                            }
                            if (response.message === 'Submission is processing meanwhile there is no step to execute') {
                                // run through all the results if there is on of them failed the checkResponse will be false
                                for (const [stepName, stepData] of Object.entries(response.results)) {
                                    if (stepData.status === 'failed') {
                                        checkResponse = false;
                                    }
                                }
                                if (checkResponse) {
                                    // if all the steps are completed
                                    updateUICompletedStatus(response);
                                } else {
                                    // if one of the steps failed
                                    updateUIFailedStatus(response);
                                }
                            }
                            // update the UI with the response
                            // move the progress bar if the completion percentage has changed
                            if (completion_percentage !== response.completion_percentage) {
                                completion_percentage = response.completion_percentage;
                                move(0, response.completion_percentage);
                            }
                            // check the status of the submission
                            // processing status
                            if (response.status === 'processing') {
                                if (response.next_step?.id !== undefined && isNotHistory) {
                                    // the submission is still processing
                                    loaderElement.removeClass('hidden');
                                } else {
                                    // the end of the submission steps
                                    loaderElement.addClass('hidden');
                                }
                            } else if (response.status === 'failed') {
                                // failed status
                                updateUIFailedStatus(response);
                            } else if (response.status === 'completed') {
                                // completed status
                                loaderElement.addClass('hidden');
                                updateUICompletedStatus(response);
                            }
                        } catch (error) {
                            // if the request failed
                            const error_response = {
                                status: 'failed',
                                message: 'Something went wrong'
                            };
                            updateUIFailedStatus(error_response);
                            error_server = true;
                            throw error;
                            // console.log(error);
                        }
                    }

                    getSubmissionStatus();

                    function updateUIFailedStatus(response) {
                        // hide the loader
                        loaderElement.addClass('hidden');
                        // change the done icon to failed
                        doneElement.find('#done_pending_icon').addClass('hidden');
                        doneElement.find('#done_failed_icon').removeClass('hidden');
                        doneElement.find('span').addClass('text-red-400');
                        // show error message
                        updateSubmissionHeader(response.status, response.message);
                        // move the progress bar to 100% and red color
                        move(0, 100);
                        barElement.removeClass('bg-secondary');
                        barElement.addClass('bg-red-400');
                        // show the refresh button
                        if (isNotHistory) {
                            refreshElement.removeClass('hidden');
                            if (allowedToRefresh) {
                                refreshElement.click(function() {
                                    // update the submission results and progress bar
                                    move(0, 0);
                                    updateSubmissionHeader('Wait', "Restarting...");
                                    // request the server to refresh the submission
                                    requestRefresh();
                                });
                            }
                        }
                        // update the submission results last element
                    }

                    async function requestRefresh() {
                        // start the refresh loader
                        refreshElement.addClass('animate-spin');
                        refreshElement.removeClass('cursor-pointer');
                        try {
                            if (allowedToRefresh) {
                                // prevent the user from clicking the button multiple times
                                allowedToRefresh = false;
                                const response = await $.ajax({
                                    url: '/submissions/refresh/submission',
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    data: {
                                        submission_id: '{{ $submission->id }}',
                                        _token: '{{ csrf_token() }}',
                                    },
                                });
                                // update the UI with the response
                                // refresh the page after 5 seconds
                                setTimeout(() => {
                                    refreshElement.removeClass('animate-spin');
                                    refreshElement.addClass('hidden');
                                    barElement.removeClass('bg-red-400');
                                    barElement.addClass('bg-secondary');
                                    window.location.reload();
                                }, 5000);
                            }

                        } catch (error) {
                            console.log(error);
                            const error_response = {
                                status: "failed",
                                message: "Something went wrong",
                            };
                            updateUIFailedStatus(error_response);
                        }
                    }

                    function updateUICompletedStatus(response) {
                        // hide the loader
                        loaderElement.addClass('hidden');
                        // change the done icon to success
                        doneElement.find('#done_pending_icon').addClass('hidden');
                        doneElement.find('#done_success_icon').removeClass('hidden');
                        doneElement.find('span').addClass('text-secondary');
                        // show the submission results
                        updateSubmissionHeader(response.status, response.message);
                        // move the progress bar to 100% and green color
                        move(0, 100);
                        barElement.removeClass('bg-secondary');
                        barElement.addClass('bg-green-400');
                        // update the submission results last element
                        const submission_results_done = $('#submission_results_done');
                        submission_results_done.children('h2').text("Status: " + response.status);
                        submission_results_done.children('p').removeClass("text-red-400");
                        submission_results_done.children('p').removeClass("text-gray-400");
                        submission_results_done.children('p').addClass("text-secondary");
                        submission_results_done.children('p').next().text("Message: " + response.message);
                        submission_results_done.children('p').next().removeClass("text-secondary");

                        console.log($('#submission_results_done').children());
                    }

                    function updateUI(response) {
                        if (response.status && response.message && response.results) {
                            // number of steps
                            number = 1;
                            // clean the submission results
                            submission_results.empty();
                            // update the submission results
                            updateSubmissionHeader(response.status, response.message);
                            // submission_status.text("Submssion Status: " + response.status); 
                            // submission_message.text("Submssion Message: " + response.message);
                            // order the steps by order
                            let arr = Object.entries(response.results);
                            arr.sort((a, b) => parseInt(a[1].order) - parseInt(b[1].order));
                            results = Object.fromEntries(arr);
                            // update the submission results first step start
                            startElement.find('#start_pending_icon').addClass('hidden');
                            startElement.find('span').addClass('text-secondary');
                            startElement.find('#start_success_icon').removeClass('hidden');
                            submission_results.append('<p class="text-center m-0 p-0">Results Summary</p>');
                            submission_results.append(
                                `<div class="text-lg text-white mt-5 border p-5 rounded-md" id="submission_results_start">
    
                                <h1 class="text-md font-bold">${number}- Start</h1>
                                <h2 class="text-xs font-semibold text-secondary-400">Status: Done</h2>
                                <p class="text-xs font-semibold">Output: Submission has been initialized and ready to process</div>`
                            );
                            // check all the steps status and update the UI
                            for (const [stepName, stepData] of Object.entries(results)) {
                                const stepElement = $(`.stepNames:contains(${stepName})`).closest('li');
                                if (stepData.status === 'completed') {
                                    stausClass = 'text-secondary';
                                    outputClass = 'break-words';
                                    stepElement.find(`#${stepData.stepID}_pending_icon`).addClass('hidden');
                                    stepElement.find(`#${stepData.stepID}_success_icon`).removeClass('hidden');
                                    stepElement.find(`#${stepData.stepID}_failed_icon`).addClass('hidden');
                                    stepElement.find('span').removeClass('text-gray-400');
                                    stepElement.find('span').removeClass('text-red-400');
                                    stepElement.find('span').addClass('text-secondary');
                                    checkResponse = true;
                                } else if (stepData.status === 'failed') {
                                    stausClass = 'text-red-400';
                                    outputClass = 'break-words';
                                    stepElement.find(`#${stepData.stepID}_pending_icon`).addClass('hidden');
                                    stepElement.find(`#${stepData.stepID}_success_icon`).addClass('hidden');
                                    stepElement.find(`#${stepData.stepID}_failed_icon`).removeClass('hidden');
                                    stepElement.find('span').removeClass('text-secondary');
                                    stepElement.find('span').removeClass('text-gray-400');
                                    stepElement.find('span').addClass('text-red-400');
                                } else {
                                    stausClass = 'text-gray-400';
                                    outputClass = 'text-gray-400 break-words';
                                    stepElement.find(`#${stepData.stepID}_pending_icon`).removeClass('hidden');
                                    stepElement.find(`#${stepData.stepID}_success_icon`).addClass('hidden');
                                    stepElement.find(`#${stepData.stepID}_failed_icon`).addClass('hidden');
                                    stepElement.find('span').removeClass('text-red-400');
                                    stepElement.find('span').removeClass('text-secondary');
                                    stepElement.find('span').addClass('text-gray-400');
                                }
                                number += 1;
                                if (stepName == 'NPM Run Tests') {
                                    testResultsDiv = ``;
                                    let totalPassedTests = 0;
                                    let totalTestsCount = 0;
                                    let hasTestMetrics = false;

                                    stepTestNames.each(function(index) {
                                        const stepTestName = $(`.stepTestNames:contains(${$(this).text()})`)
                                            .closest('li');
                                        iconID = $(this).text().replaceAll(" ", "_").replaceAll(".", "_")
                                            .replaceAll("/", "_");

                                        if (stepData.testResults[$(this).text()].status === 'completed') {
                                            teststausClass = 'text-secondary';
                                            testOutputClass = 'break-words';
                                            stepTestName.find(`#${stepData.stepID}_pending_icon_${iconID}`)
                                                .addClass('hidden');
                                            stepTestName.find(`#${stepData.stepID}_success_icon_${iconID}`)
                                                .removeClass('hidden');
                                            stepTestName.find(`#${stepData.stepID}_failed_icon_${iconID}`).addClass(
                                                'hidden');
                                            stepTestName.find('span').removeClass('text-gray-400');
                                            stepTestName.find('span').removeClass('text-red-400');
                                            stepTestName.find('span').addClass('text-secondary');
                                        } else if (stepData.testResults[$(this).text()].status === 'failed') {
                                            teststausClass = 'text-red-400';
                                            testOutputClass = 'break-words';
                                            stepTestName.find(`#${stepData.stepID}_pending_icon_${iconID}`)
                                                .addClass('hidden');
                                            stepTestName.find(`#${stepData.stepID}_success_icon_${iconID}`)
                                                .addClass('hidden');
                                            stepTestName.find(`#${stepData.stepID}_failed_icon_${iconID}`)
                                                .removeClass('hidden');
                                            stepTestName.find('span').removeClass('text-secondary');
                                            stepTestName.find('span').removeClass('text-gray-400');
                                            stepTestName.find('span').addClass('text-red-400');
                                        } else {
                                            teststausClass = 'text-gray-400';
                                            testOutputClass = 'text-gray-400 break-words';
                                            stepTestName.find(`#${stepData.stepID}_pending_icon_${iconID}`)
                                                .removeClass('hidden');
                                            stepTestName.find(`#${stepData.stepID}_success_icon_${iconID}`)
                                                .addClass('hidden');
                                            stepTestName.find(`#${stepData.stepID}_failed_icon_${iconID}`).addClass(
                                                'hidden');
                                            stepTestName.find('span').removeClass('text-red-400');
                                            stepTestName.find('span').removeClass('text-secondary');
                                            stepTestName.find('span').addClass('text-gray-400');
                                        }

                                        if (stepData.testResults[$(this).text()].passedTests !== undefined) {
                                            totalPassedTests += stepData.testResults[$(this).text()].passedTests;
                                            totalTestsCount += stepData.testResults[$(this).text()].totalTests;
                                            hasTestMetrics = true;
                                        }

                                        testResultsDiv += `
                                            <div class="text-xs mt-5 border p-5 break-words rounded-md" id="submission_results_${stepData.stepID}_${$(this).text()}">
                                                <h1 class="text-xs font-bold">${$(this).text()}</h1>
                                                <h2 class="text-xs font-semibold ${teststausClass}">Status: ${stepData.testResults[$(this).text()].status}</h2>
                                                <p class="text-xs font-semibold ${testOutputClass}">Output: ${stepData.testResults[$(this).text()].output.replaceAll("\n", "<br>").replaceAll("    ", "&emsp;")}</p>
                                            </div>
                                        `;
                                    });

                                    let testMetricsSummary = '';
                                    if (hasTestMetrics) {
                                        const metricsColor = (totalPassedTests === totalTestsCount) ? 'text-green-500' :
                                            'text-yellow-500';
                                        const progressPercent = totalTestsCount > 0 ? Math.round((totalPassedTests /
                                            totalTestsCount) * 100) : 0;
                                        const progressBarColor = progressPercent === 100 ? 'bg-green-500' : (
                                            progressPercent >= 80 ? 'bg-yellow-500' : 'bg-red-500');

                                        testMetricsSummary = `
                                        <div class="text-lg text-white mt-6 p-4 border border-gray-700 rounded-md shadow-lg">
                                            <div class="flex items-center justify-between mb-2">
                                                <div class="flex items-center">
                                                    ${progressPercent === 100 ? 
                                                      '<svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>' : 
                                                      '<svg class="w-5 h-5 mr-2 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>'}
                                                    <h3 class="text-md font-bold ${metricsColor}">Test Summary</h3>
                                                </div>
                                                <span class="text-sm font-bold ${metricsColor}">${progressPercent}%</span>
                                            </div>
                                            <div class="w-full bg-gray-700 rounded-full h-2.5 mb-4">
                                                <div class="h-2.5 rounded-full ${progressBarColor} transition-all duration-500" style="width: ${progressPercent}%"></div>
                                            </div>
                                            <div class="grid grid-cols-3 gap-2 text-sm font-medium">
                                                <div class="flex flex-col items-center p-2 bg-gray-800 rounded-md">
                                                    <span class="text-green-500 font-bold">${totalPassedTests}</span>
                                                    <span class="text-xs text-gray-400">Passed</span>
                                                </div>
                                                <div class="flex flex-col items-center p-2 bg-gray-800 rounded-md">
                                                    <span class="text-red-500 font-bold">${totalTestsCount - totalPassedTests}</span>
                                                    <span class="text-xs text-gray-400">Failed</span>
                                                </div>
                                                <div class="flex flex-col items-center p-2 bg-gray-800 rounded-md">
                                                    <span class="text-blue-500 font-bold">${totalTestsCount}</span>
                                                    <span class="text-xs text-gray-400">Total</span>
                                                </div>
                                            </div>
                                        </div>`;
                                    }

                                    submission_results.append(`<div class="text-lg text-white mt-5 border p-5 rounded-md" id="submission_results_${stepData.stepID}">
                                <h1 class="text-md font-bold">${number}- ${stepName}</h1>
                                <h2 class="text-xs font-semibold ${stausClass}">Status: ${stepData.status}</h2>
                                <p class="text-xs font-semibold ${outputClass}">Output: ${stepData.output}</p>
                                <p class="text-xs font-semibold">Test Results:</p>
                                ${testResultsDiv}
                                ${testMetricsSummary}
                                </div>`);
                                } else {
                                    submission_results.append(`<div class="text-lg text-white mt-5 border p-5 rounded-md" id="submission_results_${stepData.stepID}">
                                <h1 class="text-md font-bold">${number}- ${stepName}</h2>
                                <h2 class="text-xs font-semibold ${stausClass}">Status: ${stepData.status}</h2>
                                <p class="text-xs font-semibold ${outputClass}">Output: ${stepData.output}</p>
                                </div>`);
                                }
                            }
                            // add the last step done
                            statusClasses = '';
                            if (response.status === 'completed') {
                                statusClasses = 'text-secondary';
                            } else if (response.status === 'failed') {
                                statusClasses = 'text-red-400';
                            } else if (response.status === 'processing') {
                                statusClasses = 'text-gray-400';
                            } else if (response.status === 'pending') {
                                statusClasses = 'text-gray-400';
                            }
                            doneStep = ` <div class="text-lg text-white mt-5 border p-5 rounded-md" id="submission_results_done">
                        <h1 class="text-md font-bold">${number}- Done</h1>
                        <h2 class="text-xs font-semibold ${statusClasses}">Status: ${response.status}</h2>
                        <p class="text-xs font-semibold ${statusClasses}">Message: ${response.message}</p>
                        </div>`;
                            submission_results.append(doneStep);
                        }
                    }

                    function updateSubmissionHeader(status, message) {
                        submission_status.text("Submssion Status: ");
                        submission_message.text("Submssion Message: " + message);
                        let statusClass = '';
                        if (status === 'completed') {
                            statusClass = 'bg-green-100 text-green-800';
                        } else if (status === 'failed') {
                            statusClass = 'bg-red-100 text-red-800';
                        } else if (status === 'processing') {
                            statusClass = 'bg-secondary-100 text-secondary-800';
                        } else if (status === 'pending') {
                            statusClass = 'bg-blue-100 text-blue-800';
                        } else if (status === 'Wait') {
                            statusClass = 'bg-blue-100 text-blue-800';
                        }
                        status = status.charAt(0).toUpperCase() + status.slice(1);
                        submission_status.append(`<span class="inline-flex items-center justify-center px-2 py-1 rounded-lg text-md font-bold leading-none ${statusClass}">
                    ${status}
                </span>`);
                    }
                });
            </script>
        @endsection
    @endif
</x-app-layout>
