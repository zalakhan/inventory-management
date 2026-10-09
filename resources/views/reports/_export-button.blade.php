@can('reports.export')
    <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}"
        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm rounded-lg hover:bg-gray-50 shrink-0">
        Export CSV
    </a>
@endcan
