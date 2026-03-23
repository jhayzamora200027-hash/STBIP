
@extends('layouts.app')

@section('content')
<style>
    .st-shell { max-width:1200px; margin:0 auto; padding:20px; }
    .st-hero { background: linear-gradient(135deg,#0b2540,#175d8f); color:#fff; padding:20px; border-radius:16px; margin-bottom:18px; }
    .st-card { background:#fff; border:1px solid #e6eef7; border-radius:12px; padding:16px; }
    .st-btn { padding:10px 14px; border-radius:10px; font-weight:700; }
    .st-btn-primary { background:#175d8f; color:#fff; border:none; }
    .st-table { width:100%; border-collapse:collapse; margin-top:12px; }
    .st-table th, .st-table td { padding:10px; border-bottom:1px solid #eef4fb; text-align:left; }
</style>

<div class="st-shell">
    <div class="st-hero">
        <h1>Social Technologies - Titles Upload</h1>
        <p style="margin:6px 0 0; opacity:0.9">Upload a CSV file containing one social technology title per row. Existing exact-title duplicates are skipped.</p>
    </div>

    <!-- Inline status banner (also shown on AJAX success for visibility) -->
    <div id="st-inline-status" style="display:none; margin-top:12px; padding:12px; border-radius:8px; font-weight:700;"></div>

    @if(session('status'))
        <div class="st-card" style="border-left:4px solid #10b981; margin-bottom:12px">{{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="st-card" style="border-left:4px solid #ef4444; margin-bottom:12px">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="st-card" style="border-left:4px solid #ef4444; margin-bottom:12px">
            <strong>Unable to import.</strong>
            <ul>
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="st-card">
        <h3 style="margin-top:0">Upload CSV</h3>
        <form method="POST" action="{{ route('socialtech.import') }}" enctype="multipart/form-data">
            @csrf
            <div style="display:grid; gap:10px;">
                <div class="st-field">
                    <label for="google-sheet-url">Google Sheet URL (optional)</label>
                    <input id="google-sheet-url" type="url" name="google_sheet_url" placeholder="https://docs.google.com/spreadsheets/d/..." style="width:100%; padding:8px; border:1px solid #e6eef7; border-radius:8px;">
                </div>
                <div class="st-field">
                    <label for="csv-file">Or upload CSV / Excel</label>
                    <input id="csv-file" type="file" name="csv_file" accept=".csv,text/csv,.xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel">
                </div>
                <div style="display:flex; gap:12px; align-items:center;">
                    <button class="st-btn st-btn-primary" type="submit">Upload and Import</button>
                    <a href="{{ route('masterdata.index') }}" class="st-btn" style="background:#f1f5f9; border:1px solid #dbeafe;">Open Masterdata</a>
                    	<a href="{{ route('socialtech.export') }}" class="st-btn" style="background:#eef2ff; border:1px solid #d1d5db;">Export Titles (Excel)</a>
                    <button type="button" id="toggle-add-title" class="st-btn" style="background:#f8fafc; border:1px solid #d1fae5;">Add Title</button>
                </div>
            </div>
        </form>
    </section>

    <section id="add-title-panel" class="st-card" style="margin-top:12px; display:none">
        <h3 style="margin-top:0">Add Title(s)</h3>
        <form method="POST" action="{{ route('socialtech.add') }}">
            @csrf
            <div id="titles-list" style="display:flex; flex-direction:column; gap:8px;">
                <div style="display:flex; gap:8px; align-items:center;">
                    <input name="social_technologies[]" type="text" placeholder="Enter social technology" style="flex:1; padding:8px; border:1px solid #e6eef7; border-radius:8px;" required>
                    <button type="button" class="st-btn remove-title" style="display:none;">Remove</button>
                </div>
            </div>
            <div style="display:flex; gap:8px; margin-top:8px;">
                <button type="button" id="add-more-title" class="st-btn" style="background:#f8fafc; border:1px solid #dbeafe;">Add another</button>
                <button class="st-btn st-btn-primary" type="submit">Add</button>
                <button type="button" id="cancel-add" class="st-btn" style="background:#f1f5f9; border:1px solid #dbeafe;">Cancel</button>
            </div>
        </form>
    </section>

    <section class="st-card" style="margin-top:18px">
        <h3 style="margin-top:0">Social Technologies</h3>
        <form method="GET" action="{{ route('STDashboard') }}" style="display:flex; gap:8px; align-items:center; margin-bottom:10px;">
            <input type="text" name="social_technology" placeholder="Filter by social technology" value="{{ request('social_technology') }}" style="padding:8px; border:1px solid #e6eef7; border-radius:8px; flex:1">
            <div style="display:flex; gap:8px;">
                <button type="submit" class="st-btn" style="background:#eef2ff; border:1px solid #d1d5db;">Filter</button>
                <a href="{{ route('STDashboard') }}" class="st-btn" style="background:#f1f5f9; border:1px solid #dbeafe;">Clear</a>
            </div>
        </form>
        <div style="overflow:auto">
            <table class="st-table">
                <thead>
                    <tr><th>ID</th><th>Social Technology</th><th>Created By</th><th>Updated At</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($titles as $t)
                        <tr>
                            <td>{{ $t->id }}</td>
                            <td>{{ $t->social_technology }}</td>
                            <td>{{ $t->createdby ?: '-' }}</td>
                            <td>{{ $t->updated_at?->format('M d, Y h:i A') ?: '-' }}</td>
                            <td style="display:flex; gap:6px;">
                                <button class="st-btn edit-title" type="button" data-id="{{ $t->id }}" data-social_technology="{{ $t->social_technology }}" data-action="edit"
                                    data-sector="{{ $t->sector }}"
                                    data-laws_and_issuances="{{ $t->laws_and_issuances }}"
                                    data-description="{{ $t->description }}"
                                    data-objectives="{{ $t->objectives }}"
                                    data-components="{{ $t->components }}"
                                    data-pilot_areas="{{ $t->pilot_areas }}"
                                    data-year_implemented="{{ $t->year_implemented }}"
                                    data-status_remarks="{{ $t->status_remarks }}"
                                    data-resolution="{{ $t->resolution }}"
                                    data-guidelines="{{ $t->guidelines }}"
                                    data-program_manual_outline="{{ $t->program_manual_outline }}"
                                    data-information_systems_developed="{{ $t->information_systems_developed }}"
                                    data-session_guide_key_topics="{{ $t->session_guide_key_topics }}"
                                    data-training_manual_outline="{{ $t->training_manual_outline }}"
                                >Edit</button>
                                <button class="st-btn delete-title" type="button" data-id="{{ $t->id }}" data-action="delete" style="background:#fee2e2; border:1px solid #fca5a5;">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No titles yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:12px; display:flex; justify-content:flex-end">
            {{ $titles->appends(request()->except('page'))->links() }}
        </div>
    </section>
    <!-- Edit modal -->
    <div id="editTitleModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); align-items:center; justify-content:center; z-index:60;">
        <div class="st-card" style="width:700px; max-width:96%; max-height:90vh; margin:0 auto; overflow:auto;">
            <h3 style="margin-top:0">Edit Social Technology Title</h3>
            <div style="display:flex; flex-direction:column; gap:8px;">
                <label>Social Technology</label>
                <input id="edit-title-input" type="text" name="social_technology" style="padding:8px; border:1px solid #e6eef7; border-radius:8px; width:100%" />

                <label>Sector</label>
                <input id="edit-sector-input" type="text" style="padding:8px; border:1px solid #e6eef7; border-radius:8px; width:100%" />

                <label>Laws and Issuances</label>
                <textarea id="edit-laws-and-issuances-input" rows="3" style="padding:8px; border:1px solid #e6eef7; border-radius:8px; width:100%"></textarea>


                <label>Description</label>
                <textarea id="edit-description-input" rows="3" style="padding:8px; border:1px solid #e6eef7; border-radius:8px; width:100%"></textarea>

                <label>Objectives</label>
                <textarea id="edit-objectives-input" rows="3" style="padding:8px; border:1px solid #e6eef7; border-radius:8px; width:100%"></textarea>

                <label>Components</label>
                <textarea id="edit-components-input" rows="3" style="padding:8px; border:1px solid #e6eef7; border-radius:8px; width:100%"></textarea>

                <label>Pilot Areas</label>
                <input id="edit-pilot-areas-input" type="text" style="padding:8px; border:1px solid #e6eef7; border-radius:8px; width:100%" />

                <label>Year Implemented</label>
                <input id="edit-year-implemented-input" type="number" min="0" style="padding:8px; border:1px solid #e6eef7; border-radius:8px; width:100%" />

                <label>Status / Remarks</label>
                <textarea id="edit-status-remarks-input" rows="2" style="padding:8px; border:1px solid #e6eef7; border-radius:8px; width:100%"></textarea>

                <label>Resolution</label>
                <textarea id="edit-resolution-input" rows="2" style="padding:8px; border:1px solid #e6eef7; border-radius:8px; width:100%"></textarea>

                <label>Guidelines (AO / MC Number)</label>
                <input id="edit-guidelines-input" type="text" style="padding:8px; border:1px solid #e6eef7; border-radius:8px; width:100%" />

                <label>Program Manual Outline</label>
                <textarea id="edit-program-manual-outline-input" rows="3" style="padding:8px; border:1px solid #e6eef7; border-radius:8px; width:100%"></textarea>

                <label>Information Systems Developed (Key Tools)</label>
                <textarea id="edit-information-systems-developed-input" rows="3" style="padding:8px; border:1px solid #e6eef7; border-radius:8px; width:100%"></textarea>

                <label>Session Guide Key Topics</label>
                <textarea id="edit-session-guide-key-topics-input" rows="3" style="padding:8px; border:1px solid #e6eef7; border-radius:8px; width:100%"></textarea>

                <label>Training Manual Outline</label>
                <textarea id="edit-training-manual-outline-input" rows="3" style="padding:8px; border:1px solid #e6eef7; border-radius:8px; width:100%"></textarea>

                <div id="edit-title-error" style="color:#b91c1c; display:none"></div>
                <div style="display:flex; gap:8px; justify-content:flex-end; margin-top:6px;">
                    <button id="edit-cancel" type="button" class="st-btn" style="background:#f1f5f9; border:1px solid #dbeafe;">Cancel</button>
                    <button id="edit-save" type="button" class="st-btn st-btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

<!-- Toast container placed outside script so it's available globally -->
<div id="st-toast" aria-live="polite" aria-atomic="true" style="position:fixed; bottom:20px; right:20px; z-index:70; display:none;"></div>

@section('scripts')
<script>
    (function(){
        const toggle = document.getElementById('toggle-add-title');
        const panel = document.getElementById('add-title-panel');
        const cancel = document.getElementById('cancel-add');
        if (toggle && panel) {
            toggle.addEventListener('click', () => panel.style.display = panel.style.display === 'none' ? 'block' : 'none');
        }
        if (cancel && panel) {
            cancel.addEventListener('click', () => panel.style.display = 'none');
        }
        // Dynamic add/remove inputs
        const addMore = document.getElementById('add-more-title');
        const titlesList = document.getElementById('titles-list');
        if (addMore && titlesList) {
            addMore.addEventListener('click', () => {
                const row = document.createElement('div');
                row.style.display = 'flex';
                row.style.gap = '8px';
                row.style.alignItems = 'center';

                const input = document.createElement('input');
                input.name = 'social_technologies[]';
                input.type = 'text';
                input.placeholder = 'Enter social technology title';
                input.style.flex = '1';
                input.style.padding = '8px';
                input.style.border = '1px solid #e6eef7';
                input.style.borderRadius = '8px';

                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'st-btn';
                remove.textContent = 'Remove';
                remove.addEventListener('click', () => row.remove());

                row.appendChild(input);
                row.appendChild(remove);
                titlesList.appendChild(row);
            });
        }
        // Edit / Delete handlers (modal-based edit)
        const csrfToken = '{{ csrf_token() }}';
        const modal = document.getElementById('editTitleModal');
        const modalInput = document.getElementById('edit-title-input');
        const modalError = document.getElementById('edit-title-error');
        const btnSave = document.getElementById('edit-save');
        const btnCancel = document.getElementById('edit-cancel');
        let editingId = null;

        function showModal(btn) {
            // btn is the edit button element
            editingId = btn.getAttribute('data-id');
            modalError.style.display = 'none';
            // populate fields
            modalInput.value = btn.getAttribute('data-social_technology') || '';
            document.getElementById('edit-sector-input').value = btn.getAttribute('data-sector') || '';
            document.getElementById('edit-laws-and-issuances-input').value = btn.getAttribute('data-laws_and_issuances') || '';
            document.getElementById('edit-description-input').value = btn.getAttribute('data-description') || '';
            document.getElementById('edit-objectives-input').value = btn.getAttribute('data-objectives') || '';
            document.getElementById('edit-components-input').value = btn.getAttribute('data-components') || '';
            document.getElementById('edit-pilot-areas-input').value = btn.getAttribute('data-pilot_areas') || '';
            document.getElementById('edit-year-implemented-input').value = btn.getAttribute('data-year_implemented') || '';
            document.getElementById('edit-status-remarks-input').value = btn.getAttribute('data-status_remarks') || '';
            document.getElementById('edit-resolution-input').value = btn.getAttribute('data-resolution') || '';
            document.getElementById('edit-guidelines-input').value = btn.getAttribute('data-guidelines') || '';
            document.getElementById('edit-program-manual-outline-input').value = btn.getAttribute('data-program_manual_outline') || '';
            document.getElementById('edit-information-systems-developed-input').value = btn.getAttribute('data-information_systems_developed') || '';
            document.getElementById('edit-session-guide-key-topics-input').value = btn.getAttribute('data-session_guide_key_topics') || '';
            document.getElementById('edit-training-manual-outline-input').value = btn.getAttribute('data-training_manual_outline') || '';

            modal.style.display = 'flex';
            modalInput.focus();
        }

        function closeModal() {
            editingId = null;
            modal.style.display = 'none';
        }

        btnCancel.addEventListener('click', closeModal);
        modal.addEventListener('click', (ev) => { if (ev.target === modal) closeModal(); });
        document.addEventListener('keydown', (ev) => { if (ev.key === 'Escape') closeModal(); });

        document.querySelectorAll('button[data-id]').forEach(btn => {
            const action = btn.getAttribute('data-action');
            const id = btn.getAttribute('data-id');
            if (action === 'delete') {
                btn.addEventListener('click', async () => {
                    if (!confirm('Delete this title?')) return;
                    const rowForDelete = btn.closest('tr');
                    const titleForDelete = rowForDelete ? (rowForDelete.querySelectorAll('td')[1]?.textContent || '').trim() : '';
                    try {
                        const res = await fetch(`/social-technologies/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });
                        if (res.ok) {
                            // remove row
                            const row = btn.closest('tr');
                            if (row) row.remove();
                            const deletedLabel = titleForDelete || `ID ${id}`;
                            showToast(`Deleted: ${deletedLabel}`, 'success');
                            showInlineStatus(`Deleted: ${deletedLabel}`, 'success');
                        } else {
                            const j = await res.json().catch(() => null);
                            alert(j?.message || 'Delete failed');
                            showToast(j?.message || 'Delete failed', 'error');
                            showInlineStatus(j?.message || 'Delete failed', 'error');
                        }
                    } catch (err) {
                        alert('Delete failed');
                        showToast('Delete failed', 'error');
                        showInlineStatus('Delete failed', 'error');
                    }
                });
            } else if (action === 'edit') {
                btn.addEventListener('click', () => {
                    showModal(btn);
                });
            }
        });

        btnSave.addEventListener('click', async () => {
            if (!editingId) return;
            const newSocial = modalInput.value.trim();
            if (newSocial === '') {
                modalError.textContent = 'Social Technology cannot be empty.';
                modalError.style.display = 'block';
                return;
            }

            // collect all fields
            const payload = {
                social_technology: newSocial,
                sector: document.getElementById('edit-sector-input').value.trim(),
                laws_and_issuances: document.getElementById('edit-laws-and-issuances-input').value.trim(),
                description: document.getElementById('edit-description-input').value.trim(),
                objectives: document.getElementById('edit-objectives-input').value.trim(),
                components: document.getElementById('edit-components-input').value.trim(),
                pilot_areas: document.getElementById('edit-pilot-areas-input').value.trim(),
                year_implemented: document.getElementById('edit-year-implemented-input').value ? parseInt(document.getElementById('edit-year-implemented-input').value, 10) : null,
                status_remarks: document.getElementById('edit-status-remarks-input').value.trim(),
                resolution: document.getElementById('edit-resolution-input').value.trim(),
                guidelines: document.getElementById('edit-guidelines-input').value.trim(),
                program_manual_outline: document.getElementById('edit-program-manual-outline-input').value.trim(),
                information_systems_developed: document.getElementById('edit-information-systems-developed-input').value.trim(),
                session_guide_key_topics: document.getElementById('edit-session-guide-key-topics-input').value.trim(),
                training_manual_outline: document.getElementById('edit-training-manual-outline-input').value.trim(),
            };

            try {
                const res = await fetch(`/social-technologies/${editingId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                if (res.ok) {
                    // update row title cell and edit button data attributes
                    const editBtn = document.querySelector(`button[data-action=edit][data-id="${editingId}"]`);
                    if (editBtn) {
                        const row = editBtn.closest('tr');
                        if (row) {
                            row.querySelectorAll('td')[1].textContent = payload.social_technology;
                        }
                        // update data attributes so subsequent edits show current values
                        editBtn.setAttribute('data-social_technology', payload.social_technology || '');
                        editBtn.setAttribute('data-sector', payload.sector || '');
                        editBtn.setAttribute('data-laws_and_issuances', payload.laws_and_issuances || '');
                        editBtn.setAttribute('data-description', payload.description || '');
                        editBtn.setAttribute('data-objectives', payload.objectives || '');
                        editBtn.setAttribute('data-components', payload.components || '');
                        editBtn.setAttribute('data-pilot_areas', payload.pilot_areas || '');
                        editBtn.setAttribute('data-year_implemented', payload.year_implemented ?? '');
                        editBtn.setAttribute('data-status_remarks', payload.status_remarks || '');
                        editBtn.setAttribute('data-resolution', payload.resolution || '');
                        editBtn.setAttribute('data-guidelines', payload.guidelines || '');
                        editBtn.setAttribute('data-program_manual_outline', payload.program_manual_outline || '');
                        editBtn.setAttribute('data-information_systems_developed', payload.information_systems_developed || '');
                        editBtn.setAttribute('data-session_guide_key_topics', payload.session_guide_key_topics || '');
                        editBtn.setAttribute('data-training_manual_outline', payload.training_manual_outline || '');
                    }
                    closeModal();
                    const updatedLabel = payload.social_technology || `ID ${editingId}`;
                    showToast(`Updated: ${updatedLabel}`, 'success');
                    showInlineStatus(`Updated: ${updatedLabel}`, 'success');
                } else {
                    const j = await res.json().catch(() => null);
                    modalError.textContent = j?.message || 'Update failed';
                    modalError.style.display = 'block';
                    showToast(j?.message || 'Update failed', 'error');
                    showInlineStatus(`${j?.message || 'Update failed'}${payload?.social_technology ? ' — ' + payload.social_technology : ''}`, 'error');
                }
            } catch (err) {
                modalError.textContent = 'Update failed';
                modalError.style.display = 'block';
                showToast('Update failed', 'error');
                showInlineStatus(`Update failed${payload?.social_technology ? ' — ' + payload.social_technology : ''}`, 'error');
            }
        });
        // Toast helper
        function showToast(message, type) {
            try {
                let toast = document.getElementById('st-toast');
                let inner = document.getElementById('st-toast-inner');
                if (!toast) return;
                inner = inner || document.createElement('div');
                if (!document.getElementById('st-toast-inner')) {
                    inner.id = 'st-toast-inner';
                    toast.appendChild(inner);
                }
                inner.textContent = message || '';
                if (type === 'success') {
                    inner.style.background = '#065f46';
                } else if (type === 'error') {
                    inner.style.background = '#991b1b';
                } else {
                    inner.style.background = '#111827';
                }
                inner.style.color = '#fff';
                inner.style.padding = '12px 16px';
                inner.style.borderRadius = '8px';
                inner.style.boxShadow = '0 6px 18px rgba(0,0,0,0.2)';
                toast.style.display = 'block';
                clearTimeout(window._st_toast_timeout);
                window._st_toast_timeout = setTimeout(() => { toast.style.display = 'none'; }, 3500);
            } catch (e) {
                // ignore
            }
        }

        // Inline status banner helper (top of content)
        function showInlineStatus(message, type) {
            try {
                const banner = document.getElementById('st-inline-status');
                if (!banner) return;
                banner.textContent = message || '';
                if (type === 'success') {
                    banner.style.background = '#ecfdf5';
                    banner.style.color = '#065f46';
                    banner.style.border = '1px solid #10b981';
                } else if (type === 'error') {
                    banner.style.background = '#fff1f2';
                    banner.style.color = '#991b1b';
                    banner.style.border = '1px solid #f87171';
                } else {
                    banner.style.background = '#f8fafc';
                    banner.style.color = '#111827';
                    banner.style.border = '1px solid #e5e7eb';
                }
                banner.style.display = 'block';
                clearTimeout(window._st_inline_timeout);
                window._st_inline_timeout = setTimeout(() => { banner.style.display = 'none'; }, 4000);
            } catch (e) {
                // ignore
            }
        }

    })();
</script>
@endsection
