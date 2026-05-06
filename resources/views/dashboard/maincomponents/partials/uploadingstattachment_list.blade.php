@php
    $total = $sts instanceof \Illuminate\Pagination\LengthAwarePaginator ? $sts->total() : count($sts);
@endphp

<div class="d-flex justify-content-between align-items-center mb-2">
    <div class="text-muted" style="font-size: 0.8rem;">
        Showing <strong>{{ $sts->count() }}</strong> of <strong>{{ $total }}</strong> STs
        @if(!empty($selectedRegion))
            in <span class="badge bg-light text-primary border">{{ $selectedRegion }}</span>
        @endif
        @if(!empty($selectedProvince))
            <span class="badge bg-light text-info border ms-1">{{ $selectedProvince }}</span>
        @endif
        @if(!empty($selectedCity))
            <span class="badge bg-light text-secondary border ms-1">{{ $selectedCity }}</span>
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

<div class="table-responsive st-moa-table-wrapper" style="border-radius: 14px; overflow: hidden; box-shadow: 0 4px 14px rgba(15,23,42,0.06);">
    <table class="table mb-0 st-moa-table" style="table-layout: fixed; width: 100%; font-size: 0.82rem;">
        <thead style="background: linear-gradient(90deg,#0f766e 0%,#0ea5e9 60%,#38bdf8 100%); color:#fff;">
            <tr style="font-size:0.9rem;">
                <th class="st-col-region" style="width: 9%; min-width:9%; max-width: 27%; max-height: 2px;">Region</th>
                <th class="st-col-province" style="width: 18%; min-width:18%; max-width: 36%; max-height: 2px;">Province</th>
                <th class="st-col-city" style="width: 18%; min-width:18%; max-width: 36%; max-height: 2px;">City/Municipality</th>
                <th class="st-col-title" style="width: 36%; min-width:36%; max-width: 72%; max-height: 2px;">Title of ST</th>
                <th class="text-center st-col-year" style="width: 8%; min-width:8%; max-width: 24%; max-height: 2px;">Year of MOA</th>
                <th class="text-center st-col-attachment" style="width: 11%; min-width:11%; max-width: 33%; max-height: 2px;">Attachment</th>
            </tr>
        </thead>
        <tbody style="background:#ffffff;">
            @forelse($sts as $row)
                <tr style="height: 44px;">
                    <td class="fw-semibold text-primary st-col-region">{{ $row['region'] }}</td>
                    <td class="st-col-province">{{ $row['province'] }}</td>
                    <td class="st-col-city">{{ $row['municipality'] }}</td>
                    <td class="st-col-title" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $row['title'] }}</td>
                    <td class="text-center st-col-year">
                        @if(!empty($row['year_of_moa']))
                            <span class="badge rounded-pill" style="background:#eff6ff;color:#1d4ed8;min-width:56px;">{{ $row['year_of_moa'] }}</span>
                        @endif
                    </td>
                    <td class="text-center st-col-attachment">
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
                                    action="{{ route('sts.attachments.destroy', ['attachment' => $row['attachment_id']]) }}"
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
                    <td colspan="6" class="text-center text-muted">No ST records found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Upload Attachment Modal --}}
<div class="modal fade" id="stAttachmentModal" tabindex="-1" aria-labelledby="stAttachmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="stAttachmentForm" class="ajax-form" method="POST" action="{{ route('sts.attachments.store') }}" enctype="multipart/form-data">
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
                        <div class="form-text">PDF only, max size 30MB.</div>
                        <div class="progress mt-2" id="stAttachmentProgress" style="height:6px; display:none;">
                            <div class="progress-bar" role="progressbar" style="width:0%" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="stAttachmentSubmitBtn">Upload</button>
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

        // store reference to the button that opened the modal for popover feedback
        window._lastSTUploadButton = btn;

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

