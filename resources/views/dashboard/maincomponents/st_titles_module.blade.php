@extends('layouts.app')

@section('content')
<style>
    /* File-scoped: allow this module to use the full horizontal space */
    .st-shell { max-width: none; width:100%; margin:0; padding:16px 20px; box-sizing:border-box; }
    .st-hero { background: linear-gradient(135deg,#0b2540,#175d8f); color:#fff; padding:20px; border-radius:16px; margin-bottom:18px; }
    .st-card { background:#fff; border:1px solid #e6eef7; border-radius:12px; padding:16px; }
    .st-table { width:100%; border-collapse:collapse; margin-top:12px; }
    .st-table th, .st-table td { padding:10px; border-bottom:1px solid #eef4fb; text-align:left; }
    /* masterdata expandable row styles (copied from masterdata view) */
    .masterdata-item-list {
        border: 1px solid #dbe4f0;
        border-radius: 20px;
        overflow: hidden;
        background: #fff;
        width: calc(100% + 32px);
        margin: 0 -16px;
    }
    .masterdata-item-list-head,
    .masterdata-item-row {
        display: grid;
        grid-template-columns: minmax(750px, 1fr) repeat(4, minmax(120px, 1fr)) 56px;
        gap: 12px;
        align-items: center;
        padding: 14px 18px;
    }
    .masterdata-item-list-head {
        background: #f7fafc;
        border-bottom: 1px solid #dbe4f0;
        font-size: 0.82rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #607588;
    }
    .masterdata-item-entry {
        border-bottom: 1px solid #edf2f7;
    }
    .masterdata-item-entry:last-child {
        border-bottom: none;
    }
    .masterdata-item-row {
        background: #fff;
        cursor: pointer;
        transition: background 0.18s ease;
    }
    .masterdata-item-row:hover {
        background: #f8fbfe;
    }
    .masterdata-item-row.is-open {
        background: #f3f8fc;
    }
    .masterdata-item-row-title {
        font-size: 0.97rem;
        font-weight: 700;
        color: #0b2540;
    }
    .masterdata-item-row-cell {
        font-size: 0.9rem;
        color: #244865;
    }
    .masterdata-item-row-cell-muted {
        color: #607588;
    }
    .masterdata-row-chevron {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 999px;
        background: #e8f1f8;
        color: #175d8f;
        font-size: 1rem;
        font-weight: 800;
        transition: transform 0.18s ease, background 0.18s ease;
    }
    .masterdata-item-row.is-open .masterdata-row-chevron {
        transform: rotate(180deg);
        background: #dbeaf5;
    }
    .masterdata-item-detail {
        max-height: 0;
        overflow: hidden;
        padding: 0 18px;
        background: linear-gradient(180deg, #fbfdff 0%, #f4f8fb 100%);
        border-top: 1px solid transparent;
        opacity: 0;
        transform: translateY(-8px);
        pointer-events: none;
        transition: max-height 0.32s ease, padding 0.24s ease, opacity 0.24s ease, transform 0.24s ease, border-color 0.24s ease;
    }
    .masterdata-item-detail.is-open {
        max-height: 1400px;
        padding: 18px;
        border-top-color: #e4edf6;
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto;
    }
    .masterdata-form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 16px;
    }
    .masterdata-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .masterdata-field.full {
        grid-column: 1 / -1;
    }
    .masterdata-field.is-hidden {
        display: none;
    }
    .masterdata-field label {
        font-size: 0.9rem;
        font-weight: 700;
        color: #244865;
    }
    .masterdata-field input,
    .masterdata-field select,
    .masterdata-field textarea {
        width: 100%;
        border: 1px solid #bfd1e4;
        border-radius: 12px;
        padding: 11px 13px;
        font-size: 0.95rem;
        background: #f9fbfd;
        color: #244865;
        resize: vertical;
        min-height: 44px;
    }
    .masterdata-field input:focus,
    .masterdata-field select:focus,
    .masterdata-field textarea:focus {
        outline: none;
        border-color: #175d8f;
        box-shadow: 0 0 0 3px rgba(23, 93, 143, 0.12);
    }
    .masterdata-field-error {
        color: #b91c1c;
        font-size: 0.85rem;
        margin-top: 6px;
    }
    .masterdata-field-error:not(:empty) { display:block; }
    .masterdata-btn {
        border: none;
        border-radius: 12px;
        padding: 11px 16px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .masterdata-btn-primary { background: linear-gradient(135deg, #0b2540, #175d8f); color: #fff; }
    .masterdata-btn-secondary { background: #eff6fb; color: #194566; border: 1px solid #d8e5f1; }
    .masterdata-btn-danger { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
</style>

<div class="st-shell">
    <div class="st-hero">
        <h1>All Social Technology Titles</h1>
        <p style="margin:6px 0 0; opacity:0.9">Listing of all social technology titles from the database.</p>
    </div>

    <section class="st-card">
        <h3 style="margin-top:0">Titles</h3>
        <div>
            <div class="masterdata-item-list">
                <div class="masterdata-item-list-head">
                    <div>ST Title</div>
                    <div>Sector</div>
                    <div>Created By</div>
                    <div>Updated At</div>
                    <div></div>
                </div>

                @forelse($titles as $t)
                    <div class="masterdata-item-entry">
                        <div class="masterdata-item-row" data-masterdata-item-toggle="title-{{ $t->id }}" role="button" tabindex="0" aria-expanded="false">
                            <div>
                                <div class="masterdata-item-row-title">{{ $t->social_technology }}</div>
                            </div>
                            <div class="masterdata-item-row-cell {{ $t->sector ? '' : 'masterdata-item-row-cell-muted' }}">{{ $t->sector ?: 'No sector' }}</div>
                            <div>{{ $t->createdby ?: '-' }}</div>
                            <div>{{ $t->updated_at?->format('M d, Y h:i A') ?: '-' }}</div>
                            <div><span class="masterdata-row-chevron">▾</span></div>
                        </div>

                        <div class="masterdata-item-detail" id="title-{{ $t->id }}">
                            <div class="masterdata-item-head">
                                <div>
                                    <div class="masterdata-item-title">{{ $t->social_technology }}</div>
                                    <div class="masterdata-item-meta">
                                        <span>Created by: {{ $t->createdby ?: '-' }}</span>
                                        <span>Updated at: {{ $t->updated_at?->format('M d, Y h:i A') ?: '-' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="masterdata-form-grid" style="margin-top:12px;">
                                <div class="masterdata-field full">
                                    <label>Sector</label>
                                    <input type="text" value="{{ $t->sector ?: '-' }}" readonly>
                                </div>

                                <div class="masterdata-field full">
                                    <label>Laws and Issuances</label>
                                    <textarea rows="3" readonly>{{ $t->laws_and_issuances ?: '-' }}</textarea>
                                </div>

                                <div class="masterdata-field full">
                                    <label>Description</label>
                                    <textarea rows="3" readonly>{{ $t->description ?: '-' }}</textarea>
                                </div>

                                <div class="masterdata-field full">
                                    <label>Objectives</label>
                                    <textarea rows="2" readonly>{{ $t->objectives ?: '-' }}</textarea>
                                </div>

                                <div class="masterdata-field full">
                                    <label>Components</label>
                                    <textarea rows="2" readonly>{{ $t->components ?: '-' }}</textarea>
                                </div>

                                <div class="masterdata-field">
                                    <label>Pilot Areas</label>
                                    <input type="text" value="{{ $t->pilot_areas ?: '-' }}" readonly>
                                </div>

                                <div class="masterdata-field">
                                    <label>Year Implemented</label>
                                    <input type="text" value="{{ $t->year_implemented ?: '-' }}" readonly>
                                </div>

                                <div class="masterdata-field">
                                    <label>Status / Remarks</label>
                                    <input type="text" value="{{ $t->status_remarks ?: '-' }}" readonly>
                                </div>

                                <div class="masterdata-field">
                                    <label>Resolution</label>
                                    <input type="text" value="{{ $t->resolution ?: '-' }}" readonly>
                                </div>

                                <div class="masterdata-field full">
                                    <label>Guidelines (AO / MC Number)</label>
                                    <input type="text" value="{{ $t->guidelines ?: '-' }}" readonly>
                                </div>

                                <div class="masterdata-field full">
                                    <label>Program Manual Outline</label>
                                    <textarea rows="3" readonly>{{ $t->program_manual_outline ?: '-' }}</textarea>
                                </div>

                                <div class="masterdata-field full">
                                    <label>Information Systems Developed (Key Tools)</label>
                                    <textarea rows="2" readonly>{{ $t->information_systems_developed ?: '-' }}</textarea>
                                </div>

                                <div class="masterdata-field full">
                                    <label>Session Guide Key Topics</label>
                                    <textarea rows="2" readonly>{{ $t->session_guide_key_topics ?: '-' }}</textarea>
                                </div>

                                <div class="masterdata-field full">
                                    <label>Training Manual Outline</label>
                                    <textarea rows="2" readonly>{{ $t->training_manual_outline ?: '-' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="masterdata-empty">No titles found.</div>
                @endforelse

                @if(method_exists($titles, 'hasPages') && $titles->hasPages())
                    <div class="masterdata-pagination" style="margin-top:12px;">
                        {{ $titles->onEachSide(1)->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>

@endsection

@section('scripts')
<script>
    (function () {
        const toggles = document.querySelectorAll('[data-masterdata-item-toggle]');
        if (!toggles || toggles.length === 0) return;

        function closeAllDetails(exceptId) {
            toggles.forEach(function (toggle) {
                const detailId = toggle.getAttribute('data-masterdata-item-toggle');
                const detail = document.getElementById(detailId);
                const isTarget = detailId === exceptId;
                if (!isTarget && detail) {
                    detail.classList.remove('is-open');
                    toggle.classList.remove('is-open');
                    toggle.setAttribute('aria-expanded', 'false');
                }
            });
        }

        toggles.forEach(function (toggle) {
            const detailId = toggle.getAttribute('data-masterdata-item-toggle');
            const detail = document.getElementById(detailId);
            if (!detail) return;

            function handleToggle() {
                const willOpen = !detail.classList.contains('is-open');
                closeAllDetails(detailId);
                detail.classList.toggle('is-open', willOpen);
                toggle.classList.toggle('is-open', willOpen);
                toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            }

            toggle.addEventListener('click', function (event) {
                if (event.target.closest('button, a, input, select, label, textarea, form')) return;
                handleToggle();
            });

            toggle.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    handleToggle();
                }
            });
        });
    })();
</script>
@endsection
