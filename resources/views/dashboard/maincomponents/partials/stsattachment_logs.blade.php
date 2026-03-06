<div>
    <form id="sts-logs-filter-form" class="row g-2 mb-3">
        <div class="col-auto">
            <label class="form-label form-label-sm" for="from_date">From</label>
            <input type="date" class="form-control form-control-sm" name="from_date" id="from_date" value="{{ request('from_date') }}">
        </div>
        <div class="col-auto">
            <label class="form-label form-label-sm" for="to_date">To</label>
            <input type="date" class="form-control form-control-sm" name="to_date" id="to_date" value="{{ request('to_date') }}">
        </div>
        <div class="col-auto align-self-end">
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
        </div>
    </form>

    <table class="table table-sm table-bordered" style="font-size:0.85rem;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Region</th>
                <th>Province</th>
                <th>Municipality</th>
                <th>Title</th>
                <th>Year</th>
                <th>Action</th>
                <th>By</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($logs) && $logs->count() > 0)
                @foreach($logs as $log)
                    <tr>
                        <td>{{ $log->id }}</td>
                        <td>{{ $log->region }}</td>
                        <td>{{ $log->province }}</td>
                        <td>{{ $log->municipality }}</td>
                        <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $log->title }}</td>
                        <td>{{ $log->year_of_moa }}</td>
                        <td class="text-capitalize">{{ $log->action }}</td>
                        <td>{{ $log->created_by }}</td>
                        <td>{{ $log->created_at }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="9" class="text-center text-muted">No log entries found.</td>
                </tr>
            @endif
        </tbody>
    </table>

    @if($logs instanceof \Illuminate\Pagination\LengthAwarePaginator && $logs->hasPages())
        @php
            $currentPage = $logs->currentPage();
            $lastPage = $logs->lastPage();
        @endphp
        <style>
            .st-custom-pagination { display: flex; justify-content: center; align-items: center; gap: 14px; background: #f8fafc; border-radius: 12px; box-shadow: 0 2px 8px #b2ebf2; margin-top: 18px; padding: 10px 0 6px 0; }
            .st-custom-pagination-btn { border: none; background: linear-gradient(90deg, #4da1f7 60%, #4da1f7 100%); color: #fff; font-weight: 700; border-radius: 8px; padding: 7px 26px; font-size: 1.08em; box-shadow: 0 2px 8px #b2ebf2; outline: none; transition: background 0.18s, box-shadow 0.18s, transform 0.12s; cursor: pointer; position: relative; }
            .st-custom-pagination-btn:disabled { background: #e0f7fa; color: #b0b0b0; box-shadow: none; cursor: not-allowed; }
            .st-custom-pagination-btn:not(:disabled):hover { background: linear-gradient(90deg, #4da1f7 60%, #4da1f7 100%); transform: translateY(-2px) scale(1.04); box-shadow: 0 4px 16px #b2ebf2; }
            .st-custom-pagination-indicator { font-weight: 600; color: #4da1f7; font-size: 1.13em; min-width: 110px; text-align: center; letter-spacing: 0.5px; }
        </style>
        <div class="st-custom-pagination">
            <button type="button" class="st-custom-pagination-btn" @if($logs->onFirstPage()) disabled @else onclick="loadStsLogsPage('{{ $logs->previousPageUrl() }}')" @endif>&#8592; Prev</button>
            <span class="st-custom-pagination-indicator">Page {{ $currentPage }} of {{ $lastPage }}</span>
            <button type="button" class="st-custom-pagination-btn" @if(!$logs->hasMorePages()) disabled @else onclick="loadStsLogsPage('{{ $logs->nextPageUrl() }}')" @endif>Next &#8594;</button>
        </div>
    @endif
</div>