<!-- Confirm Delete Modal (reusable) -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="confirmDeleteMessage">Are you sure you want to delete this attachment?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var confirmModalEl = document.getElementById('confirmDeleteModal');
    var confirmModal = confirmModalEl ? new bootstrap.Modal(confirmModalEl) : null;
    var deleteTarget = null; // { formEl, url, wrapper }

    // Delegate click for delete buttons inside .attachment-delete-form
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.attachment-delete-form button');
        if (!btn) return;
        e.preventDefault();
        var form = btn.closest('.attachment-delete-form');
        if (!form) return;
        var action = form.getAttribute('action');
        deleteTarget = { formEl: form, url: action, wrapper: form.parentElement };
        // show modal
        if (confirmModal) confirmModal.show();
    });

    // Confirm delete action
    var confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function () {
            if (!deleteTarget || !deleteTarget.url) {
                if (confirmModal) confirmModal.hide();
                return;
            }
            var url = deleteTarget.url;
            var wrapper = deleteTarget.wrapper;
            var token = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';

            // Send AJAX DELETE
            var xhr = new XMLHttpRequest();
            xhr.open('POST', url, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('X-CSRF-TOKEN', token);
            xhr.setRequestHeader('Accept', 'application/json');

            xhr.onload = function () {
                if (xhr.status >= 200 && xhr.status < 300) {
                    var data = {};
                    try { data = JSON.parse(xhr.responseText); } catch (e) {}
                    // on success, replace wrapper content with upload button again
                    try {
                        if (wrapper) {
                            var region = wrapper.getAttribute('data-region') || '';
                            var province = wrapper.getAttribute('data-province') || '';
                            var municipality = wrapper.getAttribute('data-municipality') || '';
                            var title = wrapper.getAttribute('data-title') || '';
                            var year = wrapper.getAttribute('data-year') || '';
                            var uploadBtn = '<button type="button" class="btn btn-sm btn-outline-primary btn-upload-st-attachment" title="Upload attachment for this ST" data-region="' + (region.replace(/"/g,'\"')) + '" data-province="' + (province.replace(/"/g,'\"')) + '" data-municipality="' + (municipality.replace(/"/g,'\"')) + '" data-title="' + (title.replace(/"/g,'\"')) + '" data-year="' + (year.replace(/"/g,'\"')) + '"><i class="bi bi-paperclip"></i></button>';
                            wrapper.innerHTML = uploadBtn;
                        }
                    } catch (e) { console.log('replace wrapper failed', e); }

                    // show popover on replaced upload button if possible
                    try {
                        var newOpener = wrapper ? wrapper.querySelector('.btn-upload-st-attachment') : null;
                        if (newOpener) {
                            var msg = (data && data.message) ? data.message : 'Attachment deleted';
                            var pop = new bootstrap.Popover(newOpener, {content: msg, trigger: 'manual', placement: 'top'});
                            pop.show(); setTimeout(function () { pop.hide(); pop.dispose(); }, 2500);
                        }
                    } catch (e) { }

                    if (confirmModal) confirmModal.hide();
                    deleteTarget = null;
                } else {
                    var err = {};
                    try { err = JSON.parse(xhr.responseText); } catch (e) {}
                    var msg = (err && err.message) ? err.message : ('Delete failed: ' + (xhr.statusText || xhr.responseText));
                    alert(msg);
                    if (confirmModal) confirmModal.hide();
                    deleteTarget = null;
                }
            };

            xhr.onerror = function () { alert('Delete request failed.'); if (confirmModal) confirmModal.hide(); deleteTarget = null; };

            // Laravel expects _method=DELETE when using POST; send form encoded body
            var body = new FormData();
            body.append('_method', 'DELETE');
            if (token) body.append('_token', token);
            xhr.send(body);
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('stAttachmentForm');
    if (!form) return;

    // immediate file size validation on change
    var stFileInput = document.getElementById('stAttachmentFile');
    if (stFileInput) {
        stFileInput.addEventListener('change', function () {
            var maxBytes = 30 * 1024 * 1024;
            var submitBtn = document.getElementById('stAttachmentSubmitBtn');
            if (stFileInput.files && stFileInput.files.length > 0 && stFileInput.files[0].size > maxBytes) {
                var opener = window._lastSTUploadButton;
                var msg = 'File too large. Max 30MB.';
                if (opener) {
                    try { var p = new bootstrap.Popover(opener, {content: msg, trigger: 'manual', placement: 'top'}); p.show(); setTimeout(function () { p.hide(); p.dispose(); }, 3000); } catch (e) { alert(msg); }
                } else { alert(msg); }
                if (submitBtn) submitBtn.disabled = true;
            } else {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var submitBtn = document.getElementById('stAttachmentSubmitBtn');
        var modalEl = document.getElementById('stAttachmentModal');
        var modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (submitBtn) {
            submitBtn.disabled = true;
        }
        var origText = submitBtn ? submitBtn.innerHTML : null;

        if (submitBtn) submitBtn.innerHTML = 'Uploading...';

        // client-side size check (30MB)
        var fileInput = document.getElementById('stAttachmentFile');
        var maxBytes = 30 * 1024 * 1024;
        if (fileInput && fileInput.files && fileInput.files.length > 0) {
            if (fileInput.files[0].size > maxBytes) {
                // show popover on opener if possible
                var opener = window._lastSTUploadButton;
                if (opener) {
                    try {
                        var pop = new bootstrap.Popover(opener, {content: 'File too large. Max 30MB.', trigger: 'manual', placement: 'top'});
                        pop.show();
                        setTimeout(function () { pop.hide(); pop.dispose(); }, 3500);
                    } catch (e) {
                        alert('File too large. Max 30MB.');
                    }
                } else {
                    alert('File too large. Max 30MB.');
                }
                if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = origText; }
                return;
            }
        }

        // Use XMLHttpRequest to get upload progress events
        var fd = new FormData(form);
        var progressWrap = document.getElementById('stAttachmentProgress');
        var progressBar = progressWrap ? progressWrap.querySelector('.progress-bar') : null;
        if (progressWrap) { progressWrap.style.display = 'block'; if (progressBar) progressBar.style.width = '0%'; }

        var xhr = new XMLHttpRequest();
        xhr.open('POST', form.action, true);
        xhr.withCredentials = true;
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.upload.onprogress = function (e) {
            try {
                var fileSize = 0;
                try { fileSize = (fileInput && fileInput.files && fileInput.files[0]) ? fileInput.files[0].size : 0; } catch (_) { fileSize = 0; }
                var total = (e.lengthComputable && e.total) ? e.total : (fileSize || 0);
                if (progressBar) {
                    if (e.lengthComputable || total > 0) {
                        var pct = Math.round((e.loaded / total) * 100);
                        if (!isFinite(pct) || pct < 0) pct = 0;
                        if (pct > 100) pct = 100;
                        progressBar.style.width = pct + '%';
                        progressBar.setAttribute('aria-valuenow', pct);
                        progressBar.textContent = pct + '%';
                        progressBar.classList.remove('progress-bar-striped', 'progress-bar-animated');
                    } else {
                        // length not computable: show an indeterminate animated bar
                        progressBar.style.width = '40%';
                        progressBar.classList.add('progress-bar-striped', 'progress-bar-animated');
                        progressBar.removeAttribute('aria-valuenow');
                    }
                }
            } catch (err) { console.log('progress handler error', err); }
        };

        xhr.onload = function () {
            try { if (progressBar) progressBar.style.width = '100%'; } catch (e) {}
            if (xhr.status >= 200 && xhr.status < 300) {
                var data = {};
                try { data = JSON.parse(xhr.responseText); } catch (e) {}
                if (data.success) {
                    if (modalInstance) modalInstance.hide();
                    var opener = window._lastSTUploadButton;
                    // update the UI in-place: replace the upload button with view + delete
                    try {
                        if (opener) {
                            var wrapper = opener.closest('.d-inline-flex') || opener.parentElement;
                            // preserve original data attributes on wrapper so we can recreate upload button later
                            try {
                                var dr = opener.getAttribute('data-region'); if (dr) wrapper.setAttribute('data-region', dr);
                                var dp = opener.getAttribute('data-province'); if (dp) wrapper.setAttribute('data-province', dp);
                                var dm = opener.getAttribute('data-municipality'); if (dm) wrapper.setAttribute('data-municipality', dm);
                                var dt = opener.getAttribute('data-title'); if (dt) wrapper.setAttribute('data-title', dt);
                                var dy = opener.getAttribute('data-year'); if (dy) wrapper.setAttribute('data-year', dy);
                            } catch (e) {}
                            var csrf = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
                            var viewBtn = '<button type="button" class="btn btn-sm btn-outline-success btn-view-st-attachment" title="View uploaded attachment" data-url="' + (data.attachment && data.attachment.url ? data.attachment.url : '') + '" data-title="' + (data.attachment && data.attachment.title ? (data.attachment.title.replace(/"/g,'\"')) : '') + '" data-uploader="' + (data.uploader || '') + '"><i class="bi bi-filetype-pdf"></i></button>';
                            var deleteForm = '<form method="POST" class="attachment-delete-form" action="/sts-attachments/' + (data.attachment && data.attachment.id ? data.attachment.id : '') + '" onsubmit="return false;" style="display:inline-block;margin:0;">' +
                                '<input type="hidden" name="_token" value="' + csrf + '">' +
                                '<input type="hidden" name="_method" value="DELETE">' +
                                '<button type="submit" class="btn btn-sm btn-outline-danger" title="Delete attachment"><i class="bi bi-trash"></i></button>' +
                                '</form>';
                            if (wrapper) {
                                wrapper.innerHTML = viewBtn + deleteForm;
                            }
                        }
                    } catch (e) { console.log('DOM update failed', e); }

                    // show popover feedback attached to opener if possible
                    if (opener) {
                        try { var pop = new bootstrap.Popover(opener, {content: data.message || 'Uploaded', trigger: 'manual', placement: 'top'}); pop.show(); setTimeout(function () { pop.hide(); pop.dispose(); }, 3000); } catch (e) { console.log('Popover failed', e); }
                    }

                    // also show a small success modal
                    (function showUploadSuccessModal(msg){
                        try {
                            var existing = document.getElementById('upload-success-modal');
                            if (!existing) {
                                var div = document.createElement('div');
                                div.innerHTML = '<div class="modal fade" id="upload-success-modal" tabindex="-1" aria-hidden="true">' +
                                    '<div class="modal-dialog modal-sm modal-dialog-centered"><div class="modal-content"><div class="modal-body text-center p-3">' +
                                    '<div class="h5 mb-2">' + (msg || 'Uploaded') + '</div>' +
                                    '<div><button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">OK</button></div>' +
                                    '</div></div></div></div>';
                                document.body.appendChild(div.firstChild);
                                existing = document.getElementById('upload-success-modal');
                            } else {
                                existing.querySelector('.h5').textContent = msg || 'Uploaded';
                            }
                            var m = new bootstrap.Modal(existing);
                            m.show();
                        } catch (e) { try { alert(msg || 'Uploaded'); } catch(_){} }
                    })(data.message || 'Attachment uploaded successfully.');
                } else {
                    alert(data.message || 'Upload failed.');
                }
            } else if (xhr.status === 422) {
                var err = {};
                try { err = JSON.parse(xhr.responseText); } catch (e) {}
                var msgs = [];
                if (err.errors) { for (var k in err.errors) msgs.push(err.errors[k].join(' ')); }
                else if (err.message) msgs.push(err.message);
                alert(msgs.join('\n') || 'Validation failed.');
            } else if (xhr.status === 413) {
                var errText = {};
                try { errText = JSON.parse(xhr.responseText); } catch (e) {}
                var message = errText && errText.message ? errText.message : 'Uploaded file exceeds the maximum allowed size of 30MB.';
                var opener = window._lastSTUploadButton;
                if (opener) {
                    try { var pop2 = new bootstrap.Popover(opener, {content: message, trigger: 'manual', placement: 'top'}); pop2.show(); setTimeout(function () { pop2.hide(); pop2.dispose(); }, 3500); } catch (e) { alert(message); }
                } else { alert(message); }
            } else {
                alert('Upload failed: ' + (xhr.statusText || xhr.responseText));
            }
            if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = origText; }
            if (progressWrap) { setTimeout(function () { progressWrap.style.display = 'none'; if (progressBar) progressBar.style.width = '0%'; }, 800); }
        };

        xhr.onerror = function () { alert('Upload error.'); if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = origText; } if (progressWrap) progressWrap.style.display = 'none'; };
        xhr.send(fd);
    });
});
</script>

@if(Auth::user() && in_array(Auth::user()->usergroup, ['admin', 'sysadmin']))
<div class="modal fade" id="stsLogsModal" tabindex="-1" aria-labelledby="stsLogsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stsLogsModalLabel">STs Manager Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height:70vh; overflow-y:auto;" id="sts-logs-container">
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

        .st-moa-table th.st-col-year,
        .st-moa-table td.st-col-year {
            display: none;
        }

        .st-moa-table th.st-col-province,
        .st-moa-table td.st-col-province {
            display: none;
        }

        @media (max-width: 991.98px) {
            .st-moa-table-wrapper {
                border-radius: 12px;
            }
            .st-moa-table th,
            .st-moa-table td {
                padding: 0.4rem 0.55rem;
                font-size: 0.78rem;
            }
        }

        @media (max-width: 767.98px) {
            .st-moa-table {
                table-layout: auto;
                min-width: 100%;
                font-size: 0.76rem;
            }
            .st-moa-table-wrapper {
                overflow-x: visible;
                border-radius: 10px;
            }
            .st-moa-table thead tr {
                font-size: 0.78rem;
            }
            .st-custom-pagination {
                flex-wrap: wrap;
                gap: 8px;
                padding: 8px 8px 6px 8px;
            }
            .st-custom-pagination-btn {
                padding: 6px 18px;
                font-size: 0.95em;
            }
            .st-custom-pagination-indicator {
                font-size: 0.98em;
                min-width: auto;
            }
        }

    </style>
    <div class="st-custom-pagination">
        <button type="button" class="st-custom-pagination-btn" @if($sts->onFirstPage()) disabled @else onclick="loadUploadStsPage('{{ $sts->previousPageUrl() }}')" @endif>&#8592; Prev</button>
        <span class="st-custom-pagination-indicator">Page {{ $currentPage }} of {{ $lastPage }}</span>
        <button type="button" class="st-custom-pagination-btn" @if(!$sts->hasMorePages()) disabled @else onclick="loadUploadStsPage('{{ $sts->nextPageUrl() }}')" @endif>Next &#8594;</button>
    </div>
@endif
