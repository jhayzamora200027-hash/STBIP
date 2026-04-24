@extends('layouts.app')

@section('content')
    @php
        $totalUsers = $users->count();
        $activeUsers = $users->where('active', 1)->count();
        $inactiveUsers = $totalUsers - $activeUsers;
        $adminUsers = $users->filter(fn ($user) => in_array($user->usergroup, ['admin', 'sysadmin']))->count();
    @endphp

    <style>
        .users-admin-page {
            color: #16324f;
        }

        .users-hero {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            padding: 1.75rem 1.75rem 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(12, 62, 122, 0.12);
            border-radius: 24px;
            background: rgba(5, 44, 91, 0.96);
            box-shadow: 0 20px 44px rgba(8, 43, 81, 0.16);
        }

        .users-hero::before,
        .users-hero::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            pointer-events: none;
        }

        .users-hero::before {
            width: 220px;
            height: 220px;
            top: -90px;
            right: -70px;
        }

        .users-hero::after {
            width: 140px;
            height: 140px;
            bottom: -55px;
            left: 42%;
        }

        .users-hero-copy,
        .users-hero-actions {
            position: relative;
            z-index: 1;
        }

        .users-hero-copy {
            max-width: 760px;
            color: #fff;
        }

        .users-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.7rem;
            padding: 0.45rem 0.85rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            color: rgba(255, 255, 255, 0.88);
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .users-hero h1 {
            margin: 0;
            font-size: clamp(1.8rem, 2.4vw, 2.6rem);
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .users-hero p {
            margin: 0.8rem 0 0;
            max-width: 58rem;
            color: rgba(255, 255, 255, 0.82);
            font-size: 1rem;
            line-height: 1.65;
        }

        .users-hero-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
            margin-top: 1rem;
        }

        .users-hero-meta span {
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

        .users-primary-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.85rem 1.15rem;
            border: none;
            border-radius: 16px;
            background: linear-gradient(135deg, #19a464, #0f8a51);
            color: #fff;
            font-weight: 700;
            box-shadow: 0 14px 28px rgba(16, 138, 81, 0.22);
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .users-primary-btn:hover,
        .users-primary-btn:focus {
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 18px 34px rgba(16, 138, 81, 0.3);
        }

        .users-stat-card {
            height: 100%;
            padding: 1.15rem 1.2rem;
            border: 1px solid rgba(14, 75, 131, 0.1);
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.86);
            box-shadow: 0 16px 36px rgba(11, 49, 86, 0.08);
            backdrop-filter: blur(6px);
        }

        .users-stat-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.8rem;
            margin-bottom: 0.8rem;
        }

        .users-stat-label {
            color: #587089;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .users-stat-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 14px;
            font-size: 1.1rem;
            color: #fff;
        }

        .users-stat-card h2 {
            margin: 0;
            color: #16324f;
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.04em;
        }

        .users-stat-card p {
            margin: 0.35rem 0 0;
            color: #698298;
            font-size: 0.94rem;
        }

        .users-stat-total .users-stat-icon {
            background: linear-gradient(135deg, #2163d6, #15479f);
        }

        .users-stat-active .users-stat-icon {
            background: linear-gradient(135deg, #1fa96f, #127c51);
        }

        .users-stat-admin .users-stat-icon {
            background: linear-gradient(135deg, #d14f6d, #9f2f4b);
        }

        .users-stat-inactive .users-stat-icon {
            background: linear-gradient(135deg, #f0aa22, #c67f02);
        }

        .users-panel {
            border: 1px solid rgba(14, 75, 131, 0.1);
            border-radius: 26px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 20px 48px rgba(11, 49, 86, 0.1);
            backdrop-filter: blur(8px);
            overflow: hidden;
        }

        .users-panel-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.5rem 1.5rem 1rem;
            border-bottom: 1px solid rgba(14, 75, 131, 0.08);
        }

        .users-panel-title h5 {
            margin: 0;
            color: #16324f;
            font-size: 1.25rem;
            font-weight: 800;
        }

        .users-panel-title p {
            margin: 0.3rem 0 0;
            color: #6d8296;
        }

        .users-toolbar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .users-search-box {
            position: relative;
            min-width: min(320px, 100%);
        }

        .users-search-box i {
            position: absolute;
            top: 50%;
            left: 0.95rem;
            transform: translateY(-50%);
            color: #698298;
        }

        .users-search-box input,
        .users-filter-select,
        .users-modal .form-control,
        .users-modal .form-select {
            border: 1px solid rgba(14, 75, 131, 0.14);
            border-radius: 14px;
            background: #f8fbff;
            color: #16324f;
            transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }

        .users-search-box input {
            width: 100%;
            padding: 0.78rem 0.95rem 0.78rem 2.7rem;
            font-weight: 500;
        }

        .users-filter-select {
            min-width: 155px;
            padding: 0.78rem 2.2rem 0.78rem 0.85rem;
            font-weight: 600;
        }

        .users-search-box input:focus,
        .users-filter-select:focus,
        .users-modal .form-control:focus,
        .users-modal .form-select:focus {
            border-color: rgba(33, 99, 214, 0.45);
            box-shadow: 0 0 0 0.22rem rgba(33, 99, 214, 0.12);
            background: #fff;
        }

        .users-results-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
            padding: 0 1.5rem 1rem;
            color: #698298;
            font-size: 0.92rem;
        }

        .users-results-count {
            font-weight: 700;
            color: #16324f;
        }

        .users-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
        }

        .users-legend span {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.45rem 0.75rem;
            border-radius: 999px;
            background: #f4f7fb;
            color: #4f6780;
            font-weight: 600;
        }

        .users-legend span::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: currentColor;
        }

        .users-table-wrap {
            padding: 0 1.1rem 1.2rem;
        }

        .users-table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0 0.75rem;
        }

        .users-table thead th {
            border: 0;
            padding: 0 0.85rem 0.25rem;
            color: #5b748d;
            font-size: 0.84rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .users-table tbody tr {
            box-shadow: 0 10px 28px rgba(11, 49, 86, 0.06);
            transition: transform 0.16s ease, box-shadow 0.16s ease;
        }

        .users-table tbody tr:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 30px rgba(11, 49, 86, 0.1);
        }

        .users-table tbody td {
            padding: 1rem 0.85rem;
            vertical-align: middle;
            border: 0;
            background: #fff;
        }

        .users-table tbody td:first-child {
            border-top-left-radius: 18px;
            border-bottom-left-radius: 18px;
        }

        .users-table tbody td:last-child {
            border-top-right-radius: 18px;
            border-bottom-right-radius: 18px;
        }

        .users-user-cell {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .users-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 44px;
            height: 44px;
            border-radius: 15px;
            background: linear-gradient(135deg, rgba(33, 99, 214, 0.14), rgba(11, 138, 109, 0.16));
            color: #0f4d8c;
            font-size: 0.95rem;
            font-weight: 800;
            letter-spacing: 0.04em;
        }

        .users-user-name {
            color: #16324f;
            font-weight: 700;
        }

        .users-user-subtext,
        .users-email-subtext,
        .users-date-subtext {
            color: #73879a;
            font-size: 0.88rem;
        }

        .users-email {
            color: #284866;
            font-weight: 600;
        }

        .users-pill {
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

        .users-pill::before {
            content: '';
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: currentColor;
        }

        .users-pill-role-user {
            background: rgba(91, 116, 141, 0.12);
            color: #5b748d;
        }

        .users-pill-role-admin {
            background: rgba(209, 79, 109, 0.12);
            color: #be385a;
        }

        .users-pill-role-sysadmin {
            background: rgba(33, 99, 214, 0.12);
            color: #2163d6;
        }

        .users-pill-status-active {
            background: rgba(31, 169, 111, 0.12);
            color: #168a5d;
        }

        .users-pill-status-inactive {
            background: rgba(240, 170, 34, 0.16);
            color: #b77700;
        }

        .users-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.65rem 0.9rem;
            border: 1px solid rgba(33, 99, 214, 0.16);
            border-radius: 12px;
            background: linear-gradient(135deg, #2970eb, #1956b3);
            color: #fff;
            font-weight: 700;
            box-shadow: 0 10px 20px rgba(33, 99, 214, 0.18);
        }

        .users-action-btn:hover,
        .users-action-btn:focus {
            color: #fff;
            background: linear-gradient(135deg, #235fc7, #15479f);
        }

        .users-empty-state {
            padding: 2.4rem 1rem;
            text-align: center;
            color: #698298;
        }

        .users-empty-state i {
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

        .users-success-alert {
            border: 0;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(26, 167, 104, 0.14), rgba(20, 124, 81, 0.08));
            box-shadow: 0 14px 28px rgba(16, 138, 81, 0.12);
            color: #106844;
        }

        .users-modal .modal-content {
            border: 0;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 28px 60px rgba(11, 49, 86, 0.2);
        }

        .users-modal .modal-header {
            padding: 1.15rem 1.3rem;
            border-bottom: 1px solid rgba(14, 75, 131, 0.08);
            background: linear-gradient(135deg, #f8fbff, #eef5ff);
        }

        .users-modal .modal-title {
            color: #16324f;
            font-weight: 800;
        }

        .users-modal .modal-body {
            padding: 1.35rem;
        }

        .users-modal .modal-footer {
            padding: 1rem 1.35rem 1.35rem;
            border-top: 1px solid rgba(14, 75, 131, 0.08);
        }

        .users-modal .form-label {
            color: #36516c;
            font-weight: 700;
        }

        .users-modal-section {
            margin-bottom: 1rem;
            padding: 1rem;
            border: 1px solid rgba(14, 75, 131, 0.08);
            border-radius: 18px;
            background: #fbfdff;
        }

        .users-modal-section-title {
            margin-bottom: 0.9rem;
            color: #16324f;
            font-size: 0.92rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        @media (max-width: 991.98px) {
            .users-hero,
            .users-panel-header,
            .users-results-meta {
                flex-direction: column;
                align-items: stretch;
            }

            .users-hero-actions {
                width: 100%;
            }

            .users-primary-btn,
            .users-search-box,
            .users-filter-select {
                width: 100%;
            }

            .users-toolbar {
                justify-content: stretch;
            }
        }

        @media (max-width: 767.98px) {
            .users-admin-page {
                padding-bottom: 0.5rem;
            }

            .users-hero,
            .users-panel {
                border-radius: 20px;
            }

            .users-table {
                min-width: 920px;
            }
        }
    </style>

    <div class="users-admin-page">
        <div class="users-hero">
            <div class="users-hero-copy">
                <span class="users-eyebrow">
                    <i class="bi bi-shield-lock"></i>
                    Administration
                </span>
                <h1>User Management</h1>
                <div class="users-hero-meta">
                    <span><i class="bi bi-people"></i> {{ $totalUsers }} total accounts</span>
                    <span><i class="bi bi-check2-circle"></i> {{ $activeUsers }} active users</span>
                    <span><i class="bi bi-shield-check"></i> {{ $adminUsers }} elevated roles</span>
                </div>
            </div>
            <div class="users-hero-actions">
                <button type="button" class="btn users-primary-btn" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-person-plus"></i>
                    Add Additional User
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show users-success-alert" role="alert">
                <div class="d-flex align-items-start gap-3">
                    <i class="bi bi-check-circle-fill fs-4"></i>
                    <div class="flex-grow-1">
                        <strong class="d-block mb-1">User list updated successfully.</strong>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="users-stat-card users-stat-total">
                    <div class="users-stat-top">
                        <span class="users-stat-label">Total Accounts</span>
                        <span class="users-stat-icon"><i class="bi bi-people-fill"></i></span>
                    </div>
                    <h2>{{ $totalUsers }}</h2>
                    <p>All registered users accounts.</p>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="users-stat-card users-stat-active">
                    <div class="users-stat-top">
                        <span class="users-stat-label">Active Users</span>
                        <span class="users-stat-icon"><i class="bi bi-person-check-fill"></i></span>
                    </div>
                    <h2>{{ $activeUsers }}</h2>
                    <p>Accounts currently allowed to sign in</p>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="users-stat-card users-stat-admin">
                    <div class="users-stat-top">
                        <span class="users-stat-label">Admin Coverage</span>
                        <span class="users-stat-icon"><i class="bi bi-shield-fill-check"></i></span>
                    </div>
                    <h2>{{ $adminUsers }}</h2>
                    <p>Administrator and system administrator accounts.</p>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="users-stat-card users-stat-inactive">
                    <div class="users-stat-top">
                        <span class="users-stat-label">Inactive Users</span>
                        <span class="users-stat-icon"><i class="bi bi-person-dash-fill"></i></span>
                    </div>
                    <h2>{{ $inactiveUsers }}</h2>
                    <p>Accounts that are currently disabled or on hold.</p>
                </div>
            </div>
        </div>

        <div class="users-panel">
            <div class="users-panel-header">
                <div class="users-panel-title">
                    <h5>All Users</h5>
                </div>
                <div class="users-toolbar">
                    <div class="users-search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" id="userTableSearch" placeholder="Search by name, email, or role">
                    </div>
                    <select class="form-select users-filter-select" id="userStatusFilter">
                        <option value="all">All Statuses</option>
                        <option value="active">Active Only</option>
                        <option value="inactive">Inactive Only</option>
                    </select>
                    <select class="form-select users-filter-select" id="userGroupFilter">
                        <option value="all">All Roles</option>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                        <option value="sysadmin">System Admin</option>
                    </select>
                </div>
            </div>

            <div class="users-results-meta">
                <div>
                    <span class="users-results-count" id="usersVisibleCount">{{ $totalUsers }}</span>
                    <span>users shown</span>
                </div>
                <div class="users-legend">
                    <span style="color:#168a5d;">Active</span>
                    <span style="color:#b77700;">Inactive</span>
                    <span style="color:#2163d6;">System admin</span>
                    <span style="color:#be385a;">Admin</span>
                </div>
            </div>

            <div class="table-responsive users-table-wrap">
                <table class="table users-table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>User Group</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        @forelse($users as $user)
                            @php
                                $fullName = trim($user->firstname . ' ' . ($user->middlename ? $user->middlename . ' ' : '') . $user->lastname);
                                $initials = strtoupper(substr((string) $user->firstname, 0, 1) . substr((string) $user->lastname, 0, 1));
                                $initials = $initials !== '' ? $initials : 'U';
                                $userGroupLabel = $user->usergroup === 'sysadmin' ? 'System Admin' : ucfirst($user->usergroup);
                                $roleClass = $user->usergroup === 'sysadmin'
                                    ? 'users-pill-role-sysadmin'
                                    : ($user->usergroup === 'admin' ? 'users-pill-role-admin' : 'users-pill-role-user');
                            @endphp
                            <tr
                                class="users-table-row"
                                data-search="{{ strtolower($fullName . ' ' . $user->email . ' ' . $user->usergroup) }}"
                                data-status="{{ $user->active ? 'active' : 'inactive' }}"
                                data-group="{{ $user->usergroup }}"
                            >
                                <td class="text-muted fw-semibold">{{ $user->id }}</td>
                                <td>
                                    <div class="users-user-cell">
                                        <span class="users-avatar">{{ $initials }}</span>
                                        <div>
                                            <div class="users-user-name">{{ $fullName }}</div>
                                            <div class="users-user-subtext">{{ $userGroupLabel }} account</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="users-email">{{ $user->email }}</div>
                                </td>
                                <td>
                                    <span class="users-pill {{ $roleClass }}">{{ $userGroupLabel }}</span>
                                </td>
                                <td>
                                    <span class="users-pill {{ $user->active ? 'users-pill-status-active' : 'users-pill-status-inactive' }}">
                                        {{ $user->active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="users-user-name">{{ $user->created_at->format('M d, Y') }}</div>
                                    <div class="users-date-subtext">Member since {{ $user->created_at->format('Y') }}</div>
                                </td>
                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-sm users-action-btn view-user-btn"
                                        data-user-id="{{ $user->id }}"
                                        data-user-firstname="{{ $user->firstname }}"
                                        data-user-middlename="{{ $user->middlename }}"
                                        data-user-lastname="{{ $user->lastname }}"
                                        data-user-email="{{ $user->email }}"
                                        data-user-group="{{ $user->usergroup }}"
                                        data-user-active="{{ $user->active }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#userModal"
                                    >
                                        <i class="bi bi-eye"></i>
                                        View
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="users-empty-state">
                                        <i class="bi bi-people"></i>
                                        <div class="fw-bold mb-1">No users found</div>
                                        <div>Create a new account to get started.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        <tr id="usersNoResultsRow" class="d-none">
                            <td colspan="7">
                                <div class="users-empty-state">
                                    <i class="bi bi-funnel"></i>
                                    <div class="fw-bold mb-1">No users match the current filters</div>
                                    <div>Try a different search term or reset the selected filters.</div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal fade users-modal" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title" id="addUserModalLabel">Add Additional User</h5>
                            <div class="text-muted small mt-1">Create a new account and assign its initial role.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if ($errors->adduser->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="background:rgba(255,0,0,0.12);border:1.5px solid #ff4d4f;color:#3b0b0b;padding:14px 16px 14px 16px;border-radius:10px;box-shadow:0 2px 8px rgba(255,77,79,0.15);">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="bi bi-exclamation-triangle-fill" style="font-size:1.3rem;color:#ff4d4f;margin-top:2px;"></i>
                                    <div style="flex:1;">
                                        <strong style="display:block;margin-bottom:4px;">Validation failed.</strong>
                                        <ul class="mb-0" style="padding-left:18px;">
                                            @foreach ($errors->adduser->all() as $error)
                                                <li style="word-wrap: break-word; white-space: normal;">{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>
                        @endif

                        <form id="addUserForm" method="POST" action="{{ route('admin.addUser') }}">
                            @csrf
                            <div class="users-modal-section">
                                <div class="users-modal-section-title">Account Setup</div>
                                <div class="mb-3">
                                    <label for="addUserGroup" class="form-label">User Group</label>
                                    <select class="form-select" id="addUserGroup" name="usergroup" required>
                                        <option value="">Select User Group</option>
                                        <option value="user">User</option>
                                        <option value="admin">Admin</option>
                                        @if(Auth::user()->usergroup == 'sysadmin')
                                            <option value="sysadmin">System Admin</option>
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class="users-modal-section">
                                <div class="users-modal-section-title">Personal Details</div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="addFirstname" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="addFirstname" name="firstname" value="{{ old('firstname') }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="addMiddlename" class="form-label">Middle Name</label>
                                        <input type="text" class="form-control" id="addMiddlename" name="middlename" value="{{ old('middlename') }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="addLastname" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="addLastname" name="lastname" value="{{ old('lastname') }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="addEmail" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="addEmail" name="email" value="{{ old('email') }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="users-modal-section mb-0">
                                <div class="users-modal-section-title">Security</div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="addPassword" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="addPassword" name="password" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="addPasswordConfirmation" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" id="addPasswordConfirmation" name="password_confirmation" required>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer px-0 pb-0 mt-4">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn users-primary-btn">Add User</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade users-modal" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="userModalLabel">User Details</h5>
                        <div class="text-muted small mt-1">Update role, account status, and optional credentials.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="userUpdateForm" method="POST" action="">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show m-3 mb-0" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="users-modal-section">
                            <div class="users-modal-section-title">Personal Details</div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="modalFirstname" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="modalFirstname" name="firstname" value="{{ old('firstname') }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="modalMiddlename" class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" id="modalMiddlename" name="middlename" value="{{ old('middlename') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="modalLastname" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="modalLastname" name="lastname" value="{{ old('lastname') }}" required>
                                </div>
                            </div>

                            <div class="mb-0">
                                <label for="modalEmail" class="form-label">Email</label>
                                <input type="email" class="form-control" id="modalEmail" name="email" value="{{ old('email') }}" required readonly>
                            </div>
                        </div>

                        <div class="users-modal-section">
                            <div class="users-modal-section-title">Access Control</div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="modalUserGroup" class="form-label">User Group</label>
                                    <select class="form-select" id="modalUserGroup" name="usergroup" required>
                                        <option value="admin">Admin</option>
                                        <option value="user">User</option>
                                        @if(Auth::user()->usergroup == 'sysadmin')
                                            <option value="sysadmin">System Admin</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-6 mb-0">
                                    <label for="modalActive" class="form-label">Status</label>
                                    <select class="form-select" id="modalActive" name="active" required>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="users-modal-section mb-0">
                            <div class="users-modal-section-title">Password Reset</div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="modalPassword" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="modalPassword" name="password" placeholder="Leave blank to keep current password">
                                    <small class="text-muted">Minimum 8 characters</small>
                                </div>
                                <div class="col-md-6 mb-0">
                                    <label for="modalPasswordConfirm" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="modalPasswordConfirm" name="password_confirmation">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn users-action-btn">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if ($errors->adduser->any())
                const addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
                addUserModal.show();
            @endif

            @if ($errors->any() && session('editing_user_id'))
                const userModal = new bootstrap.Modal(document.getElementById('userModal'));
                userModal.show();
                document.getElementById('userUpdateForm').action = `/users/{{ session('editing_user_id') }}`;
                document.getElementById('modalFirstname').value = @json(old('firstname', ''));
                document.getElementById('modalMiddlename').value = @json(old('middlename', ''));
                document.getElementById('modalLastname').value = @json(old('lastname', ''));
                document.getElementById('modalEmail').value = @json(old('email', ''));
                document.getElementById('modalUserGroup').value = @json(old('usergroup', ''));
                document.getElementById('modalActive').value = @json(old('active', ''));
            @endif

            document.querySelectorAll('.view-user-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const userId = this.dataset.userId;
                    const userFirstname = this.dataset.userFirstname;
                    const userMiddlename = this.dataset.userMiddlename;
                    const userLastname = this.dataset.userLastname;
                    const userEmail = this.dataset.userEmail;
                    const userGroup = this.dataset.userGroup;
                    const userActive = this.dataset.userActive;

                    document.getElementById('userUpdateForm').action = `/users/${userId}`;
                    document.getElementById('modalFirstname').value = userFirstname || '';
                    document.getElementById('modalMiddlename').value = userMiddlename || '';
                    document.getElementById('modalLastname').value = userLastname || '';
                    document.getElementById('modalEmail').value = userEmail || '';
                    document.getElementById('modalUserGroup').value = userGroup || '';
                    document.getElementById('modalActive').value = userActive || '';
                    document.getElementById('modalPassword').value = '';
                    document.getElementById('modalPasswordConfirm').value = '';
                });
            });

            const searchInput = document.getElementById('userTableSearch');
            const statusFilter = document.getElementById('userStatusFilter');
            const groupFilter = document.getElementById('userGroupFilter');
            const tableRows = Array.from(document.querySelectorAll('.users-table-row'));
            const visibleCount = document.getElementById('usersVisibleCount');
            const noResultsRow = document.getElementById('usersNoResultsRow');

            const applyTableFilters = () => {
                const searchTerm = (searchInput?.value || '').trim().toLowerCase();
                const selectedStatus = statusFilter?.value || 'all';
                const selectedGroup = groupFilter?.value || 'all';
                let matches = 0;

                tableRows.forEach(row => {
                    const matchesSearch = !searchTerm || row.dataset.search.includes(searchTerm);
                    const matchesStatus = selectedStatus === 'all' || row.dataset.status === selectedStatus;
                    const matchesGroup = selectedGroup === 'all' || row.dataset.group === selectedGroup;
                    const isVisible = matchesSearch && matchesStatus && matchesGroup;

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
                searchInput.addEventListener('input', applyTableFilters);
            }

            if (statusFilter) {
                statusFilter.addEventListener('change', applyTableFilters);
            }

            if (groupFilter) {
                groupFilter.addEventListener('change', applyTableFilters);
            }

            applyTableFilters();

            const addUserForm = document.getElementById('addUserForm');
            if (addUserForm) {
                addUserForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(addUserForm);
                    const modalBody = addUserForm.closest('.modal-body');
                    const existingAlert = modalBody.querySelector('.alert-danger');
                    if (existingAlert) {
                        existingAlert.remove();
                    }

                    let errorContainer = document.getElementById('addUserErrorMsg');
                    if (!errorContainer) {
                        errorContainer = document.createElement('div');
                        errorContainer.id = 'addUserErrorMsg';
                        errorContainer.className = 'alert alert-danger alert-dismissible fade show';
                        errorContainer.style.display = 'none';
                        errorContainer.style.background = 'rgba(255,0,0,0.12)';
                        errorContainer.style.border = '1.5px solid #ff4d4f';
                        errorContainer.style.color = '#3b0b0b';
                        errorContainer.style.padding = '14px 16px 14px 16px';
                        errorContainer.style.borderRadius = '10px';
                        errorContainer.style.boxShadow = '0 2px 8px rgba(255,77,79,0.15)';
                        modalBody.insertBefore(errorContainer, addUserForm);
                    }

                    errorContainer.style.display = 'none';
                    // build safe DOM for error container contents
                    while (errorContainer.firstChild) errorContainer.removeChild(errorContainer.firstChild);
                    const wrapper = document.createElement('div'); wrapper.className = 'd-flex align-items-start gap-2';
                    const icon = document.createElement('i'); icon.className = 'bi bi-exclamation-triangle-fill'; icon.style.fontSize = '1.3rem'; icon.style.color = '#ff4d4f'; icon.style.marginTop = '2px';
                    const content = document.createElement('div'); content.style.flex = '1';
                    const strong = document.createElement('strong'); strong.style.display = 'block'; strong.style.marginBottom = '4px'; strong.textContent = 'Validation failed.';
                    const listDiv = document.createElement('div'); listDiv.id = 'addUserErrorList';
                    content.appendChild(strong); content.appendChild(listDiv);
                    const closeBtn = document.createElement('button'); closeBtn.type = 'button'; closeBtn.className = 'btn-close'; closeBtn.setAttribute('data-bs-dismiss','alert'); closeBtn.setAttribute('aria-label','Close');
                    wrapper.appendChild(icon); wrapper.appendChild(content); wrapper.appendChild(closeBtn);
                    errorContainer.appendChild(wrapper);

                    const submitBtn = addUserForm.querySelector('button[type="submit"]');
                    let originalBtnHtml = '';
                    if (submitBtn) {
                        originalBtnHtml = submitBtn.innerHTML;
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = sanitizeHtml('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...');
                    }

                    const fetchWithTimeout = (url, options, timeout = 10000) => {
                        return Promise.race([
                            fetch(url, options),
                            new Promise((_, reject) => setTimeout(() => reject(new Error('timeout')), timeout))
                        ]);
                    };

                    fetchWithTimeout(addUserForm.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': formData.get('_token'),
                            'Accept': 'application/json',
                        },
                        body: formData
                    })
                    .then(async response => {
                        if (response.ok) {
                            try {
                                await response.json();
                            } catch (error) {
                            }

                            if (typeof bootstrap !== 'undefined') {
                                const modal = bootstrap.Modal.getInstance(document.getElementById('addUserModal'))
                                    || new bootstrap.Modal(document.getElementById('addUserModal'));
                                modal.hide();
                            }

                            window.location.reload();
                        } else {
                            let data;
                            try {
                                data = await response.json();
                            } catch (error) {
                                data = { message: 'Failed to add user. (Invalid server response)' };
                            }

                            const messages = new Set();
                            if (data.message) {
                                messages.add(data.message);
                            }
                            if (data.errors) {
                                Object.keys(data.errors).forEach(key => {
                                    data.errors[key].forEach(err => messages.add(err));
                                });
                            }

                            if (messages.size > 0 && errorContainer) {
                                const escHtml = s => String(s == null ? '' : s)
                                    .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
                                let html = '<ul class="mb-0" style="padding-left:18px;">';
                                messages.forEach(msg => {
                                    html += '<li>' + escHtml(msg) + '</li>';
                                });
                                html += '</ul>';
                                const listDiv = errorContainer.querySelector('#addUserErrorList');
                                if (listDiv) {
                                    // error list contains only escaped list HTML from messages above
                                    listDiv.innerHTML = sanitizeHtml(html);
                                }
                                errorContainer.style.display = 'block';
                            }
                        }
                    })
                    .catch(error => {
                        console.error('AJAX add user error:', error);
                        if (errorContainer) {
                            const listDiv = errorContainer.querySelector('#addUserErrorList');
                            if (listDiv) {
                                listDiv.textContent = 'An error occurred. Please try again.';
                            }
                            errorContainer.style.display = 'block';
                        }
                    })
                    .finally(() => {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = sanitizeHtml(originalBtnHtml || 'Add User');
                        }
                        if (typeof hideLoader === 'function') {
                            hideLoader();
                        }
                    });
                });
            }
        });
    </script>
@endsection
