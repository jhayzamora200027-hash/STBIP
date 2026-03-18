
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
                </div>
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
