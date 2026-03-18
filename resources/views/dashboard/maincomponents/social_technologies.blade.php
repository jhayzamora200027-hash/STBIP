
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
        <h1>Social Technologies — Titles Upload</h1>
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
        <h3 style="margin-top:0">Titles (latest first)</h3>
        <div style="overflow:auto">
            <table class="st-table">
                <thead>
                    <tr><th>ID</th><th>Title</th><th>Created By</th><th>Updated At</th></tr>
                </thead>
                <tbody>
                    @forelse($titles as $t)
                        <tr>
                            <td>{{ $t->id }}</td>
                            <td>{{ $t->title }}</td>
                            <td>{{ $t->createdby ?: '-' }}</td>
                            <td>{{ $t->updated_at?->format('M d, Y h:i A') ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No titles yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
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
    })();
</script>
@endsection
