@extends('layouts.app')

@section('content')
<div class="container py-4">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap');
    .sector-utilities-page { --sector-ink: #12315c; --sector-ink-soft: #5c6f88; --sector-card: rgba(255,255,255,0.92); --sector-accent: #1b6ef3; --sector-shadow: 0 22px 48px rgba(17,53,110,0.12); font-family: 'Manrope', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; color: var(--sector-ink); position: relative; }
    .sector-hero { display: grid; grid-template-columns: minmax(0,1.7fr) minmax(280px,1fr); gap: 1.25rem; padding: 1.5rem; margin-bottom: 1.5rem; border-radius: 20px; background: linear-gradient(135deg, rgba(255,255,255,0.94), rgba(240,247,255,0.92)); box-shadow: var(--sector-shadow); }
    .sector-kicker { display:inline-flex; align-items:center; gap:.5rem; padding:.35rem .75rem; border-radius:999px; background: rgba(17,72,168,0.08); color:var(--sector-accent-deep); font-weight:800; font-size:.8rem; text-transform:uppercase; letter-spacing:.08em; }
    .sector-title { margin:.6rem 0 .2rem; font-size: clamp(1.4rem, 1.6vw + 1rem, 2.2rem); font-weight:800; }
    .sector-subtitle { color: var(--sector-ink-soft); font-size: .95rem; margin:0; }
    .sector-summary-grid { display:grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap:.9rem; }
    .sector-summary-card { padding:1rem; border-radius:14px; background: rgba(255,255,255,0.9); border:1px solid rgba(18,49,92,0.06); box-shadow: 0 12px 28px rgba(18,49,92,0.06); }
    .sector-summary-label { color: #5c6f88; font-weight:700; font-size:.82rem; text-transform:uppercase; letter-spacing:.06em; }
    .sector-summary-value { font-weight:800; font-size:1.6rem; }

    .sector-panel { border-radius: 16px; background: var(--sector-card); box-shadow: var(--sector-shadow); border: 1px solid rgba(255,255,255,0.66); }
    .sector-panel-header { display:flex; justify-content:space-between; align-items:flex-start; padding:1rem 1.25rem; }
    .sector-panel-title { margin:0; font-weight:800; }
    .sector-pill { display:inline-flex; align-items:center; padding:.45rem .8rem; border-radius:999px; background: rgba(27,110,243,0.12); color:#1148a8; font-weight:800; }

    .sector-table-wrap { padding: 0 1.25rem 1.25rem; }
    .sector-gallery-table { --bs-table-bg: transparent; --bs-table-striped-bg: rgba(234,243,255,0.36); --bs-table-hover-bg: rgba(229,240,255,0.5); margin-bottom:0; color:var(--sector-ink); }
    .sector-gallery-table thead th { border-bottom: 1px solid rgba(18,49,92,0.12); color:#5c6f88; font-size:.77rem; font-weight:800; text-transform:uppercase; padding-top:1rem; padding-bottom:1rem; background: rgba(248,251,255,0.85); }
    .sector-gallery-table tbody td { vertical-align:middle; border-color: rgba(18,49,92,0.08); padding-top:1rem; padding-bottom:1rem; }

    @media (max-width: 991.98px) { .sector-hero { grid-template-columns: 1fr; } .sector-summary-grid { grid-template-columns: repeat(3,minmax(0,1fr)); } }
    @media (max-width: 767.98px) { .sector-summary-grid { grid-template-columns: 1fr; } }
  </style>

  <div class="sector-utilities-page">
    @if(!empty($missingTables ?? []))
      <div class="alert alert-warning border-0 shadow-sm">The logs UI detected missing log-related tables: <strong>{{ implode(', ', $missingTables) }}</strong>. Create the missing migrations or run your migrations to enable those modules in the logs view.</div>
    @endif
    <section class="sector-hero">
      <div>
        <span class="sector-kicker">System</span>
        <h3 class="sector-title">System Logs</h3>
        <p class="sector-subtitle">Recorded application activity and events across modules. Use filters to narrow results.</p>
      </div>
      <div class="sector-summary-grid">
      </div>
    </section>

    @if(session('success'))
      <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
    @endif

    <div class="card sector-panel mb-4">
      <div class="sector-panel-header">
        <div>
          <h4 class="sector-panel-title">Filters</h4>
          <p class="sector-panel-text">Narrow logs by module and other criteria.</p>
        </div>
        <span class="sector-pill">Quick filter</span>
      </div>
      <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
          <div class="col-md-4">
            <label class="form-label">Module</label>
            <select name="module" class="form-select">
              <option value="">All</option>
              <option value="master_data" {{ request('module')=='master_data'?'selected':'' }}>Master Data</option>
              <option value="sector_utilities" {{ request('module')=='sector_utilities'?'selected':'' }}>Sector Utilities</option>
              <option value="file_uploads" {{ request('module')=='file_uploads'?'selected':'' }}>File Uploads</option>
              <option value="social_titles" {{ request('module')=='social_titles'?'selected':'' }}>Social Technology Titles</option>
              <option value="user_management" {{ request('module')=='user_management'?'selected':'' }}>User Management</option>
              <option value="user_approval" {{ request('module')=='user_approval'?'selected':'' }}>User Approval</option>
            </select>
          </div>
          <div class="col-auto">
            <button class="btn btn-primary sector-action-btn">Filter</button>
          </div>
        </form>
      </div>
    </div>

    <div class="card sector-panel">
      <div class="sector-panel-header">
        <div>
          <h4 class="sector-panel-title">System Logs</h4>
          <p class="sector-panel-text">Browse recent activity. Click a row for more details or copy the details text.</p>
        </div>
        <span class="sector-pill">{{ $logs->count() }} shown</span>
      </div>

      <div class="sector-table-wrap">
        <div class="table-responsive">
          <table class="table table-sm table-hover align-middle sector-gallery-table">
            <thead>
              <tr>
                <th style="width:180px">Time</th>
                <th style="width:180px">Module</th>
                <th>Action</th>
                <th style="width:150px">User</th>
                <th>Details</th>
              </tr>
            </thead>
            <tbody>
              @forelse($logs as $l)
                <tr>
                  <td>{{ $l->created_at ?? '' }}</td>
                  <td>{{ $l->module ?? '' }}</td>
                  <td>{{ $l->action ?? '' }}</td>
                  <td>{{ $l->user_id ?? $l->createdby ?? '' }}</td>
                  <td style="max-width:540px;word-break:break-word">{{ $l->details ?? '' }}</td>
                </tr>
              @empty
                <tr><td colspan="5" class="text-center py-5 text-muted">No logs found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="mt-3">Showing {{ $logs->count() }} of {{ $total ?? $logs->count() }} entries</div>
  </div>
</div>
@endsection
