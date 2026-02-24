<div class="mt-5">
    <h2 class="mb-3">Uploaded Files</h2>


    <form id="selectBaseExcelForm" action="{{ route('excel.setBase') }}" method="POST">
        @csrf
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th></th>
                    <th>Doc No</th>
                    <th>Date Uploaded</th>
                    <th>Uploaded By</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $hasLogs = isset($logs) && $logs && $logs->count() > 0;
                @endphp

                @if($hasLogs)
                    @foreach($logs as $log)
                        <tr>
                            <td>
                                <input type="radio" name="base_excel" value="{{ $log->stored_filename }}"
                                    @if(session('base_excel') == $log->stored_filename) checked @endif>
                            </td>
                            <td>{{ $log->docno }}</td>
                            <td>{{ $log->created_at }}</td>
                            <td>{{ $log->createdby }}</td>
                        </tr>
                    @endforeach

                    @php
                        $perPage = method_exists($logs, 'perPage') ? $logs->perPage() : 10;
                        $remaining = max($perPage - $logs->count(), 0);
                    @endphp

                    @for($i = 0; $i < $remaining; $i++)
                        <tr>
                            <td></td>
                            <td>&nbsp;</td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endfor
                @else
                    <tr>
                        <td colspan="4" class="text-center">No Excel files available. Please upload a file first.</td>
                    </tr>
                @endif
            </tbody>
        </table>
        @if(isset($logs) && $logs instanceof \Illuminate\Pagination\LengthAwarePaginator && $logs->hasPages())
            @php
                $currentPage = $logs->currentPage();
                $lastPage = $logs->lastPage();
            @endphp
            <style>
                .st-custom-pagination { display: flex; justify-content: center; align-items: center; gap: 14px; background: #f8fafc; border-radius: 12px; box-shadow: 0 2px 8px #b2ebf2; margin-top: 18px; padding: 10px 0 6px 0; }
                .st-custom-pagination-btn { border: none; background: linear-gradient(90deg, #10aeb5 60%, #1de9b6 100%); color: #fff; font-weight: 700; border-radius: 8px; padding: 7px 26px; font-size: 1.08em; box-shadow: 0 2px 8px #b2ebf2; outline: none; transition: background 0.18s, box-shadow 0.18s, transform 0.12s; cursor: pointer; position: relative; }
                .st-custom-pagination-btn:disabled { background: #e0f7fa; color: #b0b0b0; box-shadow: none; cursor: not-allowed; }
                .st-custom-pagination-btn:not(:disabled):hover { background: linear-gradient(90deg, #1de9b6 60%, #10aeb5 100%); transform: translateY(-2px) scale(1.04); box-shadow: 0 4px 16px #b2ebf2; }
                .st-custom-pagination-indicator { font-weight: 600; color: #10aeb5; font-size: 1.13em; min-width: 110px; text-align: center; letter-spacing: 0.5px; }
            </style>
            <div class="st-custom-pagination">
                <button type="button" class="st-custom-pagination-btn" @if($logs->onFirstPage()) disabled @else onclick="loadUploadLogsPage('{{ $logs->previousPageUrl() }}')" @endif>&#8592; Prev</button>
                <span class="st-custom-pagination-indicator">Page {{ $currentPage }} of {{ $lastPage }}</span>
                <button type="button" class="st-custom-pagination-btn" @if(!$logs->hasMorePages()) disabled @else onclick="loadUploadLogsPage('{{ $logs->nextPageUrl() }}')" @endif>Next &#8594;</button>
            </div>
        @endif
        <div class="mt-3">
            <div class="mt-3">
                <button type="button" class="btn btn-success" id="updateBaseExcelBtnTop" onclick="updateBaseExcel()" @if(!isset($logs) || count($logs) == 0) disabled @endif>
                    Update Base File
                </button>
            </div>
            @if(isset($latestUpdatedBy) || isset($latestActionLog))
                <div class="row g-3">
                    <div class="col-md-4">
                        <span class="form-label d-block mb-1">Latest Updated By</span>
                        <span class="fw-semibold">{{ $latestUpdatedBy ?? '-' }}</span>
                    </div>
                    <div class="col-md-4">
                        <span class="form-label d-block mb-1">Action</span>
                        <span class="fw-semibold text-capitalize">{{ $latestActionLog ?? '-' }}</span>
                    </div>
                    <div class="col-md-4">
                        <span class="form-label d-block mb-1">Doc No</span>
                        <span class="fw-semibold">{{ isset($latestSelection) ? $latestSelection->docselected : '-' }}</span>
                    </div>
                </div>
            @endif
        </div>
    </form>
</div>

{{-- history of selection logs --}}
@if(isset($selectLogs) && $selectLogs->count() > 0)
    <!-- modal containing history table, mimic STs logs modal classes -->
    <div class="modal fade" id="selectionHistoryModal" tabindex="-1" aria-labelledby="selectionHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="selectionHistoryModalLabel">Base File Selection History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height:70vh; overflow-y:auto;">
                    {{-- filter controls for modal history --}}
                    <form id="select-logs-filter-form" class="row g-2 mb-3">
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
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Action</th>
                                <th>File</th>
                                <th>Doc No</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($selectLogs as $slog)
                                <tr>
                                    <td>{{ $slog->updated_at }}</td>
                                    <td class="text-capitalize">{{ $slog->actionlogs }}</td>
                                    <td>{{ $slog->excelname }}</td>
                                    <td>{{ $slog->docselected ?? '-' }}</td>
                                    <td>{{ $slog->createdby }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endif
@if(request()->filled('from_date') || request()->filled('to_date'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modalEl = document.getElementById('selectionHistoryModal');
            if (modalEl) {
                var m = new bootstrap.Modal(modalEl);
                m.show();
            }
        });
    </script>
@endif