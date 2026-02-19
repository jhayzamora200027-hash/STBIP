{{-- Debug: Show region and count --}}
@php
    $filteredData = array_filter($filteredData, function($row) {
        return !empty($row['title']) && trim($row['title']) !== '';
    });
    $filteredCount = count($filteredData);
    $rows = array_slice($filteredData, 0, 2);
    $allRegions = collect($filteredData)->pluck('region')->unique()->values()->all();
@endphp

@php
    $filteredData = array_filter($filteredData, function($row) {
        return !empty($row['title']) && trim($row['title']) !== '';
    });
@endphp
@forelse($filteredData as $row)
    <tr>
        <td>{{ \Illuminate\Support\Str::limit($row['title'] ?? '-', 60) }}</td>
        <td>{{ \Illuminate\Support\Str::limit($row['province'] ?? '-', 30) }}</td>
        <td>{{ \Illuminate\Support\Str::limit($row['municipality'] ?? '-', 30) }}</td>
    </tr>
@empty
    <tr>
        <td colspan="4" class="text-center">No ST Implemented for this region.</td>
    </tr>
@endforelse
