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
                    <div class="col-md-6">
                        <span class="form-label d-block mb-1">Latest Updated By</span>
                        <span class="fw-semibold">{{ $latestUpdatedBy ?? '-' }}</span>
                    </div>
                    <div class="col-md-6">
                        <span class="form-label d-block mb-1">Action</span>
                        <span class="fw-semibold text-capitalize">{{ $latestActionLog ?? '-' }}</span>
                    </div>
                </div>
            @endif
        </div>
    </form>
</div>
