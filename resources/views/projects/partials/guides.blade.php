<style>
    .pdfobject-container {
        height: 50rem;
        border: 1rem solid rgba(0, 0, 0, .1);
    }
</style>
<div class="p-6 text-gray-900 dark:text-gray-100">
    <div class="mb-3 py-2 border-b w-fit">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Poject Guides') }}
        </h2>
    </div>
    <div id="grid" class="grid grid-col-1 md:grid-cols-2 gap-4 py-2 items-center">
        <div class="mb-20">
            {{-- list of guides --}}
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <caption
                    class="p-5 text-lg font-semibold text-left text-gray-900 bg-white dark:text-white dark:bg-gray-800">
                    {{$project->title}} - Guides
                    <p class="mt-1 text-sm font-normal text-gray-500 dark:text-gray-400">Here is the the
                        list of PDF guide files </p>
                    <p class="mt-1 text-sm font-normal text-gray-500 dark:text-gray-400 float-right">Total
                        Amount: {{count($project->getMedia('project_guides'))}}</p>
                </caption>
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">NO#</th>
                        <th scope="col" class="px-6 py-3">Name</th>
                        <th scope="col" class="px-6 py-3"><span class="sr-only">View</span></th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $no = 1;
                    @endphp
                    @forelse ($project->getMedia('project_guides') as $item)
                    @php
                    $no += 1;
                    @endphp
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                            {{$no}}
                        </td>
                        <td scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            <span class="text-gray-50 rounded-md bg-secondary p-1 text-md">
                                {{$item->file_name}}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            {{-- dropdown --}}
                            <x-dropdown align="left" width="48">
                                <x-slot name="trigger">
                                    <button
                                        class="flex items-center text-sm font-medium text-gray-900 hover:text-gray-500 dark:text-white dark:hover:text-gray-300 hover:underline">
                                        <svg class="ml-1 h-5 w-5 text-gray-500 dark:text-gray-400"
                                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                            aria-hidden="true">
                                            <g id="Menu / Menu_Alt_02">
                                                <path id="Vector" d="M11 17H19M5 12H19M11 7H19" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </g>
                                        </svg>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link onclick="requestPDF({{$item->id}})">View
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="$item->getUrl()" target="_blank">Open in a new tab
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="$item->getUrl()" download="{{$item->file_name}}">Download
                                    </x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                        </td>
                    </tr>
                    @empty
                    <x-not-found message="No Guides Found" />
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>
            {{-- PDF Preview --}}
            <div id="pdf-viewer">
                <x-not-found message="Click View To Open PDF" />
            </div>
        </div>
    </div>
</div>
@section('scripts')
<script>
    function requestPDF(id){
        $.ajax({
            url: '/projects/pdf',
            type: 'GET',
            data: {id: id},
            success: function (data) {
                $('#grid').removeClass('items-center');
                PDFObject.embed(data, "#pdf-viewer");
            },
            error: function (data) {
                $('#grid').addClass('items-center');
                $('#pdf-viewer').removeClass('pdfobject-container');
                $('#pdf-viewer').empty();
                $('#pdf-viewer').html(`<x-not-found message="An Error Occured" />`);
            }
        });
    }
</script>
@endsection