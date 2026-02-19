<table class="table table-bordered table-striped align-middle mb-0 st-title-listing-table">
    <thead style="background:linear-gradient(90deg,#10aeb5 60%,#1de9b6 100%);color:#fff;">
        <tr>
            <th style="width:340px;max-width:340px;">Title</th>
            <th style="width:180px;max-width:180px;">Province</th>
            <th style="width:180px;max-width:180px;">City/Municipality</th>
        </tr>
    </thead>
    <tbody>
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
@php
    $totalPages = ceil($total / $perPage);
@endphp
@if($totalPages > 1)
<div class="st-custom-pagination">
    <a class="st-custom-pagination-btn ajax-listing-page{{ $currentPage == 1 ? ' disabled' : '' }}" href="#" data-page="{{ $currentPage > 1 ? $currentPage-1 : 1 }}" {{ $currentPage == 1 ? 'tabindex=-1 aria-disabled=true' : '' }}>Prev</a>
    <span class="st-custom-pagination-indicator">Page {{ $currentPage }} of {{ $totalPages }}</span>
    <a class="st-custom-pagination-btn ajax-listing-page{{ $currentPage == $totalPages ? ' disabled' : '' }}" href="#" data-page="{{ $currentPage < $totalPages ? $currentPage+1 : $totalPages }}" {{ $currentPage == $totalPages ? 'tabindex=-1 aria-disabled=true' : '' }}>Next</a>
</div>
@endif
