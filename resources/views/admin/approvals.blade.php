@extends('layouts.app')

@section('content')
    @php
        $pendingCount = $pendingUsers->count();
        $todayCount = $pendingUsers->filter(fn ($user) => optional($user->created_at)->isToday())->count();
    @endphp

    <style>
        .approvals-admin-page {
            color: #16324f;
        }

        .approvals-hero {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            padding: 1.75rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(12, 62, 122, 0.12);
            border-radius: 24px;
            background: rgba(5, 44, 91, 0.96);
            box-shadow: 0 20px 44px rgba(8, 43, 81, 0.16);
        }

        .approvals-hero::before,
        .approvals-hero::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            pointer-events: none;
        }

        .approvals-hero::before {
            width: 220px;
            height: 220px;
            top: -85px;
            right: -70px;
        }

        .approvals-hero::after {
            width: 150px;
            height: 150px;
            bottom: -55px;
            left: 38%;
        }

        .approvals-hero-copy,
        .approvals-hero-meta-panel {
            position: relative;
            z-index: 1;
        }

        .approvals-hero-copy {
            max-width: 760px;
            color: #fff;
        }

        .approvals-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
            padding: 0.45rem 0.85rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .approvals-hero h1 {
            margin: 0;
            font-size: clamp(1.8rem, 2.4vw, 2.6rem);
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .approvals-hero p {
            margin: 0.8rem 0 0;
            max-width: 58rem;
            color: rgba(255, 255, 255, 0.84);
            line-height: 1.65;
        }

        .approvals-hero-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
            margin-top: 1rem;
        }

        .approvals-hero-tags span {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.55rem 0.85rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
            font-size: 0.92rem;
            font-weight: 600;
        }

        .approvals-hero-meta-panel {
            min-width: 280px;
            padding: 1.1rem 1.15rem;
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            backdrop-filter: blur(6px);
        }

        .approvals-hero-meta-panel strong {
            display: block;
            margin-bottom: 0.2rem;
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.04em;
        }

        .approvals-hero-meta-panel span {
            display: block;
            color: rgba(255, 255, 255, 0.76);
            font-size: 0.92rem;
        }

        .approvals-stat-card {
            height: 100%;
            padding: 1.15rem 1.2rem;
            border: 1px solid rgba(14, 75, 131, 0.1);
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.88);
            box-shadow: 0 16px 36px rgba(11, 49, 86, 0.08);
            backdrop-filter: blur(6px);
        }

        .approvals-stat-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.8rem;
            margin-bottom: 0.8rem;
        }

        .approvals-stat-label {
            color: #587089;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .approvals-stat-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 14px;
            font-size: 1.1rem;
            color: #fff;
        }

        .approvals-stat-card h2 {
            margin: 0;
            color: #16324f;
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.04em;
        }

        .approvals-stat-card p {
            margin: 0.35rem 0 0;
            color: #698298;
            font-size: 0.94rem;
        }

        .approvals-stat-total .approvals-stat-icon {
            background: linear-gradient(135deg, #2163d6, #15479f);
        }

        .approvals-stat-today .approvals-stat-icon {
            background: linear-gradient(135deg, #1fa96f, #127c51);
        }

        .approvals-panel {
            border: 1px solid rgba(14, 75, 131, 0.1);
            border-radius: 26px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 20px 48px rgba(11, 49, 86, 0.1);
            backdrop-filter: blur(8px);
            overflow: hidden;
        }

        .approvals-panel-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.5rem 1.5rem 1rem;
            border-bottom: 1px solid rgba(14, 75, 131, 0.08);
        }

        .approvals-panel-title h5 {
            margin: 0;
            color: #16324f;
            font-size: 1.25rem;
            font-weight: 800;
        }

        .approvals-panel-title p {
            margin: 0.3rem 0 0;
            color: #6d8296;
        }

        .approvals-toolbar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .approvals-search-box {
            position: relative;
            min-width: min(320px, 100%);
        }

        .approvals-search-box i {
            position: absolute;
            top: 50%;
            left: 0.95rem;
            transform: translateY(-50%);
            color: #698298;
        }

        .approvals-search-box input,
        .approvals-filter-select,
        .approvals-modal .form-select,
        .approvals-modal .form-control,
        .approval-readonly {
            border: 1px solid rgba(14, 75, 131, 0.14);
            border-radius: 14px;
            background: #f8fbff;
            color: #16324f;
            transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }

        .approvals-search-box input {
            width: 100%;
            padding: 0.78rem 0.95rem 0.78rem 2.7rem;
            font-weight: 500;
        }

        .approvals-filter-select {
            min-width: 170px;
            padding: 0.78rem 2.2rem 0.78rem 0.85rem;
            font-weight: 600;
        }

        .approvals-search-box input:focus,
        .approvals-filter-select:focus,
        .approvals-modal .form-select:focus,
        .approvals-modal .form-control:focus {
            border-color: rgba(33, 99, 214, 0.45);
            box-shadow: 0 0 0 0.22rem rgba(33, 99, 214, 0.12);
            background: #fff;
        }

        .approvals-results-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
            padding: 0 1.5rem 1rem;
            color: #698298;
            font-size: 0.92rem;
        }

        .approvals-results-count {
            font-weight: 700;
            color: #16324f;
        }

        .approvals-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
        }

        .approvals-legend span {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.45rem 0.75rem;
            border-radius: 999px;
            background: #f4f7fb;
            color: #4f6780;
            font-weight: 600;
        }

        .approvals-legend span::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: currentColor;
        }

        .approvals-table-wrap {
            padding: 0 1.1rem 1.2rem;
        }

        .approvals-table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0 0.75rem;
        }

        .approvals-table thead th {
            border: 0;
            padding: 0 0.85rem 0.25rem;
            color: #5b748d;
            font-size: 0.84rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .approvals-table tbody tr {
            box-shadow: 0 10px 28px rgba(11, 49, 86, 0.06);
            transition: transform 0.16s ease, box-shadow 0.16s ease;
        }

        .approvals-table tbody tr:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 30px rgba(11, 49, 86, 0.1);
        }

        .approvals-table tbody td {
            padding: 1rem 0.85rem;
            vertical-align: middle;
            border: 0;
            background: #fff;
        }

        .approvals-table tbody td:first-child {
            border-top-left-radius: 18px;
            border-bottom-left-radius: 18px;
        }

        .approvals-table tbody td:last-child {
            border-top-right-radius: 18px;
            border-bottom-right-radius: 18px;
        }

        .approvals-user-cell {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .approvals-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 46px;
            height: 46px;
            border-radius: 15px;
            background: linear-gradient(135deg, rgba(33, 99, 214, 0.14), rgba(11, 138, 109, 0.16));
            color: #0f4d8c;
            font-size: 0.95rem;
            font-weight: 800;
            letter-spacing: 0.04em;
        }

        .approvals-user-name,
        .approvals-date-label {
            color: #16324f;
            font-weight: 700;
        }

        .approvals-user-subtext,
        .approvals-email-subtext,
        .approvals-date-subtext {
            color: #73879a;
            font-size: 0.88rem;
        }

        .approvals-email {
            color: #284866;
            font-weight: 600;
        }

        .approvals-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.38rem 0.7rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            white-space: nowrap;
        }

        .approvals-pill::before {
            content: '';
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: currentColor;
        }

        .approvals-pill-user {
            background: rgba(91, 116, 141, 0.12);
            color: #5b748d;
        }

        .approvals-pill-admin {
            background: rgba(209, 79, 109, 0.12);
            color: #be385a;
        }

        .approvals-pill-sysadmin {
            background: rgba(123, 82, 216, 0.12);
            color: #6d46cc;
        }

        .approvals-view-btn,
        .approvals-approve-btn,
        .approvals-reject-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 12px;
            font-weight: 700;
        }

        .approvals-view-btn {
            padding: 0.65rem 0.95rem;
            border: 1px solid rgba(33, 99, 214, 0.16);
            background: linear-gradient(135deg, #2970eb, #1956b3);
            color: #fff;
            box-shadow: 0 10px 20px rgba(33, 99, 214, 0.18);
        }

        .approvals-view-btn:hover,
        .approvals-view-btn:focus {
            color: #fff;
            background: linear-gradient(135deg, #235fc7, #15479f);
        }

        .approvals-approve-btn {
            padding: 0.9rem 1rem;
            border: none;
            background: linear-gradient(135deg, #19a464, #0f8a51);
            color: #fff;
            box-shadow: 0 14px 28px rgba(16, 138, 81, 0.2);
        }

        .approvals-reject-btn {
            padding: 0.9rem 1rem;
            border: none;
            background: linear-gradient(135deg, #d9546b, #ba304a);
            color: #fff;
            box-shadow: 0 14px 28px rgba(186, 48, 74, 0.2);
        }

        .approvals-empty-state,
        .approvals-access-state {
            padding: 2.4rem 1rem;
            text-align: center;
            color: #698298;
        }

        .approvals-empty-state i,
        .approvals-access-state i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            margin-bottom: 0.8rem;
            border-radius: 20px;
            background: rgba(33, 99, 214, 0.08);
            color: #2163d6;
            font-size: 1.4rem;
        }

        .approvals-success-alert,
        .approvals-error-alert {
            border: 0;
            border-radius: 20px;
            box-shadow: 0 14px 28px rgba(11, 49, 86, 0.08);
        }

        .approvals-success-alert {
            background: linear-gradient(135deg, rgba(26, 167, 104, 0.14), rgba(20, 124, 81, 0.08));
            color: #106844;
        }

        .approvals-error-alert {
            background: linear-gradient(135deg, rgba(217, 84, 107, 0.14), rgba(186, 48, 74, 0.08));
            color: #8f2038;
        }

        /* Floating toast for JS feedback (success / error) */
        .approval-toast {
            position: fixed;
            right: 1.5rem;
            top: 1.5rem;
            min-width: 320px;
            max-width: calc(100% - 3rem);
            border-radius: 14px;
            box-shadow: 0 18px 40px rgba(11, 49, 86, 0.15);
            z-index: 1080;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.9rem 1rem;
            color: #fff;
            transition: transform 0.22s cubic-bezier(.2,.9,.2,1), opacity 0.18s ease;
            transform: translateY(-8px);
            opacity: 0;
            pointer-events: auto;
        }

        .approval-toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .approval-toast .toast-icon {
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .approval-toast.success { background: linear-gradient(135deg,#19a464,#0f8a51); }
        .approval-toast.error { background: linear-gradient(135deg,#d9546b,#ba304a); }

        .approval-toast .toast-body { flex: 1 1 auto; }
        .approval-toast .toast-close { background: transparent; border: 0; color: rgba(255,255,255,0.95); font-size: 1.05rem; }

        .approvals-modal .modal-content {
            border: 0;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 28px 60px rgba(11, 49, 86, 0.2);
        }

        .approvals-modal .modal-header {
            padding: 1.15rem 1.3rem;
            border-bottom: 1px solid rgba(14, 75, 131, 0.08);
            background: linear-gradient(135deg, #f8fbff, #eef5ff);
        }

        .approvals-modal .modal-title {
            color: #16324f;
            font-weight: 800;
        }

        .approvals-modal .modal-body {
            padding: 1.35rem;
        }

        .approvals-modal .modal-footer {
            padding: 1rem 1.35rem 1.35rem;
            border-top: 1px solid rgba(14, 75, 131, 0.08);
        }

        .approvals-modal-section {
            margin-bottom: 1rem;
            padding: 1rem;
            border: 1px solid rgba(14, 75, 131, 0.08);
            border-radius: 18px;
            background: #fbfdff;
        }

        .approvals-modal-section-title {
            margin-bottom: 0.9rem;
            color: #16324f;
            font-size: 0.92rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .approval-readonly {
            min-height: 48px;
            padding: 0.78rem 0.95rem;
            margin: 0;
            font-weight: 500;
        }

        .approval-form-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.85rem;
            margin-top: 1rem;
        }

        @media (max-width: 991.98px) {
            .approvals-hero,
            .approvals-panel-header,
            .approvals-results-meta {
                flex-direction: column;
                align-items: stretch;
            }

            .approvals-hero-meta-panel,
            .approvals-search-box,
            .approvals-filter-select {
                width: 100%;
            }

            .approvals-toolbar {
                justify-content: stretch;
            }
        }

        @media (max-width: 767.98px) {
            .approvals-panel,
            .approvals-hero {
                border-radius: 20px;
            }

            .approvals-table {
                min-width: 980px;
            }

            .approval-form-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="approvals-admin-page">
        @if(Auth::user()->usergroup !== 'sysadmin' && Auth::user()->usergroup !== 'admin')
            <div class="approvals-panel">
                <div class="approvals-access-state">
                    <i class="bi bi-shield-lock"></i>
                    <div class="fw-bold fs-5 mb-2">You do not have permission to access this page.</div>
                    <div class="mb-3">Only administrators can review and decide pending account registrations.</div>
                    <a href="{{ route('main') }}" class="btn approvals-view-btn">Go Back</a>
                </div>
            </div>
        @else
            <div class="approvals-hero">
                <div class="approvals-hero-copy">
                    <span class="approvals-eyebrow">
                        <i class="bi bi-person-check"></i>
                        Registration Approvals
                    </span>
                    <h1>Review Pending Registrations</h1>
                    <div class="approvals-hero-tags">
                        <span><i class="bi bi-hourglass-split"></i> {{ $pendingCount }} waiting for review</span>
                        <span><i class="bi bi-person-workspace"></i> Roles assigned by admin during approval</span>
                        <span><i class="bi bi-calendar-event"></i> {{ $todayCount }} submitted today</span>
                    </div>
                </div>
                <div class="approvals-hero-meta-panel">
                    <strong>{{ $pendingCount }}</strong>
                    <span>Registrations currently in the approval queue.</span>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show approvals-success-alert" role="alert">
                    <div class="d-flex align-items-start gap-3">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                        <div class="flex-grow-1">
                            <strong class="d-block mb-1">Approval queue updated.</strong>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show approvals-error-alert" role="alert">
                    <div class="d-flex align-items-start gap-3">
                        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                        <div class="flex-grow-1">
                            <strong class="d-block mb-1">Approval action failed.</strong>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="approvals-stat-card approvals-stat-total">
                        <div class="approvals-stat-top">
                            <span class="approvals-stat-label">Pending Queue</span>
                            <span class="approvals-stat-icon"><i class="bi bi-inboxes-fill"></i></span>
                        </div>
                        <h2>{{ $pendingCount }}</h2>
                        <p>Registrations waiting for administrator review.</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="approvals-stat-card approvals-stat-today">
                        <div class="approvals-stat-top">
                            <span class="approvals-stat-label">Today</span>
                            <span class="approvals-stat-icon"><i class="bi bi-calendar2-check-fill"></i></span>
                        </div>
                        <h2>{{ $todayCount }}</h2>
                        <p>Registrations submitted since the start of today.</p>
                    </div>
                </div>
            </div>

            <div class="approvals-panel">
                <div class="approvals-panel-header">
                    <div class="approvals-panel-title">
                        <h5>Pending Registrations</h5>
                        <p>Use the search and role filter to focus the queue before opening full registration details.</p>
                    </div>
                    <div class="approvals-toolbar">
                        <div class="approvals-search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" id="approvalTableSearch" placeholder="Search by name, email, or phone">
                        </div>
                    </div>
                </div>

                <div class="approvals-results-meta">
                    <div>
                        <span class="approvals-results-count" id="approvalsVisibleCount">{{ $pendingCount }}</span>
                        <span>requests shown</span>
                    </div>
                    <div class="approvals-legend">
                        <span style="color:#2163d6;">Admin assigns role during approval</span>
                    </div>
                </div>

                @if($pendingUsers->isEmpty())
                    <div class="approvals-empty-state">
                        <i class="bi bi-check-circle"></i>
                        <div class="fw-bold fs-5 mb-2">No pending registrations</div>
                        <div>No approvals are waiting in the queue right now.</div>
                    </div>
                @else
                    <div class="table-responsive approvals-table-wrap">
                        <table class="table approvals-table align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Applicant</th>
                                    <th>Email</th>
                                    <th>Registered On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingUsers as $index => $user)
                                    @php
                                        $displayName = $user->name ?: 'Unnamed Applicant';
                                        $initials = collect(explode(' ', trim($displayName)))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
                                        $initials = $initials !== '' ? $initials : 'U';
                                    @endphp
                                    <tr
                                        class="approval-table-row"
                                        data-search="{{ strtolower($displayName . ' ' . $user->email . ' ' . ($user->phonenumber ?? '')) }}"
                                    >
                                        <td class="text-muted fw-semibold">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="approvals-user-cell">
                                                <span class="approvals-avatar">{{ $initials }}</span>
                                                <div>
                                                    <div class="approvals-user-name">{{ $displayName }}</div>
                                                    <div class="approvals-user-subtext">{{ $user->phonenumber ?: 'No phone number provided' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="approvals-email">{{ $user->email }}</div>
                                            <div class="approvals-email-subtext">Primary registration email</div>
                                        </td>
                                        <td>
                                            <div class="approvals-date-label">{{ $user->created_at->format('M d, Y') }}</div>
                                            <div class="approvals-date-subtext">{{ $user->created_at->format('h:i A') }}</div>
                                        </td>
                                        <td>
                                            <button
                                                type="button"
                                                class="btn approvals-view-btn"
                                                onclick="showUserDetails(this)"
                                                data-db-id="{{ $user->id }}"
                                                data-user-id="{{ $user->user_id }}"
                                                data-name="{{ $displayName }}"
                                                data-email="{{ $user->email }}"
                                                data-phone="{{ $user->phonenumber }}"
                                                data-gender="{{ $user->gender }}"
                                                data-address="{{ $user->address }}"
                                                data-created="{{ $user->created_at->format('M d, Y h:i A') }}"
                                            >
                                                <i class="bi bi-eye"></i>
                                                Review
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                <tr id="approvalsNoResultsRow" class="d-none">
                                    <td colspan="5">
                                        <div class="approvals-empty-state">
                                            <i class="bi bi-funnel"></i>
                                            <div class="fw-bold mb-1">No registrations match the current filters</div>
                                            <div>Try another search term.</div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <div class="modal fade approvals-modal" id="userDetailsModal" tabindex="-1" aria-labelledby="userDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="userDetailsModalLabel">Registration Details</h5>
                        <div class="text-muted small mt-1">Review the applicant details before approving access or declining the request.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="approvals-modal-section">
                        <div class="approvals-modal-section-title">Applicant Information</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">User ID</label>
                                <p id="detail-user-id" class="approval-readonly"></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Full Name</label>
                                <p id="detail-name" class="approval-readonly"></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email Address</label>
                                <p id="detail-email" class="approval-readonly"></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Phone Number</label>
                                <p id="detail-phone" class="approval-readonly"></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Gender</label>
                                <p id="detail-gender" class="approval-readonly"></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Registered On</label>
                                <p id="detail-created" class="approval-readonly"></p>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Address</label>
                                <p id="detail-address" class="approval-readonly"></p>
                            </div>
                        </div>
                    </div>

                    <div class="approvals-modal-section mb-0">
                        <div class="approvals-modal-section-title">Approval Decision</div>
                        <form id="approvalForm" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" id="userId" name="user_id">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="detail-usergroup-select" class="form-label fw-bold">Approved User Group</label>
                                    <select id="detail-usergroup-select" class="form-select" required>
                                        <option value="">Select User Group</option>
                                        <option value="user">User</option>
                                        <option value="admin">Admin</option>
                                        @if(Auth::user()->usergroup === 'sysadmin')
                                            <option value="sysadmin">Sysadmin</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="w-100 text-muted small">
                                        Assign the final role this account should receive after approval.
                                    </div>
                                </div>
                            </div>
                            <div class="approval-form-actions" id="approvalActionButtons">
                                <button type="button" class="btn approvals-approve-btn" onclick="submitApproval('A')">
                                    <i class="bi bi-check-circle"></i>
                                    Approve Registration
                                </button>
                                <button type="button" class="btn approvals-reject-btn" onclick="showRejectionModal()">
                                    <i class="bi bi-x-circle"></i>
                                    Reject Registration
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade approvals-modal" id="rejectionReasonModal" tabindex="-1" aria-labelledby="rejectionReasonModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="rejectionReasonModalLabel">Rejection Reason</h5>
                        <div class="text-muted small mt-1">Provide a clear reason that explains why the registration cannot be approved.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="rejectionForm">
                        <div class="mb-3">
                            <label for="rejectionReason" class="form-label fw-bold">Reason for rejection</label>
                            <textarea class="form-control" id="rejectionReason" name="rejectionReason" rows="4" required></textarea>
                        </div>
                        <button type="button" class="btn approvals-reject-btn w-100" onclick="submitApproval('R')">
                            <i class="bi bi-send"></i>
                            Submit Rejection
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container for JS notifications -->
    <div id="approvalToast" class="approval-toast d-none" role="status" aria-live="polite">
        <div class="toast-icon" id="approvalToastIcon"><i class="bi bi-check-circle-fill"></i></div>
        <div class="toast-body" id="approvalToastBody">Message</div>
        <button type="button" class="toast-close btn-close-white" id="approvalToastClose" aria-label="Close"></button>
    </div>

    <script>
        let currentUserId = null;
        let userDetailsModal = null;
        let rejectionReasonModal = null;
        let approvalActionButtonsHtml = '';
        let rejectionFormHtml = '';

        function showToast(type, message, autoHide = true, delay = 1100) {
            const toast = document.getElementById('approvalToast');
            const icon = document.getElementById('approvalToastIcon');
            const body = document.getElementById('approvalToastBody');
            const close = document.getElementById('approvalToastClose');

            if (!toast || !icon || !body) return;

            // set content
            body.textContent = message;

            // set type
            toast.classList.remove('success', 'error', 'show');
            if (type === 'success') {
                toast.classList.add('success');
                icon.innerHTML = '<i class="bi bi-check-lg"></i>';
            } else {
                toast.classList.add('error');
                icon.innerHTML = '<i class="bi bi-x-lg"></i>';
            }

            // show
            toast.classList.remove('d-none');
            // small reflow to allow transition
            void toast.offsetWidth;
            toast.classList.add('show');

            // close handler
            const hide = () => {
                toast.classList.remove('show');
                setTimeout(() => toast.classList.add('d-none'), 220);
            };

            close.onclick = hide;

            if (autoHide) {
                setTimeout(hide, delay);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const modalElement = document.getElementById('userDetailsModal');
            if (modalElement && typeof bootstrap !== 'undefined') {
                userDetailsModal = new bootstrap.Modal(modalElement);
            }

            const rejectionModalElement = document.getElementById('rejectionReasonModal');
            if (rejectionModalElement && typeof bootstrap !== 'undefined') {
                rejectionReasonModal = new bootstrap.Modal(rejectionModalElement);
            }

            const approvalActionButtons = document.getElementById('approvalActionButtons');
            if (approvalActionButtons) {
                approvalActionButtonsHtml = approvalActionButtons.innerHTML;
            }

            const rejectionForm = document.getElementById('rejectionForm');
            if (rejectionForm) {
                rejectionFormHtml = rejectionForm.innerHTML;
            }

            const searchInput = document.getElementById('approvalTableSearch');
            const tableRows = Array.from(document.querySelectorAll('.approval-table-row'));
            const visibleCount = document.getElementById('approvalsVisibleCount');
            const noResultsRow = document.getElementById('approvalsNoResultsRow');

            const applyApprovalFilters = () => {
                const searchTerm = (searchInput?.value || '').trim().toLowerCase();
                let matches = 0;

                tableRows.forEach(row => {
                    const matchesSearch = !searchTerm || row.dataset.search.includes(searchTerm);
                    const isVisible = matchesSearch;

                    row.classList.toggle('d-none', !isVisible);
                    if (isVisible) {
                        matches += 1;
                    }
                });

                if (visibleCount) {
                    visibleCount.textContent = matches;
                }

                if (noResultsRow) {
                    noResultsRow.classList.toggle('d-none', matches !== 0 || tableRows.length === 0);
                }
            };

            if (searchInput) {
                searchInput.addEventListener('input', applyApprovalFilters);
            }

            applyApprovalFilters();
        });

        function showUserDetails(button) {
            if (!button) {
                return;
            }

            document.getElementById('detail-user-id').textContent = button.dataset.userId || 'N/A';
            document.getElementById('detail-name').textContent = button.dataset.name || 'N/A';
            document.getElementById('detail-email').textContent = button.dataset.email || 'N/A';
            document.getElementById('detail-phone').textContent = button.dataset.phone || 'N/A';
            document.getElementById('detail-gender').textContent = button.dataset.gender || 'N/A';
            document.getElementById('detail-address').textContent = button.dataset.address || 'N/A';
            document.getElementById('detail-created').textContent = button.dataset.created || 'N/A';

            const usergroupSelect = document.getElementById('detail-usergroup-select');
            if (usergroupSelect) {
                usergroupSelect.value = '';
            }

            currentUserId = button.dataset.dbId;
            document.getElementById('userId').value = currentUserId || '';

            const approvalActionButtons = document.getElementById('approvalActionButtons');
            if (approvalActionButtons && approvalActionButtonsHtml) {
                approvalActionButtons.innerHTML = approvalActionButtonsHtml;
            }

            if (userDetailsModal) {
                userDetailsModal.show();
            }
        }

        function showRejectionModal() {
            if (!rejectionReasonModal) {
                return;
            }

            const rejectionForm = document.getElementById('rejectionForm');
            if (rejectionForm && rejectionFormHtml) {
                rejectionForm.innerHTML = rejectionFormHtml;
            }

            const rejectionReason = document.getElementById('rejectionReason');
            if (rejectionReason) {
                rejectionReason.value = '';
            }

            rejectionReasonModal.show();
        }

        function submitApproval(status) {
            if (!currentUserId) {
                showToast('error', 'User ID not found');
                return;
            }

            let approvalcomment = '';
            if (status === 'R') {
                approvalcomment = document.getElementById('rejectionReason').value.trim();
                if (!approvalcomment) {
                    showToast('error', 'Please provide a reason for rejection.');
                    return;
                }
            }

            let usergroup = null;
            if (status === 'A') {
                const usergroupSelect = document.getElementById('detail-usergroup-select');
                usergroup = usergroupSelect ? usergroupSelect.value : '';
                if (!usergroup) {
                    showToast('error', 'Please select a user group before approval.');
                    return;
                }
            }

            const approvedby = '{{ Auth::user()->name }}';

            if (status === 'A') {
                const approvalActionButtons = document.getElementById('approvalActionButtons');
                if (approvalActionButtons) {
                    approvalActionButtons.innerHTML = '<div class="text-center py-3"><div class="spinner-border" role="status"></div><p class="mt-3 mb-0">Processing approval...</p></div>';
                }
            } else if (status === 'R') {
                const rejectionForm = document.getElementById('rejectionForm');
                if (rejectionForm) {
                    rejectionForm.innerHTML = '<div class="text-center py-3"><div class="spinner-border" role="status"></div><p class="mt-3 mb-0">Submitting rejection...</p></div>';
                }
            }

            const payload = {
                approval_status: status,
                approvalcomment: approvalcomment,
                approvedby: approvedby
            };

            if (status === 'A') {
                payload.usergroup = usergroup;
            }

            fetch(`/users/${currentUserId}/approval`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (userDetailsModal) userDetailsModal.hide();
                    if (rejectionReasonModal) rejectionReasonModal.hide();
                    showToast('success', data.message || 'Approval updated', true, 900);
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast('error', 'Error: ' + (data.message || 'Failed to update approval status'), true, 1400);
                    setTimeout(() => window.location.reload(), 1500);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('error', 'An error occurred while processing the request', true, 1600);
                setTimeout(() => window.location.reload(), 1700);
            });
        }
    </script>
@endsection