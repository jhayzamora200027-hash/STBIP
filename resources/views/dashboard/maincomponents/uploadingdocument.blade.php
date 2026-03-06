        @if(session('success') === 'Base Excel file updated successfully.')
            <div class="modal fade" id="successUpdateModal" tabindex="-1" aria-labelledby="successUpdateModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title" id="successUpdateModalLabel">Successfully Updated</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <div style="font-size:2.5rem; color:#22c55e;">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <h4 class="mt-3">Base Excel file updated successfully!</h4>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                        window.addEventListener('DOMContentLoaded', function() {
                                var modal = new bootstrap.Modal(document.getElementById('successUpdateModal'));
                                modal.show();
                        });
                </script>
        @endif

        @if(session('success') === 'Base Excel file refreshed from Google Sheet.')
                <div class="modal fade" id="successRefreshModal" tabindex="-1" aria-labelledby="successRefreshModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title" id="successRefreshModalLabel">Successfully Resync</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <div style="font-size:2.5rem; color:#22c55e;">
                                    <i class="bi bi-arrow-repeat"></i>
                                </div>
                                <h4 class="mt-3">Base file Resync from Google Sheet!</h4>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                        window.addEventListener('DOMContentLoaded', function() {
                                var modal = new bootstrap.Modal(document.getElementById('successRefreshModal'));
                                modal.show();
                        });
                </script>
        @endif
@extends('layouts.app')

@section('content')
    <div class="container mt-4 d-flex justify-content-start">
        <div class="card shadow" style="max-width: 750px; min-width: 650px; margin-bottom: 2.5rem; background: #fff; border-radius: 18px;">
            <div class="card-body">
                <h1 class="mb-4">Upload Document</h1>
                @if(Auth::user() && in_array(Auth::user()->usergroup, ['admin','sysadmin']))
                    <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="viewSelectLogsBtn" data-bs-toggle="modal" data-bs-target="#selectionHistoryModal">
                        <i class="bi bi-card-list"></i> View Logs
                    </button>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if (Auth::user() && in_array(Auth::user()->usergroup, ['admin', 'sysadmin']))
                <div class="mb-3">
                    <h2 style="font-size:1.25rem;">Social Technology Documents</h2>
                    <label for="googleSheetUrl" class="form-label">Google Sheets Link</label>
                    <div class="d-flex align-items-stretch mb-2">
                        <form action="{{ route('excel.upload') }}" method="POST" enctype="multipart/form-data" class="flex-grow-1 me-2">
                            @csrf
                            <div class="input-group">
                                <input type="url" class="form-control" id="googleSheetUrl" name="googleSheetUrl" placeholder="https://docs.google.com/spreadsheets/d/..." required>
                                <button type="submit" class="btn btn-primary">Upload</button>
                            </div>
                        </form>
                        @if(isset($currentSheetUrl) && $currentSheetUrl)
                            <form id="refreshSheetForm" action="{{ route('excel.refreshGoogleSheet') }}" method="POST" class="d-flex align-items-stretch">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary" id="refreshSheetBtn" title="Resync Sheet">
                                    <i class="bi bi-arrow-repeat" id="refreshSheetIcon"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                    <small class="text-muted">Make sure the sheet is shared as "Anyone with the link" (viewer).</small>
                    @if(isset($currentSheetUrl) && $currentSheetUrl)
                    <div class="mt-2 mb-2 d-flex justify-content-between align-items-center">
                        <span class="text-muted">Current sheet is ready to share.</span>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#shareSheetModal">
                            View & Copy Link
                        </button>
                    </div>
                    <div class="modal fade" id="shareSheetModal" tabindex="-1" aria-labelledby="shareSheetModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="shareSheetModalLabel">Share Google Sheet Link</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="mb-2">Use this link to share the current Google Sheet:</p>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="shareSheetInput" value="{{ $currentSheetUrl }}" readonly>
                                        <button class="btn btn-primary" type="button" id="copySheetLinkBtn">Copy</button>
                                    </div>
                                    <small class="text-muted">Anyone with this link (viewer) can access the sheet if sharing is enabled in Google Sheets.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            var copyBtn = document.getElementById('copySheetLinkBtn');
                            if (!copyBtn) return;
                            copyBtn.addEventListener('click', function () {
                                var input = document.getElementById('shareSheetInput');
                                if (!input) return;
                                input.select();
                                input.setSelectionRange(0, 99999);
                                var text = input.value;

                                if (navigator.clipboard && navigator.clipboard.writeText) {
                                    navigator.clipboard.writeText(text).then(function () {
                                        copyBtn.textContent = 'Copied!';
                                        setTimeout(function () { copyBtn.textContent = 'Copy'; }, 2000);
                                    }).catch(function () {
                                        document.execCommand('copy');
                                    });
                                } else {
                                    document.execCommand('copy');
                                }
                            });
                            var refreshBtn = document.getElementById('refreshSheetBtn');
                            var refreshIcon = document.getElementById('refreshSheetIcon');
                            if (refreshBtn && refreshIcon) {
                                refreshBtn.addEventListener('click', function () {
                                    refreshIcon.classList.add('refresh-spin');
                                });
                            }
                        });
                    </script>
                    @endif
                    </div>
                </form>
                @endif
                <div id="upload-logs-container">
                    @include('dashboard.maincomponents.partials.uploadingdocument_logs')
                </div>
                <style>
                    .refresh-spin {
                        animation: refresh-rotate 0.8s linear infinite;
                    }
                    @keyframes refresh-rotate {
                        from { transform: rotate(0deg); }
                        to { transform: rotate(360deg); }
                    }
                </style>
                <script>
                    function updateBaseExcel() {
                        var form = document.getElementById('selectBaseExcelForm');
                        if (!form) {
                            alert('Form not found. Please reload the page.');
                            return;
                        }
                        var radios = form.querySelectorAll('input[type=radio][name=base_excel]');
                        var selected = false;
                        radios.forEach(function (radio) {
                            if (radio.checked) selected = true;
                        });
                        if (!selected) {
                            alert('Please select an Excel file to set as base.');
                            return;
                        }
                        form.submit();
                    }

                    function loadUploadLogsPage(url) {
                        if (!url) {
                            return;
                        }
                        var container = document.getElementById('upload-logs-container');
                        if (!container) {
                            window.location = url;
                            return;
                        }

                        fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                            .then(function (response) { return response.json(); })
                            .then(function (data) {
                                if (data && data.html) {
                                    container.innerHTML = data.html;
                                } else if (data && data.redirect) {
                                    window.location = data.redirect;
                                }
                            })
                            .catch(function () {
                                window.location = url;
                            });
                    }
                </script>
                    </div>
                    </div>
                </div>
                <div class="container mt-1 d-flex justify-content-start">
    </div>
    </div>

    <div class="mt-5">
    </div>
    


@endsection