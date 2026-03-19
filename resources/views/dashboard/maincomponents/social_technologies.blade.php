
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
                    <label for="csv-file">Or upload CSV</label>
                    <input id="csv-file" type="file" name="csv_file" accept=".csv,text/csv">
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
                    <input name="titles[]" type="text" placeholder="Enter social technology title" style="flex:1; padding:8px; border:1px solid #e6eef7; border-radius:8px;" required>
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
        <h3 style="margin-top:0">Social Technology Titles</h3>
        <form method="GET" action="{{ route('socialtech.index') }}" style="display:flex; gap:8px; align-items:center; margin-bottom:10px;">
            <input type="text" name="title" placeholder="Filter by title" value="{{ request('title') }}" style="padding:8px; border:1px solid #e6eef7; border-radius:8px; flex:1">
            <div style="display:flex; gap:8px;">
                <button type="submit" class="st-btn" style="background:#eef2ff; border:1px solid #d1d5db;">Filter</button>
                <a href="{{ route('socialtech.index') }}" class="st-btn" style="background:#f1f5f9; border:1px solid #dbeafe;">Clear</a>
            </div>
        </form>
        <div style="overflow:auto">
            <table class="st-table">
                <thead>
                    <tr><th>ID</th><th>Title</th><th>Created By</th><th>Updated At</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($titles as $t)
                        <tr>
                            <td>{{ $t->id }}</td>
                            <td>{{ $t->title }}</td>
                            <td>{{ $t->createdby ?: '-' }}</td>
                            <td>{{ $t->updated_at?->format('M d, Y h:i A') ?: '-' }}</td>
                            <td style="display:flex; gap:6px;">
                                <button class="st-btn edit-title" type="button" data-id="{{ $t->id }}" data-title="{{ $t->title }}" data-action="edit">Edit</button>
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
        <div class="st-card" style="width:600px; max-width:92%; margin:0 auto;">
            <h3 style="margin-top:0">Edit Title</h3>
            <div style="display:flex; flex-direction:column; gap:8px;">
                <input id="edit-title-input" type="text" style="padding:8px; border:1px solid #e6eef7; border-radius:8px; width:100%" />
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
                input.name = 'titles[]';
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

        function showModal(id, title) {
            editingId = id;
            modalInput.value = title;
            modalError.style.display = 'none';
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
                        } else {
                            const j = await res.json().catch(() => null);
                            alert(j?.message || 'Delete failed');
                        }
                    } catch (err) {
                        alert('Delete failed');
                    }
                });
            } else if (action === 'edit') {
                btn.addEventListener('click', () => {
                    const title = btn.getAttribute('data-title') || btn.closest('tr').querySelectorAll('td')[1].textContent.trim();
                    showModal(id, title);
                });
            }
        });

        btnSave.addEventListener('click', async () => {
            if (!editingId) return;
            const newTitle = modalInput.value.trim();
            if (newTitle === '') {
                modalError.textContent = 'Title cannot be empty.';
                modalError.style.display = 'block';
                return;
            }
            try {
                const res = await fetch(`/social-technologies/${editingId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ title: newTitle })
                });
                if (res.ok) {
                    // update row title cell
                    const editBtn = document.querySelector(`button[data-action=edit][data-id="${editingId}"]`);
                    if (editBtn) {
                        const row = editBtn.closest('tr');
                        if (row) {
                            row.querySelectorAll('td')[1].textContent = newTitle;
                        }
                        // also update data-title attribute
                        editBtn.setAttribute('data-title', newTitle);
                    }
                    closeModal();
                } else {
                    const j = await res.json().catch(() => null);
                    modalError.textContent = j?.message || 'Update failed';
                    modalError.style.display = 'block';
                }
            } catch (err) {
                modalError.textContent = 'Update failed';
                modalError.style.display = 'block';
            }
        });
    })();
</script>
@endsection
