{{-- STs MOA Attachment table + custom pagination (AJAX-ready) --}}
@php
    $total = $sts instanceof \Illuminate\Pagination\LengthAwarePaginator ? $sts->total() : count($sts);
@endphp

<div class="d-flex justify-content-between align-items-center mb-2">
    <div class="text-muted" style="font-size: 0.8rem;">
        Showing <strong>{{ $sts->count() }}</strong> of <strong>{{ $total }}</strong> STs
        @if(!empty($selectedRegion))
            in <span class="badge bg-light text-primary border">{{ $selectedRegion }}</span>
        @endif
        @if(!empty($searchTitle))
            matching
            <span class="badge bg-primary-subtle text-primary" style="background:#e0ecff;color:#1d4ed8;">"{{ $searchTitle }}"</span>
        @endif
    </div>
    <div class="badge rounded-pill" style="background: #e0f2fe; color:#0369a1; font-size:0.75rem;">
        Only STs with MOA and Year of MOA are listed
    </div>
</div>

<div class="table-responsive" style="border-radius: 14px; overflow: hidden; box-shadow: 0 4px 14px rgba(15,23,42,0.06);">
    <table class="table mb-0" style="table-layout: fixed; width: 100%; font-size: 0.82rem;">
        <thead style="background: linear-gradient(90deg,#0f766e 0%,#0ea5e9 60%,#38bdf8 100%); color:#fff;">
            <tr style="font-size:0.9rem;">
                <th style="width: 9%; min-width:9%; max-width: 27%; max-height: 2px;">Region</th>
                <th style="width: 18%; min-width:18%; max-width: 36%; max-height: 2px;">Province</th>
                <th style="width: 18%; min-width:18%; max-width: 36%; max-height: 2px;">City/Municipality</th>
                <th style="width: 36%; min-width:36%; max-width: 72%; max-height: 2px;">Title of ST</th>
                <th class="text-center" style="width: 8%; min-width:8%; max-width: 24%; max-height: 2px;">Year of MOA</th>
                <th class="text-center" style="width: 11%; min-width:11%; max-width: 33%; max-height: 2px;">Attachment</th>
            </tr>
        </thead>
        <tbody style="background:#ffffff;">
            @forelse($sts as $row)
                <tr style="height: 44px;">
                    <td class="fw-semibold text-primary">{{ $row['region'] }}</td>
                    <td>{{ $row['province'] }}</td>
                    <td>{{ $row['municipality'] }}</td>
                    <td style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $row['title'] }}</td>
                    <td class="text-center">
                        @if(!empty($row['year_of_moa']))
                            <span class="badge rounded-pill" style="background:#eff6ff;color:#1d4ed8;min-width:56px;">{{ $row['year_of_moa'] }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="d-inline-flex align-items-center gap-1">
                            @if(!empty($row['attachment_url'] ?? null) && !empty($row['attachment_id'] ?? null))
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-success btn-view-st-attachment"
                                    title="View uploaded attachment"
                                    data-url="{{ $row['attachment_url'] }}"
                                    data-title="{{ $row['title'] }}"
                                    data-uploader="{{ $row['attachment_uploaded_by'] ?? '' }}"
                                >
                                    <i class="bi bi-filetype-pdf"></i>
                                </button>
                                <form
                                    method="POST"
                                    action="{{ route('sts.attachments.destroy', $row['attachment_id']) }}"
                                    onsubmit="return confirm('Are you sure you want to delete this attachment?');"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete attachment">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @else
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary btn-upload-st-attachment"
                                    title="Upload attachment for this ST"
                                    data-region="{{ $row['region'] }}"
                                    data-province="{{ $row['province'] }}"
                                    data-municipality="{{ $row['municipality'] }}"
                                    data-title="{{ $row['title'] }}"
                                    data-year="{{ $row['year_of_moa'] }}"
                                >
                                    <i class="bi bi-paperclip"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">No ST records found for the selected region.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Upload Attachment Modal --}}
<div class="modal fade" id="stAttachmentModal" tabindex="-1" aria-labelledby="stAttachmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('sts.attachments.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="stAttachmentModalLabel">Upload Attachment for ST</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2 small text-muted" id="stAttachmentSummary"></div>
                    <input type="hidden" name="region" id="stAttachmentRegion">
                    <input type="hidden" name="province" id="stAttachmentProvince">
                    <input type="hidden" name="municipality" id="stAttachmentMunicipality">
                    <input type="hidden" name="title" id="stAttachmentTitle">
                    <input type="hidden" name="year_of_moa" id="stAttachmentYear">

                    <div class="mb-3">
                        <label for="stAttachmentFile" class="form-label">Select PDF file</label>
                        <input type="file" class="form-control" id="stAttachmentFile" name="attachment" accept="application/pdf" required>
                        <div class="form-text">PDF only, max size 10MB.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- View Attachment Modal --}}
