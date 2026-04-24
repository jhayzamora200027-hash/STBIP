@extends('layouts.app')


@section('content')
@php
    $galleryCardsCollection = collect($galleryCards ?? []);
    $galleryCount = $galleryCardsCollection->count();
    $activeGalleryCount = $galleryCardsCollection->where('is_active', true)->count();
    $childCount = $galleryCardsCollection->sum(function ($card) {
        return optional($card->children)->count() ?? 0;
    });
@endphp

<div class="container py-4">
    <style>
      @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap');

      .sector-utilities-page {
        --sector-ink: #12315c;
        --sector-ink-soft: #5c6f88;
        --sector-card: rgba(255, 255, 255, 0.92);
        --sector-accent: #1b6ef3;
        --sector-accent-deep: #1148a8;
        --sector-shadow: 0 22px 48px rgba(17, 53, 110, 0.12);
        font-family: 'Manrope', 'Trebuchet MS', sans-serif;
        color: var(--sector-ink);
        position: relative;
      }

      .sector-utilities-page::before,
      .sector-utilities-page::after {
        content: '';
        position: absolute;
        inset: 0;
        pointer-events: none;
        z-index: 0;
      }

      .sector-utilities-page::before {
        background:
          radial-gradient(circle at top right, rgba(27, 110, 243, 0.14), transparent 26%),
          linear-gradient(135deg, rgba(18, 49, 92, 0.06), transparent 28%),
          linear-gradient(180deg, rgba(255, 255, 255, 0.8), rgba(234, 243, 255, 0.4));
      }

      .sector-utilities-page::after {
        background-image:
          linear-gradient(135deg, rgba(18, 49, 92, 0.035) 25%, transparent 25%),
          linear-gradient(225deg, rgba(18, 49, 92, 0.03) 25%, transparent 25%);
        background-size: 68px 68px;
        opacity: .45;
      }

      .sector-utilities-page > * {
        position: relative;
        z-index: 1;
      }

      .sector-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.7fr) minmax(280px, 1fr);
        gap: 1.25rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.66);
        border-radius: 28px;
        background:
          linear-gradient(135deg, rgba(255, 255, 255, 0.94), rgba(240, 247, 255, 0.92)),
          linear-gradient(120deg, rgba(27, 110, 243, 0.1), transparent);
        box-shadow: var(--sector-shadow);
        overflow: hidden;
      }

      .sector-hero-copy {
        max-width: 780px;
      }

      .sector-kicker {
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        padding: .45rem .85rem;
        border-radius: 999px;
        background: rgba(17, 72, 168, 0.1);
        color: var(--sector-accent-deep);
        font-size: .78rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
      }

      .sector-title {
        margin: .95rem 0 .55rem;
        font-size: clamp(1.8rem, 2vw + 1rem, 2.7rem);
        font-weight: 800;
        letter-spacing: -.04em;
      }

      .sector-subtitle {
        max-width: 60ch;
        margin: 0;
        color: #5c6f88;
        font-size: 1rem;
        line-height: 1.7;
      }

      .sector-summary-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .9rem;
        align-self: stretch;
      }

      .sector-summary-card {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: .85rem;
        min-height: 150px;
        padding: 1.1rem;
        border: 1px solid rgba(18, 49, 92, 0.08);
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.86);
        box-shadow: 0 16px 36px rgba(18, 49, 92, 0.08);
      }

      .sector-summary-label {
        color: #5c6f88;
        font-size: .8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        align-self: center;
      }

      .sector-summary-value {
        font-size: 2rem;
        font-weight: 800;
        letter-spacing: -.05em;
        line-height: 1;
        align-self:center;
        
      }

      .sector-summary-note {
        color: #5c6f88;
        font-size: .88rem;
      }

      .sector-panel {
        border: 1px solid rgba(255, 255, 255, 0.66);
        border-radius: 26px;
        background: var(--sector-card);
        box-shadow: var(--sector-shadow);
        backdrop-filter: blur(10px);
      }

      .sector-panel .card-body,
      .sector-panel .card-header {
        background: transparent;
      }

      .sector-panel-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.35rem 1.5rem 0;
      }

      .sector-panel-title {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 800;
        letter-spacing: -.02em;
      }

      .sector-panel-text {
        margin: .2rem 0 0;
        color: #5c6f88;
        font-size: .92rem;
      }

      .sector-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 54px;
        padding: .45rem .8rem;
        border-radius: 999px;
        background: rgba(27, 110, 243, 0.12);
        color: var(--sector-accent-deep);
        font-size: .82rem;
        font-weight: 800;
      }

      .sector-panel .card-body {
        padding: 1.5rem;
      }

      .sector-form-grid .form-label {
        margin-bottom: .45rem;
        color: var(--sector-ink);
        font-size: .86rem;
        font-weight: 700;
        letter-spacing: .01em;
      }

      .sector-form-grid .form-control,
      .sector-form-grid .form-select {
        min-height: 48px;
        border: 1px solid rgba(18, 49, 92, 0.12);
        border-radius: 16px;
        background: rgba(248, 251, 255, 0.95);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
      }

      .sector-form-grid textarea.form-control {
        min-height: 112px;
        resize: vertical;
      }

      .sector-form-grid .form-control:focus,
      .sector-form-grid .form-select:focus {
        border-color: rgba(27, 110, 243, 0.42);
        box-shadow: 0 0 0 .2rem rgba(27, 110, 243, 0.12);
        background: #fff;
      }

      .sector-toggle-wrap {
        display: flex;
        align-items: center;
        min-height: 48px;
        padding: .8rem 1rem;
        border: 1px solid rgba(18, 49, 92, 0.12);
        border-radius: 16px;
        background: rgba(248, 251, 255, 0.95);
      }

      .sector-toggle-wrap .form-check {
        margin: 0;
      }

      .sector-toggle-wrap .form-check-label {
        font-weight: 700;
        color: var(--sector-ink);
      }

      .sector-submit-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-top: .5rem;
      }

      .sector-submit-hint {
        color: #5c6f88;
        font-size: .9rem;
      }

      .sector-action-btn {
        border: none;
        border-radius: 16px;
        padding: .85rem 1.25rem;
        font-weight: 800;
        letter-spacing: .01em;
        box-shadow: 0 14px 24px rgba(27, 110, 243, 0.2);
      }

      .sector-action-btn.btn-primary,
      .sector-action-btn.btn-success {
        background: linear-gradient(135deg, #1b6ef3, #1148a8);
      }

      .sector-action-btn.btn-primary:hover,
      .sector-action-btn.btn-success:hover {
        background: linear-gradient(135deg, #155fd7, #0f3f91);
      }

      .sector-table-wrap {
        padding: 0 1.25rem 1.25rem;
      }

      .sector-gallery-table {
        --bs-table-bg: transparent;
        --bs-table-striped-bg: rgba(234, 243, 255, 0.36);
        --bs-table-hover-bg: rgba(229, 240, 255, 0.5);
        margin-bottom: 0;
        color: var(--sector-ink);
      }

      .sector-gallery-table thead th {
        border-bottom: 1px solid rgba(18, 49, 92, 0.12);
        color: #5c6f88;
        font-size: .77rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        background: rgba(248, 251, 255, 0.85);
        padding-top: 1rem;
        padding-bottom: 1rem;
      }

      .sector-gallery-table tbody td {
        vertical-align: middle;
        border-color: rgba(18, 49, 92, 0.08);
        padding-top: 1rem;
        padding-bottom: 1rem;
      }

      .gallery-row > td {
        position: relative;
        z-index: 1;
        transition: background-color .12s ease;
      }

      .gallery-row.expanded > td {
        border-top: none;
        border-left: none;
        border-right: none;
        background: #ffffff;
        box-shadow: 0 10px 24px rgba(16, 24, 32, 0.05);
        transition: border-color .22s ease, box-shadow .28s ease, background-color .18s ease;
      }

      .gallery-row.expanded > td:first-child {
        border-left: 3px solid #9aa0a6;
        border-top-left-radius: .85rem;
      }

      .gallery-row.expanded > td:last-child {
        border-right: 3px solid #9aa0a6;
        border-top-right-radius: .85rem;
      }

      .gallery-row.animating > td {
        box-shadow: 0 12px 28px rgba(16, 24, 32, 0.08);
      }

      .gallery-row.expanded > td {
        border-bottom: 0 !important;
      }

      .gallery-row.expanded + .children-row > td {
        border-top: 0 !important;
      }

      .children-row > td {
        border: none;
        padding: 0;
        background: transparent;
      }

      .children-row > td .card {
        border: none;
        box-shadow: none;
        margin-bottom: 0;
      }

      .children-row > td .table {
        margin-bottom: 0;
      }

      .children-panel {
        display: block;
        padding: 0;
        background: transparent;
        margin-top: -4px;
        position: relative;
        z-index: 2;
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transition: height .36s cubic-bezier(.2, .9, .2, 1), max-height .36s cubic-bezier(.2, .9, .2, 1), opacity .22s ease, border-color .18s ease;
      }

      .children-panel .card {
        transform: translateY(-8px);
        opacity: 0;
        transition: transform .32s cubic-bezier(.2, .9, .2, 1), opacity .22s ease;
        will-change: transform, opacity;
      }

      .children-panel.show {
        border: 3px solid #9aa0a6;
        background: linear-gradient(180deg, #fbfcfd, #f5f8fd);
        border-radius: 0 0 1rem 1rem;
        box-sizing: border-box;
        width: 100%;
        margin-top: -3px;
        max-height: 1400px;
        opacity: 1;
      }

      .children-panel.show .card,
      .children-panel.collapse.show .card {
        transform: translateY(0);
        opacity: 1;
        transition-delay: .02s;
      }

      .children-panel.collapse .card {
        transform: translateY(-8px);
        opacity: 0;
        transition: transform .32s cubic-bezier(.2, .9, .2, 1), opacity .22s ease;
        will-change: transform, opacity;
      }

      .expand-icon {
        transition: transform .22s ease;
      }

      .table {
        border-collapse: collapse;
      }

      .table td,
      .table th {
        border-spacing: 0;
      }

      .sector-section-card {
        border-radius: 20px;
        border: 1px solid rgba(18, 49, 92, 0.08);
        background: rgba(255, 255, 255, 0.8);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
      }

      .sector-section-card .card-body {
        padding: 1.25rem;
      }

      .sector-modal .modal-content {
        border: 1px solid rgba(18, 49, 92, 0.08);
        border-radius: 24px;
        box-shadow: 0 28px 60px rgba(18, 49, 92, 0.18);
        overflow: hidden;
      }

      .sector-modal .modal-header {
        padding: 1.1rem 1.35rem;
        background: linear-gradient(135deg, rgba(18, 49, 92, 0.04), rgba(27, 110, 243, 0.08));
        border-bottom-color: rgba(18, 49, 92, 0.08);
      }

      .sector-modal .modal-title {
        font-weight: 800;
        letter-spacing: -.02em;
      }

      .sector-modal .modal-footer {
        border-top-color: rgba(18, 49, 92, 0.08);
      }

      .sector-modal .form-control,
      .sector-modal .form-select {
        border-radius: 14px;
        border-color: rgba(18, 49, 92, 0.12);
        min-height: 46px;
      }

      .manage-sub-modal-shell {
        padding: .25rem;
      }

      .manage-sub-hero {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        padding: 1rem 1.1rem;
        margin-bottom: 1rem;
        border: 1px solid rgba(18, 49, 92, 0.08);
        border-radius: 20px;
        background: linear-gradient(135deg, rgba(18, 49, 92, 0.04), rgba(27, 110, 243, 0.08));
      }

      .manage-sub-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 800;
        letter-spacing: -.02em;
      }

      .manage-sub-copy {
        margin: .3rem 0 0;
        color: #5c6f88;
        font-size: .92rem;
      }

      .manage-sub-stats {
        display: flex;
        flex-wrap: wrap;
        gap: .6rem;
      }

      .manage-sub-stat {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .55rem .8rem;
        border: 1px solid rgba(18, 49, 92, 0.08);
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.88);
        font-size: .82rem;
        font-weight: 700;
        color: #12315c;
      }

      .manage-sub-stat strong {
        font-size: .95rem;
      }

      .manage-sub-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: .95rem;
      }

      .manage-sub-toolbar-copy {
        color: #5c6f88;
        font-size: .9rem;
      }

      .manage-sub-inline-card {
        display: none;
        margin-bottom: 1rem;
        padding: 1rem;
        border: 1px solid rgba(18, 49, 92, 0.08);
        border-radius: 18px;
        background: rgba(248, 251, 255, 0.96);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9);
      }

      .manage-sub-inline-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: .8rem;
      }

      .manage-sub-inline-head h6 {
        margin: 0;
        font-weight: 800;
      }

      .manage-sub-inline-head p {
        margin: .2rem 0 0;
        color: #5c6f88;
        font-size: .88rem;
      }

      .manage-sub-table-wrap {
        border: 1px solid rgba(18, 49, 92, 0.08);
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.94);
        overflow: hidden;
      }

      .manage-sub-table {
        --bs-table-bg: transparent;
        --bs-table-hover-bg: rgba(234, 243, 255, 0.42);
        margin-bottom: 0;
      }

      .manage-sub-table thead th {
        border-bottom: 1px solid rgba(18, 49, 92, 0.08);
        background: rgba(248, 251, 255, 0.94);
        color: #5c6f88;
        font-size: .76rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
        padding-top: .95rem;
        padding-bottom: .95rem;
      }

      .manage-sub-table tbody td {
        vertical-align: middle;
        padding-top: .95rem;
        padding-bottom: .95rem;
        border-color: rgba(18, 49, 92, 0.08);
      }

      .manage-sub-title-cell {
        display: flex;
        align-items: flex-start;
        gap: .8rem;
      }

      .manage-sub-tree-marker {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        height: 34px;
        margin-top: .05rem;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(27, 110, 243, 0.12), rgba(17, 72, 168, 0.18));
        color: #1148a8;
        font-size: .8rem;
        font-weight: 800;
      }

      .manage-sub-title-text {
        min-width: 0;
      }

      .manage-sub-title-text strong {
        display: block;
        font-size: .95rem;
      }

      .manage-sub-title-text span {
        display: block;
        color: #5c6f88;
        font-size: .82rem;
      }

      .manage-sub-url {
        display: inline-block;
        max-width: 240px;
        color: #5c6f88;
        font-size: .84rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      .manage-sub-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        align-items: center;
        justify-content: flex-end;
      }

      .manage-sub-empty {
        padding: 2.5rem 1rem;
        text-align: center;
      }

      .manage-sub-empty strong {
        display: block;
        margin-bottom: .35rem;
        color: #12315c;
      }

      .manage-sub-empty span {
        color: #5c6f88;
        font-size: .9rem;
      }

      @media (max-width: 991.98px) {
        .sector-hero {
          grid-template-columns: 1fr;
        }

        .sector-summary-grid {
          grid-template-columns: repeat(3, minmax(0, 1fr));
        }
      }

      @media (max-width: 767.98px) {
        .sector-utilities-page {
          padding-left: .25rem;
          padding-right: .25rem;
        }

        .sector-hero,
        .sector-panel .card-body,
        .sector-table-wrap {
          padding-left: 1rem;
          padding-right: 1rem;
        }

        .sector-summary-grid {
          grid-template-columns: 1fr;
        }

        .sector-submit-row {
          flex-direction: column;
          align-items: stretch;
        }

        .manage-sub-hero,
        .manage-sub-toolbar,
        .manage-sub-inline-head {
          flex-direction: column;
          align-items: stretch;
        }

        .manage-sub-actions {
          justify-content: flex-start;
        }

        .sector-action-btn {
          width: 100%;
        }
      }
    </style>

    <div class="sector-utilities-page">
        <section class="sector-hero">
            <div class="sector-hero-copy">
                <span class="sector-kicker">Sector Utilities Workspace</span>
                <h3 class="sector-title">STs Report Gallery Utilities</h3>
            </div>
            <div class="sector-summary-grid">
                <div class="sector-summary-card">
                    <span class="sector-summary-label">Gallery Cards</span>
                    <span class="sector-summary-value">{{ $galleryCount }}</span>
                </div>
                <div class="sector-summary-card">
                    <span class="sector-summary-label">Active Cards</span>
                    <span class="sector-summary-value">{{ $activeGalleryCount }}</span>
                </div>
                <div class="sector-summary-card">
                    <span class="sector-summary-label">Child Entries</span>
                    <span class="sector-summary-value">{{ $childCount }}</span>
                </div>
            </div>
        </section>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm">
                <strong>Validation failed — please fix the following:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card sector-panel mb-4">
            <div class="sector-panel-header">
                <div>
                    <h4 class="sector-panel-title">Create gallery card</h4>
                    <p class="sector-panel-text">Add a new sector card with its label, route, status, and optional image preview.</p>
                </div>
                <span class="sector-pill">New entry</span>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.gallery.store') }}" method="POST" enctype="multipart/form-data" class="sector-form-grid">
                    @csrf
                    <div class="row g-3">
                        <div class="col-lg-5">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror" placeholder="e.g. Children and Youth" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">URL</label>
                            <input type="text" name="url" value="{{ old('url') }}" class="form-control @error('url') is-invalid @enderror" placeholder="/category...">
                            @error('url')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="On going" {{ old('status') === 'On going' ? 'selected' : '' }}>On going</option>
                                <option value="Completed" {{ old('status') === 'Completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Short supporting text for this sector card.">{{ old('description') }}</textarea>
                        </div>

                        <div class="col-md-7 col-lg-8">
                            <label class="form-label">Image (optional)</label>
                            <input type="file" name="image" accept="image/*" class="form-control @error('image') is-invalid @enderror">
                            @error('image')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-5 col-lg-4">
                            <label class="form-label">Visibility</label>
                            <div class="sector-toggle-wrap">
                                <div class="form-check">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Set card as active</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="sector-submit-row">
                                <div class="sector-submit-hint">This card will appear in the sector list below and can be expanded to manage children.</div>
                                <button class="btn btn-primary sector-action-btn">Add gallery card</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card sector-panel">
            <div class="sector-panel-header">
                <div>
                    <h4 class="sector-panel-title">Existing gallery cards</h4>
                    <p class="sector-panel-text">Expand any row to manage its children and sub-children, or edit card details directly from the action column.</p>
                </div>
                <span class="sector-pill">{{ $galleryCount }} total</span>
            </div>
            <div class="sector-table-wrap">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle sector-gallery-table">
                        <thead>
                            <tr>
                                <th style="width:110px">Status</th>
                                <th style="width:120px">DocNo</th>
                                <th style="width:100px">Preview</th>
                                <th>Title</th>
                                <th>URL</th>
                                <th style="width:150px">Created By</th>
                                <th style="width:150px">Updated By</th>
                                <th style="width:90px">Active</th>
                                <th style="width:220px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($galleryCards ?? [] as $card)
                                @include('admin._gallery_card_row', ['card' => $card])
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">No gallery cards yet. Use the form above to create the first sector card.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

 
<div class="modal fade sector-modal" id="editChildModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit child</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editChildForm" method="POST" action="" class="ajax-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="editing_child_id" id="editing_child_id" value="{{ old('editing_child_id') }}">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Title</label>
              <input id="modal_title" type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
              @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
              <label class="form-label">URL</label>
              <input id="modal_url" type="text" name="url" class="form-control @error('url') is-invalid @enderror" value="{{ old('url') }}">
              @error('url')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3">
              <label class="form-label">Status</label>
              <select id="modal_status" name="status" class="form-select">
                <option value="On going">On going</option>
                <option value="Completed">Completed</option>
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label">Active</label>
              <select id="modal_is_active" name="is_active" class="form-select">
                <option value="1">Yes</option>
                <option value="0">No</option>
              </select>
            </div>

            <div class="col-md-4 d-flex align-items-center">
              <div class="form-check mt-3">
                <input type="hidden" name="is_mother" value="0">
                <input id="modal_is_mother" class="form-check-input" type="checkbox" name="is_mother" value="1">
                <label class="form-check-label" for="modal_is_mother">Is mother</label>
              </div>
            </div>

            <div class="col-md-4">
              <label class="form-label">DocNo</label>
              <div><span id="modal_docno" class="badge bg-secondary">-</span></div>
            </div>

            <div class="col-12">
              <hr>
              <strong>DocNo history</strong>
              <ul id="modal_docno_history" class="list-unstyled small mt-2 mb-0"></ul>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

 
<div class="modal fade sector-modal" id="addChildModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add child</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addChildForm" method="POST" action="" class="ajax-form">
        @csrf
        <input type="hidden" name="parent_card_id" id="add_parent_card_id" value="{{ old('parent_card_id') }}">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Title</label>
              <input id="add_child_title" type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
              @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">URL (optional)</label>
              <input id="add_child_url" type="text" name="url" class="form-control @error('url') is-invalid @enderror" value="{{ old('url') }}">
              @error('url')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
              <label class="form-label">Active</label>
              <select id="add_child_is_active" name="is_active" class="form-select">
                <option value="1">Yes</option>
                <option value="0">No</option>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label">Status</label>
              <select id="add_child_status" name="status" class="form-select">
                <option value="On going">On going</option>
                <option value="Completed">Completed</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-success">Add Child</button>
        </div>
      </form>
    </div>
  </div>
</div>

 
<div class="modal fade sector-modal" id="addSubChildModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add sub-child</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addSubChildForm" method="POST" action="" class="ajax-form">
        @csrf
        <input type="hidden" name="parent_card_id" id="add_sub_parent_card_id" value="{{ old('parent_card_id') }}">
        <input type="hidden" name="parent_child_id" id="add_parent_child_id" value="{{ old('parent_child_id') }}">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Title</label>
              <input id="add_sub_title" type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
              @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">URL (optional)</label>
              <input id="add_sub_url" type="text" name="url" class="form-control @error('url') is-invalid @enderror" value="{{ old('url') }}">
              @error('url')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
              <label class="form-label">Active</label>
              <select id="add_sub_is_active" name="is_active" class="form-select">
                <option value="1">Yes</option>
                <option value="0">No</option>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label">Status</label>
              <select id="add_sub_status" name="status" class="form-select">
                <option value="On going">On going</option>
                <option value="Completed">Completed</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-success">Add Sub-child</button>
        </div>
      </form>
    </div>
  </div>
</div>

 
<div class="modal fade sector-modal" id="manageSubChildrenModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Sub-children</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body manage-sub-modal-shell">
        <div class="manage-sub-hero">
          <div>
            <h6 class="manage-sub-title" id="manageSub_childTitle">Sub-children</h6>
            <p class="manage-sub-copy">Review nested items, update details, and add deeper entries without leaving this panel.</p>
          </div>
          <div class="manage-sub-stats">
            <span class="manage-sub-stat"><strong id="manageSub_childCount">0</strong> items</span>
            <span class="manage-sub-stat"><strong id="manageSub_activeCount">0</strong> active</span>
          </div>
        </div>

        <div class="manage-sub-toolbar">
          <div class="manage-sub-toolbar-copy">Use this view to manage all nested records under the selected mother entry.</div>
          <div>
            <button type="button" class="btn btn-sm btn-success sector-action-btn" id="manageSub_addSubchildBtn">Add Sub-child</button>
          </div>
        </div>

        <!-- inline Add Sub-child form (appears inside this modal) -->
        <div id="manageSub_inlineAddWrap" class="manage-sub-inline-card">
          <form id="manageSub_inlineAddForm" method="POST" action="" class="ajax-form">
            @csrf
            <input type="hidden" name="parent_card_id" id="manageSub_add_parent_card_id" value="">
            <input type="hidden" name="parent_child_id" id="manageSub_add_parent_child_id" value="">
            <input type="hidden" name="from_manage_modal" id="manageSub_from_manage_modal" value="1">

            <div class="manage-sub-inline-head">
              <div>
                <h6>Add a new sub-child</h6>
                <p>Fill in the key fields below to add another nested record.</p>
              </div>
              <span class="sector-pill">Inline create</span>
            </div>

            <div class="row g-2 align-items-end">
              <div class="col-md-4">
                <label class="form-label">Title</label>
                <input id="manageSub_add_title" name="title" type="text" class="form-control" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">URL (optional)</label>
                <input id="manageSub_add_url" name="url" type="text" class="form-control">
              </div>
              <div class="col-md-2">
                <label class="form-label">Active</label>
                <select id="manageSub_add_is_active" name="is_active" class="form-select">
                  <option value="1">Yes</option>
                  <option value="0">No</option>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label">Status</label>
                <select id="manageSub_add_status" name="status" class="form-select">
                  <option value="On going">On going</option>
                  <option value="Completed">Completed</option>
                </select>
              </div>
            </div>

            <div class="mt-3 text-end d-flex gap-2 justify-content-end flex-wrap">
              <button type="button" class="btn btn-sm btn-secondary" id="manageSub_inlineCancelBtn">Cancel</button>
              <button type="submit" class="btn btn-sm btn-success sector-action-btn">Add Sub-child</button>
            </div>
          </form>
        </div>

        <div class="table-responsive manage-sub-table-wrap">
          <table class="table table-sm table-hover manage-sub-table" id="manageSub_table">
            <thead>
              <tr><th>Title</th><th>DocNo</th><th>URL</th><th>Active</th><th>Status</th><th>Created By</th><th style="width:170px">Actions</th></tr>
            </thead>
            <tbody id="manageSub_tbody">
              <tr><td colspan="7" class="manage-sub-empty"><strong>No sub-children yet</strong><span>Add a sub-child to start building this nested structure.</span></td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

 
<div class="modal fade sector-modal" id="editCardModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit gallery card</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editCardForm" method="POST" action="" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="editing_gallery_id" id="editing_gallery_id_card" value="{{ old('editing_gallery_id') }}">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Title</label>
              <input id="card_modal_title" type="text" name="title" class="form-control {{ (old('editing_gallery_id') && $errors->has('title')) ? 'is-invalid' : '' }}" value="{{ old('title') }}" required>
              @if(old('editing_gallery_id')) @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <div class="col-md-3">
              <label class="form-label">Active</label>
              <select id="card_modal_is_active" name="is_active" class="form-select {{ (old('editing_gallery_id') && $errors->has('is_active')) ? 'is-invalid' : '' }}">
                <option value="1" {{ (old('editing_gallery_id') && old('is_active') == '1') ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ (old('editing_gallery_id') && old('is_active') == '0') ? 'selected' : '' }}>No</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Status</label>
              <select id="card_modal_status" name="status" class="form-select {{ (old('editing_gallery_id') && $errors->has('status')) ? 'is-invalid' : '' }}">
                <option value="On going" {{ (old('editing_gallery_id') && old('status') == 'On going') ? 'selected' : '' }}>On going</option>
                <option value="Completed" {{ (old('editing_gallery_id') && old('status') == 'Completed') ? 'selected' : '' }}>Completed</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">URL</label>
              <input id="card_modal_url" type="text" name="url" class="form-control {{ (old('editing_gallery_id') && $errors->has('url')) ? 'is-invalid' : '' }}" value="{{ old('url') }}">
              @if(old('editing_gallery_id')) @error('url')<div class="text-danger small">{{ $message }}</div>@enderror @endif
            </div>



            <div class="col-md-6">
              <label class="form-label">Image (replace)</label>
              <input id="card_modal_image" type="file" name="image" accept="image/*" class="form-control form-control-sm {{ (old('editing_gallery_id') && $errors->has('image')) ? 'is-invalid' : '' }}">
              @if(old('editing_gallery_id')) @error('image')<div class="text-danger small">{{ $message }}</div>@enderror @endif
            </div>

            <div class="col-md-6">
              <label class="form-label"></label>
              <div id="card_modal_preview" class="mt-1"></div>
              <style>
                .gallery-thumb { display:inline-block; vertical-align:middle; }
              </style>
            </div>

          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function replaceCardRow(cardId, html){
    var parser = new DOMParser();
    var doc = parser.parseFromString('<table>' + html + '</table>', 'text/html');
    var newGalleryRow = doc.querySelector('tr.gallery-row');
    var newChildrenRow = doc.querySelector('tr.children-row');
    if(!newGalleryRow || !newChildrenRow) return;
    var galleryRow = document.querySelector('tr.gallery-row[data-bs-target="#children-panel-' + cardId + '"]');
    var childrenRow = document.getElementById('children-row-' + cardId);
    var wasOpen = false;
    if(childrenRow){
        var panelEl = childrenRow.querySelector('.children-panel');
        wasOpen = panelEl && panelEl.classList.contains('show');
    }
    if(galleryRow && childrenRow) {
        galleryRow.parentNode.replaceChild(newGalleryRow, galleryRow);
        childrenRow.parentNode.replaceChild(newChildrenRow, childrenRow);
    }
    initGalleryRowEvents();
    initChildControlListeners();
    initAjaxForms();
    if(wasOpen){
        var newPanel = document.querySelector('#children-panel-' + cardId);
        if(newPanel){
            var inst = bootstrap.Collapse.getInstance(newPanel);
            if(!inst) inst = new bootstrap.Collapse(newPanel, {toggle:false});
            inst.show();
        }
    }
}

function ajaxSubmit(form, successCb, errorCb){
    var override = (form.querySelector('input[name="_method"]') || {}).value;
    var method = override || form.method || 'POST';
    var url = form.action;
    var data = new FormData(form);
    var fetchMethod = method.toUpperCase();
    if (override) fetchMethod = 'POST';

    var headers = {
        'X-Requested-With': 'XMLHttpRequest'
    };
    var meta = document.querySelector('meta[name="csrf-token"]');
    if(meta){ headers['X-CSRF-TOKEN'] = meta.getAttribute('content'); }
    console.debug('ajaxSubmit', fetchMethod, url);
    data.forEach(function(v,k){ console.debug('  ', k, v); });
    fetch(url, {
        method: fetchMethod,
        headers: headers,
        body: data
    }).then(function(resp){
        if(resp.ok) return resp.json();
        return resp.json().then(function(j){ throw j; });
    }).then(function(json){
        if(json.success){
            successCb && successCb(json);
        } else {
            errorCb && errorCb(json);
        }
        if(typeof hideLoader === 'function') hideLoader();
    }).catch(function(err){
        console.error('ajax error', err);
        if(typeof hideLoader === 'function') hideLoader();
        errorCb && errorCb(err);
    });
}

function safeHideModal(mod){
  try {
    var bs = bootstrap.Modal.getInstance(mod);
    if(bs) bs.hide();
  } catch(e){ console.error('safeHideModal hide error', e); }

  setTimeout(function(){
    try {
      document.querySelectorAll('.modal-backdrop').forEach(function(b){ b.remove(); });
      document.body.classList.remove('modal-open');
    } catch(e){}
  }, 120);
}

function showFormErrors(form, errors){
    form.querySelectorAll('.is-invalid').forEach(function(el){ el.classList.remove('is-invalid'); });
    form.querySelectorAll('.invalid-feedback').forEach(function(el){ el.remove(); });
    if(!errors) return;
    Object.entries(errors).forEach(function([field,msgs]){
        var input = form.querySelector('[name="'+field+'"]');
        if(!input) return;
        input.classList.add('is-invalid');
        var fb = document.createElement('div');
        fb.className = 'invalid-feedback';
        fb.textContent = msgs[0] || '';
        if(input.parentNode) input.parentNode.appendChild(fb);
    });
}

function initAjaxForms(){
    document.querySelectorAll('form.ajax-form').forEach(function(f){
        if (f.dataset.ajaxBound) return;
        f.dataset.ajaxBound = '1';
        f.addEventListener('submit', function(e){
            e.preventDefault();
            ajaxSubmit(f, function(json){
                if(json.rowHtml){ replaceCardRow(json.card_id, json.rowHtml); }
            });
        });
    });
}

function openEditChildModal(data){
    var id = data.id;
    var modal = document.getElementById('editChildModal');
    var form = document.getElementById('editChildForm');
    form.action = '/admin/gallery-children/' + id; 
    document.getElementById('editing_child_id').value = id;
    document.getElementById('modal_title').value = data.title || '';
    document.getElementById('modal_url').value = data.url || '';
    document.getElementById('modal_is_active').value = data.is_active ? '1' : '0';
    if (document.getElementById('modal_status')) document.getElementById('modal_status').value = data.status || 'On going';
    document.getElementById('modal_is_mother').checked = data.is_mother ? true : false;
    document.getElementById('modal_docno').textContent = data.docno || '-';

    var histEl = document.getElementById('modal_docno_history');
    histEl.textContent = '';
    (data.histories || []).forEach(function(h){
      var li = document.createElement('li');
      var histHtml = '<strong>' + (h.docno || '') + '</strong>' +
               ' <span class="text-muted">(previous: ' + (h.previous_docno||'-') + ')</span>' +
               ' — <em>' + (h.creator || '') + '</em> <small class="text-muted">' + (h.created_at || '') + '</small>';
      li.innerHTML = sanitizeHtml(histHtml);
      histEl.appendChild(li);
    });

    try {
        var visibleModals = document.querySelectorAll('.modal.show');
        if (visibleModals.length > 0) {
            var highest = 1050;
            visibleModals.forEach(function(m){
                var z = parseInt(window.getComputedStyle(m).zIndex, 10) || 1050;
                if (z > highest) highest = z;
            });
            modal.style.zIndex = (highest + 20);
        } else {
            modal.style.zIndex = '';
        }
    } catch (e) {  }

    var bsModal = new bootstrap.Modal(modal);
    bsModal.show();

    setTimeout(function(){
        try {
            var backdrops = document.querySelectorAll('.modal-backdrop.show');
            if (backdrops.length) {
                var topBackdrop = backdrops[backdrops.length - 1];
                var modalZ = parseInt(modal.style.zIndex || window.getComputedStyle(modal).zIndex, 10) || 1070;
                topBackdrop.style.zIndex = (modalZ - 10);
            }
        } catch (e) {  }
    }, 0);

    modal.addEventListener('hidden.bs.modal', function cleanupStacking(){
        modal.style.zIndex = '';
        try {
            var bps = document.querySelectorAll('.modal-backdrop.show');
            if (bps.length) {
                var last = bps[bps.length - 1];
                if (last) last.style.zIndex = '';
            }
        } catch(e){}
        modal.removeEventListener('hidden.bs.modal', cleanupStacking);
    });
}

function openAddChildModal(data){
    var cardId = data.cardId || data.parent_card_id || null;
    var modal = document.getElementById('addChildModal');
    var form = document.getElementById('addChildForm');
    form.action = '/admin/gallery-cards/' + cardId + '/children';
    document.getElementById('add_parent_card_id').value = cardId || '';
    document.getElementById('add_child_title').value = data.title || '';
    document.getElementById('add_child_url').value = data.url || '';
    document.getElementById('add_child_is_active').value = data.is_active ? '1' : '0';
    if (document.getElementById('add_child_status')) document.getElementById('add_child_status').value = data.status || 'On going';
    var bs = new bootstrap.Modal(modal);
    bs.show();
}

function openAddSubChildModal(data){
    var cardId = data.cardId || data.parent_card_id || null;
    var parentChildId = data.parentChildId || data.parent_child_id || null;
    var modal = document.getElementById('addSubChildModal');
    var form = document.getElementById('addSubChildForm');
    form.action = '/admin/gallery-cards/' + cardId + '/children';
    document.getElementById('add_sub_parent_card_id').value = cardId || '';
    document.getElementById('add_parent_child_id').value = parentChildId || '';
    document.getElementById('add_sub_title').value = data.title || '';
    document.getElementById('add_sub_url').value = data.url || '';
    document.getElementById('add_sub_is_active').value = data.is_active ? '1' : '0';
    if (document.getElementById('add_sub_status')) document.getElementById('add_sub_status').value = data.status || 'On going';
    var bs = new bootstrap.Modal(modal);
    bs.show();
}

function openEditCardModal(data){
    var id = data.id;
    var modal = document.getElementById('editCardModal');
    var form = document.getElementById('editCardForm');
    form.action = '/admin/gallery-cards/' + id; 
    document.getElementById('editing_gallery_id_card').value = id;
    document.getElementById('card_modal_title').value = data.title || '';
    document.getElementById('card_modal_url').value = data.url || '';
    document.getElementById('card_modal_is_active').value = data.is_active ? '1' : '0';
    if (document.getElementById('card_modal_status')) document.getElementById('card_modal_status').value = data.status || 'On going';

    var prev = document.getElementById('card_modal_preview');
    prev.textContent = '';
    if (data.image) {
        var img = document.createElement('img');
        img.src = data.image;
        img.style.maxHeight = '64px';
        img.style.objectFit = 'contain';
        prev.appendChild(img);
    }

    try {
        var visibleModals = document.querySelectorAll('.modal.show');
        if (visibleModals.length > 0) {
            var highest = 1050;
            visibleModals.forEach(function(m){
                var z = parseInt(window.getComputedStyle(m).zIndex, 10) || 1050;
                if (z > highest) highest = z;
            });
            modal.style.zIndex = (highest + 20);
        } else {
            modal.style.zIndex = '';
        }
    } catch (e) {}

    var bsModal = new bootstrap.Modal(modal);
    bsModal.show();

    setTimeout(function(){
        try {
            var backdrops = document.querySelectorAll('.modal-backdrop.show');
            if (backdrops.length) {
                var topBackdrop = backdrops[backdrops.length - 1];
                var modalZ = parseInt(modal.style.zIndex || window.getComputedStyle(modal).zIndex, 10) || 1070;
                topBackdrop.style.zIndex = (modalZ - 10);
            }
        } catch (e) {}
    }, 0);

    modal.addEventListener('hidden.bs.modal', function cleanupCardStacking(){
        modal.style.zIndex = '';
        try {
            var bps = document.querySelectorAll('.modal-backdrop.show');
            if (bps.length) {
                var last = bps[bps.length - 1];
                if (last) last.style.zIndex = '';
            }
        } catch(e){}
        modal.removeEventListener('hidden.bs.modal', cleanupCardStacking);
    });
}


function initGalleryRowEvents(){
    document.querySelectorAll('.gallery-row').forEach(function(row){
        row.addEventListener('click', function(e){
            if (e.target.closest('button, a, input, select, textarea, form')) return;
            var sel = row.getAttribute('data-bs-target');
            if (!sel) return;
            var targetEl = document.querySelector(sel);
            if (!targetEl) return;
            var inst = bootstrap.Collapse.getInstance(targetEl);
            if (!inst) inst = new bootstrap.Collapse(targetEl, {toggle: false});
            inst.toggle();
        });

        var icon = row.querySelector('.expand-icon');
        var sel = row.getAttribute('data-bs-target');
        if (!sel) return;
        var panel = document.querySelector(sel);
        if (!panel || !icon) return;
        if (panel.classList.contains('show')) { icon.style.transform = 'rotate(0deg)'; row.classList.add('expanded'); }

        panel.addEventListener('show.bs.collapse', function(){
            icon.style.transform = 'rotate(0deg)';
            row.classList.add('expanded');
            row.classList.add('animating');
        });
        panel.addEventListener('shown.bs.collapse', function(){
            row.classList.remove('animating');
        });
        panel.addEventListener('hide.bs.collapse', function(){
            icon.style.transform = 'rotate(-90deg)';
            row.classList.add('animating');
            row.classList.remove('expanded');
        });
        panel.addEventListener('hidden.bs.collapse', function(){
            row.classList.remove('animating');
        });
    });
}

function initChildControlListeners(){
    document.querySelectorAll('.btn-edit-child').forEach(function(btn){
        btn.addEventListener('click', function(){
            var data = {
                id: btn.getAttribute('data-id'),
                title: btn.getAttribute('data-title'),
                url: btn.getAttribute('data-url'),
                is_active: parseInt(btn.getAttribute('data-is-active') || '1', 10),
                status: btn.getAttribute('data-status') || 'On going',
                is_mother: parseInt(btn.getAttribute('data-is-mother') || '0', 10),
                docno: btn.getAttribute('data-docno') || '-',
                histories: []
            };
            try { data.histories = JSON.parse(btn.getAttribute('data-histories') || '[]'); } catch(e){ data.histories = []; }
            openEditChildModal(data);
        });
    });

    document.querySelectorAll('.btn-edit-card').forEach(function(btn){
        btn.addEventListener('click', function(e){
            e.stopPropagation();
            var data = {
                id: btn.getAttribute('data-id'),
                title: btn.getAttribute('data-title'),
                url: btn.getAttribute('data-url'),
                is_active: parseInt(btn.getAttribute('data-is-active') || '1', 10)
            };
            openEditCardModal(data);
        });
    });

    document.querySelectorAll('.btn-open-add-child').forEach(function(btn){
        btn.addEventListener('click', function(){
            var cardId = btn.getAttribute('data-card-id');
            openAddChildModal({ cardId: cardId });
        });
    });

    document.querySelectorAll('.btn-open-add-subchild').forEach(function(btn){
        btn.addEventListener('click', function(){
            var cardId = btn.getAttribute('data-card-id');
            var parentChildId = btn.getAttribute('data-parent-child-id');
            openAddSubChildModal({ cardId: cardId, parentChildId: parentChildId });
        });
    });
}

document.addEventListener('DOMContentLoaded', function(){
    initGalleryRowEvents();
    initChildControlListeners();


    ['addChildForm','editChildForm','addSubChildForm','manageSub_inlineAddForm'].forEach(function(id){
      var form = document.getElementById(id);
      if(form && !form.dataset.ajaxBound){
        form.dataset.ajaxBound = '1';
        form.addEventListener('submit', function(e){
          e.preventDefault();
          ajaxSubmit(form, function(json){
            if(json.rowHtml){
              replaceCardRow(json.card_id, json.rowHtml);
              if (currentManageState && document.getElementById('manageSubChildrenModal').classList.contains('show')) {
                var btn = document.querySelector('.btn-manage-subchildren[data-card-id="'+currentManageState.cardId+'"][data-child-id="'+currentManageState.childId+'"]');
                if(btn){
                  try { currentManageState.subs = JSON.parse(btn.getAttribute('data-subchildren')||'[]'); } catch(e){currentManageState.subs = [];}                                
                }
                populateManageSubModal(currentManageState);
              }
            }
            var mod = form.closest('.modal');
            if(mod){
              try { safeHideModal(mod); } catch(e){ console.error(e); }
            }
          }, function(err){
            if(err && err.errors){
              showFormErrors(form, err.errors);
            }
          });
        });
      }
    });

    var currentManageState = null;

    function populateManageSubModal(state){
        if(!state) return;
        var cardId = state.cardId;
        var childId = state.childId;
        var childTitle = state.childTitle || 'Sub-children';
        var subs = state.subs || [];
        var parentHistories = state.parentHistories || [];

        var modal = document.getElementById('manageSubChildrenModal');
        document.getElementById('manageSub_childTitle').textContent = 'Sub-children for "' + childTitle + '"';
        document.getElementById('manageSub_childCount').textContent = (subs.length || 0);
        document.getElementById('manageSub_activeCount').textContent = countActiveNodes(subs);

        var tbody = document.getElementById('manageSub_tbody');
        tbody.textContent = '';

        var parentNode = {
            id: childId,
            title: childTitle,
            docno: state.docno || '-',
            url: state.url || '',
            is_active: state.is_active || 0,
            status: state.status || 'On going',
            created_by: state.created_by || '',
            histories: parentHistories,
            children: subs || []
        };

          function countActiveNodes(nodes){
            var total = 0;
            (nodes || []).forEach(function(node){
              if (node.is_active) total += 1;
              if (node.children && node.children.length) total += countActiveNodes(node.children);
            });
            return total;
          }

          function escapeHtml(value){
            return String(value || '')
              .replace(/&/g, '&amp;')
              .replace(/</g, '&lt;')
              .replace(/>/g, '&gt;')
              .replace(/"/g, '&quot;')
              .replace(/'/g, '&#39;');
          }

        function renderSubRows(nodes, level){
            nodes.forEach(function(n){
                var tr = document.createElement('tr');
              var padding = 8 + (level * 18);
              var childBadge = (n.children && n.children.length) ? '<span class="badge rounded-pill text-bg-light border ms-2">' + n.children.length + ' nested</span>' : '';
              var titleHtml = '<div class="manage-sub-title-cell" style="padding-left:' + padding + 'px;">' +
                        '<span class="manage-sub-tree-marker">L' + (level + 1) + '</span>' +
                        '<div class="manage-sub-title-text">' +
                          '<strong>' + escapeHtml(n.title || 'Untitled sub-child') + childBadge + '</strong>' +
                          '<span>' + ((n.children && n.children.length) ? 'Has nested descendants.' : 'Leaf node in this branch.') + '</span>' +
                        '</div>' +
                      '</div>';
                var histAttr = ' data-histories="' + (n.histories ? (JSON.stringify(n.histories).replace(/'/g,'&#39;').replace(/\"/g,'&quot;')) : '[]') + '"';
              var activeBadge = '<span class="badge rounded-pill ' + (n.is_active ? 'text-bg-success' : 'text-bg-secondary') + '">' + (n.is_active ? 'Yes' : 'No') + '</span>';
              var statusBadge = '<span class="badge rounded-pill ' + ((n.status || 'On going') === 'Completed' ? 'text-bg-success' : 'text-bg-warning') + '">' + escapeHtml(n.status || 'On going') + '</span>';

                var rowHtml = '<td>' + titleHtml + '</td>' +
                       '<td><span class="badge rounded-pill text-bg-light border px-3 py-2">' + escapeHtml(n.docno || '-') + '</span></td>' +
                       '<td><span class="manage-sub-url">' + escapeHtml(n.url || '-') + '</span></td>' +
                       '<td>' + activeBadge + '</td>' +
                       '<td>' + statusBadge + '</td>' +
                       '<td>' + escapeHtml(n.created_by || '') + '</td>' +
                               '<td style="white-space:nowrap; width:170px;">' +
                       '<div class="manage-sub-actions">' +
                         '<button type="button" class="btn btn-sm btn-outline-secondary btn-edit-subchild" aria-label="Edit sub-child" data-id="'+n.id+'" data-title="'+escapeHtml(n.title||'')+'" data-url="'+escapeHtml(n.url||'')+'" data-docno="'+escapeHtml(n.docno||'')+'" data-is-active="'+(n.is_active?1:0)+'" data-status="'+escapeHtml(n.status||'On going')+'"'+histAttr+'>Edit</button>' +
                                   '<form action="/admin/gallery-children/'+n.id+'" method="POST" class="m-0 ajax-form" style="display:inline-block;margin:0;" onsubmit="return confirm(\'Delete this sub-child?\');">' +
                                      '@csrf'.replace('@csrf','{!! csrf_field() !!}') +
                                      '<input type="hidden" name="_method" value="DELETE">' +
                          '<button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Delete sub-child">Delete</button>' +
                                   '</form>' +
                                 '</div>' +
                               '</td>';
                tr.innerHTML = sanitizeHtml(rowHtml);
                tbody.appendChild(tr);
                if (n.children && n.children.length) renderSubRows(n.children, level + 1);
            });
        }

          if (parentNode.children && parentNode.children.length) {
            renderSubRows(parentNode.children || [], 0);
          } else {
            tbody.innerHTML = sanitizeHtml('<tr><td colspan="7" class="manage-sub-empty"><strong>No sub-children yet</strong><span>Add a sub-child to start building this nested structure.</span></td></tr>');
          }

        tbody.querySelectorAll('.btn-edit-subchild').forEach(function(b){
            b.addEventListener('click', function(){
                var data = {
                    id: b.getAttribute('data-id'),
                    title: b.getAttribute('data-title'),
                    url: b.getAttribute('data-url'),
                    docno: b.getAttribute('data-docno'),
                    is_active: parseInt(b.getAttribute('data-is-active')||'1',10),
                    status: b.getAttribute('data-status') || 'On going',
                    is_mother: 0,
                    histories: []
                };
                try { data.histories = JSON.parse(b.getAttribute('data-histories') || '[]'); } catch(e){ data.histories = []; }
                openEditChildModal(data);
            });
        });
        initAjaxForms();

        try {
          var headerBtn = document.getElementById('manageSub_addSubchildBtn');
          if (headerBtn) {
            headerBtn.onclick = function(){
              console.log('manageSub header Add clicked', cardId, childId);
              showManageSubInlineAdd(cardId, childId);
            };
          }
        } catch(e){ console.error('bind header add error', e); }
    }

      window.populateManageSubModal = populateManageSubModal;

      window.triggerManageSubchildren = function(btn){
        try {
          var b = btn;
          var cardId = b.getAttribute('data-card-id');
          var childId = b.getAttribute('data-child-id');
          var childTitle = b.getAttribute('data-child-title') || 'Sub-children';
          var subs = [];
          try { subs = JSON.parse(b.getAttribute('data-subchildren') || '[]'); } catch(e){ subs = []; }
          var parentHistories = [];
          try { parentHistories = JSON.parse(b.getAttribute('data-child-histories') || '[]'); } catch(e){ parentHistories = []; }

          var state = {
            cardId: cardId,
            childId: childId,
            childTitle: childTitle,
            subs: subs,
            parentHistories: parentHistories,
            docno: b.getAttribute('data-child-docno') || '-',
            url: b.getAttribute('data-child-url') || '',
            is_active: parseInt(b.getAttribute('data-child-is-active') || '0',10),
            status: b.getAttribute('data-child-status') || 'On going',
            created_by: b.getAttribute('data-child-created-by') || ''
          };

          if(window.populateManageSubModal) window.populateManageSubModal(state);
          var modal = document.getElementById('manageSubChildrenModal');
          if(modal){ var bs = new bootstrap.Modal(modal); bs.show(); }
        } catch(e) { console.error('triggerManageSubchildren error', e); }
        return false;
      };

    document.querySelectorAll('.btn-manage-subchildren').forEach(function(btn){
        btn.addEventListener('click', function(){
            var cardId = btn.getAttribute('data-card-id');
            var childId = btn.getAttribute('data-child-id');
            var childTitle = btn.getAttribute('data-child-title') || 'Sub-children';
            var subs = [];
            try { subs = JSON.parse(btn.getAttribute('data-subchildren') || '[]'); } catch(e){ subs = []; }

            var parentHistories = [];
            try { parentHistories = JSON.parse(btn.getAttribute('data-child-histories') || '[]'); } catch(e){ parentHistories = []; }

            currentManageState = { cardId, childId, childTitle, subs, parentHistories,
                docno: btn.getAttribute('data-child-docno') || '-',
                url: btn.getAttribute('data-child-url') || '',
                is_active: parseInt(btn.getAttribute('data-child-is-active') || '0',10),
                status: btn.getAttribute('data-child-status') || 'On going',
                created_by: btn.getAttribute('data-child-created-by') || ''
            };

            populateManageSubModal(currentManageState);

            var modal = document.getElementById('manageSubChildrenModal');
            var bs = new bootstrap.Modal(modal);
            bs.show();
        });

        window.showManageSubInlineAdd = function(a,b,c){ showManageSubInlineAdd(a,b,c); };
    });

            function renderSubRows(nodes, level){
                nodes.forEach(function(n){
                    var tr = document.createElement('tr');
                    var indent = '<div style="padding-left:' + (level*18) + 'px;">' + (n.title || '') + '</div>';
                    var titleHtml = indent + (n.children && n.children.length ? ' <span class="badge bg-secondary ms-2">' + n.children.length + '</span>' : '');
                    var histAttr = ' data-histories="' + (n.histories ? (JSON.stringify(n.histories).replace(/'/g,'&#39;').replace(/\"/g,'&quot;')) : '[]') + '"';

                    var rowHtml = '<td>' + titleHtml + '</td>' +
                                   '<td>' + (n.docno || '') + '</td>' +
                                   '<td><small class="text-muted">' + (n.url || '-') + '</small></td>' +
                                   '<td>' + (n.is_active ? 'Yes' : 'No') + '</td>' +
                                   '<td>' + (n.status || 'On going') + '</td>' +
                                   '<td>' + (n.created_by || '') + '</td>' +
                                   '<td>' + (n.updated_by || '') + '</td>' +
                                   '<td style="white-space:nowrap; width:170px;">' +
                                     '<div class="d-flex gap-2 flex-wrap" style="display:flex;gap:.5rem;align-items:center;">' +
                                       '<button type="button" class="btn btn-sm btn-secondary btn-edit-subchild" aria-label="Edit sub-child" data-id="'+n.id+'" data-title="'+(n.title||'')+'" data-url="'+(n.url||'')+'" data-docno="'+(n.docno||'')+'" data-is-active="'+(n.is_active?1:0)+'" data-status="'+(n.status||'On going')+'"'+histAttr+'>Edit</button>' +
                                       '<form action="/admin/gallery-children/'+n.id+'" method="POST" class="m-0 ajax-form" style="display:inline-block;margin:0;" onsubmit="return confirm(\'Delete this sub-child?\');">' +
                                          '@csrf'.replace('@csrf','{!! csrf_field() !!}') +
                                          '<input type="hidden" name="_method" value="DELETE">' +
                                          '<button type="submit" class="btn btn-sm btn-danger" aria-label="Delete sub-child">Delete</button>' +
                                       '</form>' +
                                     '</div>' +
                                   '</td>';
                    tr.innerHTML = sanitizeHtml(rowHtml);
                    tbody.appendChild(tr);
                    if (n.children && n.children.length) renderSubRows(n.children, level + 1);
                });
            }

            renderSubRows(parentNode.children || [], 0);

            tbody.querySelectorAll('.btn-edit-subchild').forEach(function(b){
                b.addEventListener('click', function(){
                    var data = {
                        id: b.getAttribute('data-id'),
                        title: b.getAttribute('data-title'),
                        url: b.getAttribute('data-url'),
                        docno: b.getAttribute('data-docno'),
                        is_active: parseInt(b.getAttribute('data-is-active')||'1',10),
                        status: b.getAttribute('data-status') || 'On going',
                        is_mother: 0,
                        histories: []
                    };
                    try { data.histories = JSON.parse(b.getAttribute('data-histories') || '[]'); } catch(e){ data.histories = []; }
                    openEditChildModal(data);
                });
            });
            initAjaxForms();
            initAjaxForms();
            initAjaxForms();

            var addBtn = document.getElementById('manageSub_addSubchildBtn');
            if (addBtn) {
              addBtn.onclick = function(){
                try {
                  if (currentManageState && currentManageState.cardId) {
                    showManageSubInlineAdd(currentManageState.cardId, currentManageState.childId);
                  } else {
                    var fallbackCard = document.getElementById('manageSub_add_parent_card_id') ? document.getElementById('manageSub_add_parent_card_id').value : '';
                    var fallbackChild = document.getElementById('manageSub_add_parent_child_id') ? document.getElementById('manageSub_add_parent_child_id').value : '';
                    showManageSubInlineAdd(fallbackCard, fallbackChild);
                  }
                } catch(e){
                  console.error('manageSub add button error', e);
                }
              };
            }

            function showManageSubInlineAdd(cardIdParam, parentChildIdParam, prefill){
                var form = document.getElementById('manageSub_inlineAddForm');
                form.action = '/admin/gallery-cards/' + (cardIdParam || cardId) + '/children';
                document.getElementById('manageSub_add_parent_card_id').value = cardIdParam || cardId || '';
                document.getElementById('manageSub_add_parent_child_id').value = parentChildIdParam || '';
                document.getElementById('manageSub_add_title').value = (prefill && prefill.title) ? prefill.title : '';
                document.getElementById('manageSub_add_url').value = (prefill && prefill.url) ? prefill.url : '';
                document.getElementById('manageSub_add_is_active').value = (prefill && typeof prefill.is_active !== 'undefined') ? (prefill.is_active ? '1' : '0') : '1';

                var headerBtn = document.getElementById('manageSub_addSubchildBtn');
                if (headerBtn) headerBtn.style.display = 'none';
                document.querySelectorAll('#manageSub_tbody .btn-add-sub-for, .btn-add-sub-for').forEach(function(b){ b.style.display = 'none'; });

                document.getElementById('manageSub_inlineAddWrap').style.display = 'block';
                document.getElementById('manageSub_add_title').focus();
            }

            var inlineCancel = document.getElementById('manageSub_inlineCancelBtn');
            if (inlineCancel) inlineCancel.onclick = function(){
                document.getElementById('manageSub_inlineAddWrap').style.display = 'none';
                document.getElementById('manageSub_add_title').value = '';
                document.getElementById('manageSub_add_url').value = '';
                document.getElementById('manageSub_add_parent_card_id').value = '';
                document.getElementById('manageSub_add_parent_child_id').value = '';

                var headerBtn = document.getElementById('manageSub_addSubchildBtn');
                if (headerBtn) headerBtn.style.display = '';
                document.querySelectorAll('#manageSub_tbody .btn-add-sub-for, .btn-add-sub-for').forEach(function(b){ b.style.display = ''; });
            };

            var manageModalEl = document.getElementById('manageSubChildrenModal');
            if (manageModalEl) manageModalEl.addEventListener('hidden.bs.modal', function(){
                document.getElementById('manageSub_inlineAddWrap').style.display = 'none';
                document.getElementById('manageSub_add_title').value = '';
                document.getElementById('manageSub_add_url').value = '';
                document.getElementById('manageSub_add_parent_card_id').value = '';
                document.getElementById('manageSub_add_parent_child_id').value = '';
                var headerBtn = document.getElementById('manageSub_addSubchildBtn');
                if (headerBtn) headerBtn.style.display = '';
                document.querySelectorAll('#manageSub_tbody .btn-add-sub-for, .btn-add-sub-for').forEach(function(b){ b.style.display = ''; });
            });

            var bs = new bootstrap.Modal(modal);
            bs.show();
        });

        window.showManageSubInlineAdd = function(a,b,c){ showManageSubInlineAdd(a,b,c); };
    

    @if(old('parent_card_id') && !old('parent_child_id'))
        openAddChildModal({
            cardId: {{ (int) old('parent_card_id') }},
            title: {!! json_encode(old('title')) !!},
            url: {!! json_encode(old('url')) !!},
            is_active: {!! json_encode(old('is_active', 1)) !!}
        });
    @endif

    @if(old('parent_card_id') && old('parent_child_id'))
        @if(old('from_manage_modal'))
            (function(){
                var cardIdOld = {{ (int) old('parent_card_id') }};
                var parentChildIdOld = {{ (int) old('parent_child_id') }};
                document.querySelectorAll('.btn-manage-subchildren').forEach(function(b){
                    if (parseInt(b.getAttribute('data-child-id')) === parentChildIdOld && parseInt(b.getAttribute('data-card-id')) === cardIdOld) {
                        b.click();
                        setTimeout(function(){
                            if (window.showManageSubInlineAdd) {
                                window.showManageSubInlineAdd(cardIdOld, parentChildIdOld, {
                                    title: {!! json_encode(old('title')) !!},
                                    url: {!! json_encode(old('url')) !!},
                                    is_active: {!! json_encode(old('is_active', 1)) !!}
                                });
                            }
                        }, 250);
                    }
                });
            })();
        @else
            openAddSubChildModal({
                cardId: {{ (int) old('parent_card_id') }},
                parentChildId: {{ (int) old('parent_child_id') }},
                title: {!! json_encode(old('title')) !!},
                url: {!! json_encode(old('url')) !!},
                is_active: {!! json_encode(old('is_active', 1)) !!}
            });
        @endif
    @endif

    @if(old('editing_child_id'))
        openEditChildModal({
            id: {{ (int) old('editing_child_id') }},
            title: {!! json_encode(old('title')) !!},
            url: {!! json_encode(old('url')) !!},
            is_active: {!! json_encode(old('is_active', 1)) !!},
            is_mother: {!! old('is_mother') ? '1' : '0' !!},
            docno: {!! json_encode(old('docno') ?? '-') !!},
            status: {!! json_encode(old('status', 'On going')) !!},
            histories: []
        });
    @endif

    @if(old('editing_gallery_id'))
        openEditCardModal({
            id: {{ (int) old('editing_gallery_id') }},
            title: {!! json_encode(old('title')) !!},
            url: {!! json_encode(old('url')) !!},
            is_active: {!! json_encode(old('is_active', 1)) !!},
            status: {!! json_encode(old('status', 'On going')) !!}
        });
    @endif

</script>
@endsection