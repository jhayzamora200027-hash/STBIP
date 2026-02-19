<div class="st-title-listing-scroll">
    <table class="table table-bordered table-striped align-middle mb-0 st-title-listing-table">
        <thead style="background:linear-gradient(90deg,#10aeb5 60%,#1de9b6 100%);color:#fff;">
            <tr>
                <th style="width:340px;max-width:340px;">Title</th>
                <th style="width:180px;max-width:180px;">Province</th>
                <th style="width:180px;max-width:180px;">City/Municipality</th>
            </tr>
        </thead>
        <tbody>
            @php
                $listingData = collect($data)->filter(function($row){
                    return stripos($row['region'], 'Data CY 2020-2022') === false && !empty($row['title']);
                });
                $perPage = 10;
                $currentPage = request()->input('listing_page', 1);
                $paged = $listingData->slice(($currentPage-1)*$perPage, $perPage);
            @endphp
            @forelse($paged as $row)
                <tr>
                    <td title="{{ $row['title'] }}">{{ Str::limit($row['title'], 60) }}</td>
                    <td title="{{ $row['province'] ?? '-' }}">{{ Str::limit($row['province'] ?? '-', 30) }}</td>
                    <td title="{{ $row['municipality'] ?? '-' }}">{{ Str::limit($row['municipality'] ?? '-', 30) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center">No data found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@php
    $total = $listingData->count();
    $totalPages = ceil($total / $perPage);
@endphp
@if($totalPages > 1)
<div class="st-custom-pagination">
    <a class="st-custom-pagination-btn{{ $currentPage == 1 ? ' disabled' : '' }} ajax-pagination" href="{{ $currentPage == 1 ? '#' : request()->fullUrlWithQuery(['listing_page' => $currentPage-1]) }}" {{ $currentPage == 1 ? 'tabindex="-1" aria-disabled="true"' : '' }}>Prev</a>
    <span class="st-custom-pagination-indicator">Page {{ $currentPage }} of {{ $totalPages }}</span>
    <a class="st-custom-pagination-btn{{ $currentPage == $totalPages ? ' disabled' : '' }} ajax-pagination" href="{{ $currentPage == $totalPages ? '#' : request()->fullUrlWithQuery(['listing_page' => $currentPage+1]) }}" {{ $currentPage == $totalPages ? 'tabindex="-1" aria-disabled="true"' : '' }}>Next</a>
</div>
@endif