<div class="modal fade" id="viewAttachmentModal" tabindex="-1" aria-labelledby="viewAttachmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center w-100 justify-content-between">
                    <h5 class="modal-title mb-1 mb-md-0" id="viewAttachmentModalLabel">View Attachment</h5>
                    <span class="badge bg-light text-muted" id="viewAttachmentUploadedBy" style="font-size:0.8rem; display:none;">Uploaded by:</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="height: 80vh;">
                <iframe id="viewAttachmentFrame" src="" style="width: 100%; height: 100%; border: none;" title="ST Attachment PDF"></iframe>
            </div>
        </div>
    </div>
    
</div>

<script>
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-upload-st-attachment');
        if (!btn) return;

        var modalEl = document.getElementById('stAttachmentModal');
        if (!modalEl) return;

        document.getElementById('stAttachmentRegion').value = btn.getAttribute('data-region') || '';
        document.getElementById('stAttachmentProvince').value = btn.getAttribute('data-province') || '';
        document.getElementById('stAttachmentMunicipality').value = btn.getAttribute('data-municipality') || '';
        document.getElementById('stAttachmentTitle').value = btn.getAttribute('data-title') || '';
        document.getElementById('stAttachmentYear').value = btn.getAttribute('data-year') || '';

        var summary = document.getElementById('stAttachmentSummary');
        if (summary) {
            summary.textContent = (btn.getAttribute('data-title') || '') + ' — ' +
                (btn.getAttribute('data-province') || '') + ', ' +
                (btn.getAttribute('data-municipality') || '');
        }

        var modal = new bootstrap.Modal(modalEl);
        modal.show();
    });

    document.addEventListener('click', function (e) {
        var viewBtn = e.target.closest('.btn-view-st-attachment');
        if (!viewBtn) return;

        var url = viewBtn.getAttribute('data-url');
        if (!url) return;

        var viewModalEl = document.getElementById('viewAttachmentModal');
        var frame = document.getElementById('viewAttachmentFrame');
        var titleEl = document.getElementById('viewAttachmentModalLabel');
        var uploaderEl = document.getElementById('viewAttachmentUploadedBy');

        if (frame) {
            frame.src = url;
        }
        if (titleEl) {
            var stTitle = viewBtn.getAttribute('data-title') || 'View Attachment';
            titleEl.textContent = stTitle;
        }
        if (uploaderEl) {
            var uploadedBy = viewBtn.getAttribute('data-uploader') || '';
            if (uploadedBy) {
                uploaderEl.textContent = 'Uploaded by: ' + uploadedBy;
                uploaderEl.style.display = 'inline-block';
            } else {
                uploaderEl.textContent = '';
                uploaderEl.style.display = 'none';
            }
        }

        if (viewModalEl) {
            var viewModal = new bootstrap.Modal(viewModalEl);
            viewModal.show();
        }
    });
</script>
@if(Auth::user() && in_array(Auth::user()->usergroup, ['admin', 'sysadmin']))
{{-- Logs modal (only shown when admin/sysadmin button is clicked) --}}
<div class="modal fade" id="stsLogsModal" tabindex="-1" aria-labelledby="stsLogsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stsLogsModalLabel">STs Manager Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height:70vh; overflow-y:auto;" id="sts-logs-container">
                {{-- AJAX-loaded logs will appear here --}}
            </div>
        </div>
    </div>
</div>
@endif
@if($sts instanceof \Illuminate\Pagination\LengthAwarePaginator && $sts->hasPages())
    @php
        $currentPage = $sts->currentPage();
        $lastPage = $sts->lastPage();
    @endphp
    <style>
        .st-custom-pagination { display: flex; justify-content: center; align-items: center; gap: 14px; background: #f8fafc; border-radius: 12px; box-shadow: 0 2px 8px #b2ebf2; margin-top: 18px; padding: 10px 0 6px 0; }
        .st-custom-pagination-btn { border: none; background: linear-gradient(90deg, #4da1f7 60%, #4da1f7 100%); color: #fff; font-weight: 700; border-radius: 8px; padding: 7px 26px; font-size: 1.08em; box-shadow: 0 2px 8px #b2ebf2; outline: none; transition: background 0.18s, box-shadow 0.18s, transform 0.12s; cursor: pointer; position: relative; }
        .st-custom-pagination-btn:disabled { background: #e0f7fa; color: #b0b0b0; box-shadow: none; cursor: not-allowed; }
        .st-custom-pagination-btn:not(:disabled):hover { background: linear-gradient(90deg, #4da1f7 60%, #4da1f7 100%); transform: translateY(-2px) scale(1.04); box-shadow: 0 4px 16px #b2ebf2; }
        .st-custom-pagination-indicator { font-weight: 600; color: #4da1f7; font-size: 1.13em; min-width: 110px; text-align: center; letter-spacing: 0.5px; }
    </style>
    <div class="st-custom-pagination">
        <button type="button" class="st-custom-pagination-btn" @if($sts->onFirstPage()) disabled @else onclick="loadUploadStsPage('{{ $sts->previousPageUrl() }}')" @endif>&#8592; Prev</button>
        <span class="st-custom-pagination-indicator">Page {{ $currentPage }} of {{ $lastPage }}</span>
        <button type="button" class="st-custom-pagination-btn" @if(!$sts->hasMorePages()) disabled @else onclick="loadUploadStsPage('{{ $sts->nextPageUrl() }}')" @endif>Next &#8594;</button>
    </div>
@endif
