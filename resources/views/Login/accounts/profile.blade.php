@php
    $profileUser = Auth::user();
    $profilePictureUrl = $profileUser->profile_picture_url;
    $profileDisplayName = $profileUser->display_name;
    $profileInitials = $profileUser->initials;
@endphp

<style>
    .profile-modal .modal-dialog {
        max-width: 960px;
    }

    .profile-modal .modal-content {
        border: 0;
        border-radius: 28px;
        overflow: hidden;
        box-shadow: 0 30px 70px rgba(8, 43, 81, 0.24);
    }

    .profile-modal .modal-header {
        position: relative;
        padding: 0;
        border: 0;
    }

    .profile-modal-hero {
        position: relative;
        width: 100%;
        padding: 1.6rem 1.6rem 1.4rem;
        background: rgba(5, 44, 91, 0.98);
        color: #fff;
    }

    .profile-modal-hero::before,
    .profile-modal-hero::after {
        content: '';
        position: absolute;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.08);
        pointer-events: none;
    }

    .profile-modal-hero::before {
        width: 180px;
        height: 180px;
        top: -75px;
        right: -55px;
    }

    .profile-modal-hero::after {
        width: 110px;
        height: 110px;
        bottom: -38px;
        left: 42%;
    }

    .profile-modal-close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        z-index: 2;
        filter: invert(1) grayscale(1) brightness(200%);
    }

    .profile-modal-hero-content {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.25rem;
    }

    .profile-modal-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        margin-bottom: 0.75rem;
        padding: 0.42rem 0.8rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .profile-modal-title {
        margin: 0;
        font-size: clamp(1.6rem, 2vw, 2.2rem);
        font-weight: 800;
        letter-spacing: -0.03em;
    }

    .profile-modal-subtitle {
        margin: 0.7rem 0 0;
        color: rgba(255, 255, 255, 0.82);
        line-height: 1.6;
    }

    .profile-modal-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 88px;
        height: 88px;
        border-radius: 28px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.18);
        font-size: 2rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        overflow: hidden;
    }

    .profile-modal-avatar img,
    .profile-summary-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-modal .modal-body {
        padding: 1.5rem;
        background: linear-gradient(180deg, #f7fbff 0%, #ffffff 100%);
    }

    .profile-alert {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 14px 30px rgba(8, 43, 81, 0.08);
    }

    .profile-alert-success {
        background: linear-gradient(135deg, rgba(26, 167, 104, 0.14), rgba(20, 124, 81, 0.08));
        color: #106844;
    }

    .profile-alert-danger {
        background: linear-gradient(135deg, rgba(217, 84, 107, 0.14), rgba(186, 48, 74, 0.08));
        color: #8f2038;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: minmax(260px, 300px) minmax(0, 1fr);
        gap: 1.25rem;
    }

    .profile-summary-card,
    .profile-form-card {
        border: 1px solid rgba(14, 75, 131, 0.1);
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.92);
        box-shadow: 0 18px 36px rgba(8, 43, 81, 0.08);
    }

    .profile-summary-card {
        padding: 1.25rem;
    }

    .profile-summary-top {
        text-align: center;
        padding-bottom: 1.1rem;
        border-bottom: 1px solid rgba(14, 75, 131, 0.08);
    }

    .profile-summary-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 84px;
        height: 84px;
        margin-bottom: 0.9rem;
        border-radius: 26px;
        background: linear-gradient(135deg, rgba(33, 99, 214, 0.15), rgba(11, 138, 109, 0.18));
        color: #0f4d8c;
        font-size: 1.85rem;
        font-weight: 800;
        overflow: hidden;
    }

    .profile-picture-picker {
        position: relative;
        display: block;
        cursor: pointer;
    }

    .profile-picture-picker:hover .profile-picture-overlay,
    .profile-picture-picker:focus-visible .profile-picture-overlay {
        opacity: 1;
    }

    .profile-picture-input {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    .profile-picture-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        border-radius: 26px;
        background: rgba(6, 48, 110, 0.7);
        color: #fff;
        opacity: 0;
        transition: opacity 0.18s ease;
        text-align: center;
        padding: 0.75rem;
    }

    .profile-picture-overlay i {
        font-size: 1.15rem;
    }

    .profile-picture-overlay span {
        font-size: 0.75rem;
        font-weight: 700;
        line-height: 1.35;
    }

    .profile-picture-note {
        margin-top: 0.7rem;
        color: #6c7f91;
        font-size: 0.84rem;
        line-height: 1.45;
    }

    .profile-summary-name {
        margin: 0;
        color: #16324f;
        font-size: 1.15rem;
        font-weight: 800;
    }

    .profile-summary-email {
        margin-top: 0.35rem;
        color: #698298;
        word-break: break-word;
    }

    .profile-summary-list {
        display: grid;
        gap: 0.8rem;
        margin-top: 1rem;
    }

    .profile-summary-item {
        padding: 0.9rem 1rem;
        border: 1px solid rgba(14, 75, 131, 0.08);
        border-radius: 18px;
        background: #fbfdff;
    }

    .profile-summary-label {
        display: block;
        margin-bottom: 0.2rem;
        color: #6b8196;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .profile-summary-value {
        color: #16324f;
        font-weight: 700;
    }

    .profile-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.42rem 0.75rem;
        border-radius: 999px;
        background: rgba(33, 99, 214, 0.1);
        color: #2163d6;
        font-size: 0.8rem;
        font-weight: 800;
    }

    .profile-pill::before {
        content: '';
        width: 7px;
        height: 7px;
        border-radius: 999px;
        background: currentColor;
    }

    .profile-form-card {
        padding: 1.2rem;
    }

    .profile-section {
        margin-bottom: 1rem;
        padding: 1rem;
        border: 1px solid rgba(14, 75, 131, 0.08);
        border-radius: 20px;
        background: #fbfdff;
    }

    .profile-section:last-child {
        margin-bottom: 0;
    }

    .profile-section-title {
        margin-bottom: 0.9rem;
        color: #16324f;
        font-size: 0.92rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .profile-modal .form-label {
        color: #36516c;
        font-weight: 700;
    }

    .profile-modal .form-control,
    .profile-modal .form-select,
    .profile-readonly {
        border: 1px solid rgba(14, 75, 131, 0.14);
        border-radius: 14px;
        background: #f8fbff;
        color: #16324f;
        transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
    }

    .profile-modal .form-control:focus,
    .profile-modal .form-select:focus {
        border-color: rgba(33, 99, 214, 0.45);
        box-shadow: 0 0 0 0.22rem rgba(33, 99, 214, 0.12);
        background: #fff;
    }

    .profile-readonly {
        min-height: 48px;
        padding: 0.78rem 0.95rem;
        display: flex;
        align-items: center;
        font-weight: 600;
    }

    .profile-help {
        margin-top: 0.35rem;
        color: #6c7f91;
        font-size: 0.85rem;
    }

    .profile-submit-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.85rem 1.15rem;
        border: none;
        border-radius: 14px;
        background: linear-gradient(135deg, #2970eb, #1956b3);
        color: #fff;
        font-weight: 700;
        box-shadow: 0 14px 28px rgba(33, 99, 214, 0.2);
    }

    .profile-submit-btn:hover,
    .profile-submit-btn:focus {
        color: #fff;
        background: linear-gradient(135deg, #235fc7, #15479f);
    }

    @media (max-width: 991.98px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }

        .profile-modal-hero-content {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="modal fade profile-modal" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="profile-modal-hero">
                    <button type="button" class="btn-close profile-modal-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="profile-modal-hero-content">
                        <div>
                            <span class="profile-modal-eyebrow">
                                <i class="bi bi-person-badge"></i>
                                Account Profile
                            </span>
                            <h5 class="profile-modal-title" id="profileModalLabel">Manage Your Profile</h5>
                        </div>
                        <div class="profile-modal-avatar">
                            @if($profilePictureUrl)
                                <img src="{{ $profilePictureUrl }}" alt="{{ $profileDisplayName }}" id="profileHeroAvatarImage">
                            @else
                                <span id="profileHeroAvatarFallback">{{ $profileInitials }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                @if ($errors->profileUpdate->any())
                    <div class="alert alert-danger profile-alert profile-alert-danger alert-dismissible fade show" role="alert">
                        <div class="d-flex align-items-start gap-3">
                            <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                            <div class="flex-grow-1">
                                <strong class="d-block mb-1">Please correct the highlighted profile fields.</strong>
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->profileUpdate->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                @endif

                @if(session('profile_success'))
                    <div class="alert alert-success profile-alert profile-alert-success alert-dismissible fade show" role="alert">
                        <div class="d-flex align-items-start gap-3">
                            <i class="bi bi-check-circle-fill fs-4"></i>
                            <div class="flex-grow-1">
                                <strong class="d-block mb-1">Profile updated.</strong>
                                <span>{{ session('profile_success') }}</span>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                @endif

                @if(session('profile_error'))
                    <div class="alert alert-danger profile-alert profile-alert-danger alert-dismissible fade show" role="alert">
                        <div class="d-flex align-items-start gap-3">
                            <i class="bi bi-exclamation-octagon-fill fs-4"></i>
                            <div class="flex-grow-1">
                                <strong class="d-block mb-1">Profile update failed.</strong>
                                <span>{{ session('profile_error') }}</span>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                @endif

                <div class="profile-grid">
                    <aside class="profile-summary-card">
                        <div class="profile-summary-top">
                            <label class="profile-picture-picker" for="profile_picture">
                                <input type="file" name="profile_picture" id="profile_picture" class="profile-picture-input" accept="image/png,image/jpeg,image/webp" form="profileUpdateForm">
                                <div class="profile-summary-avatar">
                                    @if($profilePictureUrl)
                                        <img src="{{ $profilePictureUrl }}" alt="{{ $profileDisplayName }}" id="profileSummaryAvatarImage">
                                    @else
                                        <span id="profileSummaryAvatarFallback">{{ $profileInitials }}</span>
                                    @endif
                                </div>
                                <div class="profile-picture-overlay">
                                    <i class="bi bi-camera-fill"></i>
                                    <span>Upload profile picture</span>
                                </div>
                            </label>
                            <div class="profile-picture-note">PNG, JPG, or WEBP up to 2 MB.</div>
                            <h6 class="profile-summary-name">{{ $profileDisplayName }}</h6>
                            <div class="profile-summary-email">{{ $profileUser->email }}</div>
                        </div>
                        <div class="profile-summary-list">
                            <div class="profile-summary-item">
                                <span class="profile-summary-label">User ID</span>
                                <div class="profile-summary-value">{{ $profileUser->user_id ?: 'Not assigned' }}</div>
                            </div>
                            <div class="profile-summary-item">
                                <span class="profile-summary-label">Role</span>
                                <div><span class="profile-pill">{{ $profileUser->usergroup === 'sysadmin' ? 'System Admin' : ucfirst($profileUser->usergroup) }}</span></div>
                            </div>
                            <div class="profile-summary-item">
                                <span class="profile-summary-label">Current Phone</span>
                                <div class="profile-summary-value">{{ $profileUser->phonenumber ?: 'Not set' }}</div>
                            </div>
                            <div class="profile-summary-item">
                                <span class="profile-summary-label">Current Address</span>
                                <div class="profile-summary-value">{{ $profileUser->address ?: 'Not set' }}</div>
                            </div>
                        </div>
                    </aside>

                    <div class="profile-form-card">
                        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" id="profileUpdateForm">
                            @csrf
                            @method('PUT')

                            <div class="profile-section">
                                <div class="profile-section-title">Identity</div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">First Name</label>
                                        <div class="profile-readonly">{{ $profileUser->firstname ?? 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Middle Name</label>
                                        <div class="profile-readonly">{{ $profileUser->middlename ?: 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Last Name</label>
                                        <div class="profile-readonly">{{ $profileUser->lastname ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="profile-section">
                                <div class="profile-section-title">Contact Details</div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="profile_email" class="form-label">Email</label>
                                        <input type="email" name="email" id="profile_email" value="{{ old('email', $profileUser->email) }}" class="form-control @error('email', 'profileUpdate') is-invalid @enderror">
                                        @error('email', 'profileUpdate')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="profile_phonenumber" class="form-label">Phone Number</label>
                                        <input type="text" name="phonenumber" id="profile_phonenumber" value="{{ old('phonenumber', $profileUser->phonenumber) }}" class="form-control @error('phonenumber', 'profileUpdate') is-invalid @enderror">
                                        @error('phonenumber', 'profileUpdate')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="profile_gender" class="form-label">Gender</label>
                                        <select name="gender" id="profile_gender" class="form-select @error('gender', 'profileUpdate') is-invalid @enderror">
                                            <option value="">Select Gender</option>
                                            <option value="Male" {{ old('gender', $profileUser->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                            <option value="Female" {{ old('gender', $profileUser->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                        </select>
                                        @error('gender', 'profileUpdate')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="profile_usergroup" class="form-label">User Group</label>
                                        <select name="usergroup" id="profile_usergroup" class="form-select @error('usergroup', 'profileUpdate') is-invalid @enderror" {{ $profileUser->usergroup == 'user' ? 'disabled' : '' }}>
                                            <option value="admin" {{ old('usergroup', $profileUser->usergroup) == 'admin' ? 'selected' : '' }}>Admin</option>
                                            <option value="user" {{ old('usergroup', $profileUser->usergroup) == 'user' ? 'selected' : '' }}>User</option>
                                            @if($profileUser->usergroup == 'sysadmin')
                                                <option value="sysadmin" {{ old('usergroup', $profileUser->usergroup) == 'sysadmin' ? 'selected' : '' }}>System Admin</option>
                                            @endif
                                        </select>
                                        @if($profileUser->usergroup == 'user')
                                            <input type="hidden" name="usergroup" value="{{ $profileUser->usergroup }}">
                                        @endif
                                        @error('usergroup', 'profileUpdate')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="profile_address" class="form-label">Address</label>
                                        <textarea name="address" id="profile_address" class="form-control @error('address', 'profileUpdate') is-invalid @enderror" rows="3">{{ old('address', $profileUser->address) }}</textarea>
                                        @error('address', 'profileUpdate')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="profile-section">
                                <div class="profile-section-title">Security Check</div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="profile_current_password" class="form-label">Current Password</label>
                                        <input type="password" name="current_password" id="profile_current_password" class="form-control @error('current_password', 'profileUpdate') is-invalid @enderror">
                                        <div class="profile-help">Required when changing account details or password. Not required for photo-only uploads.</div>
                                        @error('current_password', 'profileUpdate')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="profile_new_password" class="form-label">New Password</label>
                                        <input type="password" name="new_password" id="profile_new_password" class="form-control @error('new_password', 'profileUpdate') is-invalid @enderror">
                                        <div class="profile-help">Leave blank to keep your current password.</div>
                                        @error('new_password', 'profileUpdate')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="profile_new_password_confirmation" class="form-label">Confirm New Password</label>
                                        <input type="password" name="new_password_confirmation" id="profile_new_password_confirmation" class="form-control">
                                    </div>
                                    <div class="col-12">
                                        @error('profile_picture', 'profileUpdate')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn profile-submit-btn">
                                    <i class="bi bi-save"></i>
                                    Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const profileModalElement = document.getElementById('profileModal');
        if (!profileModalElement || typeof bootstrap === 'undefined') {
            return;
        }

        const profileModal = new bootstrap.Modal(profileModalElement);
        const shouldOpenProfileModal = @json(session('profile_modal_open', false) || request()->routeIs('profile'));
        const profilePictureInput = document.getElementById('profile_picture');
        const heroAvatar = document.getElementById('profileHeroAvatarImage');
        const summaryAvatar = document.getElementById('profileSummaryAvatarImage');
        const heroFallback = document.getElementById('profileHeroAvatarFallback');
        const summaryFallback = document.getElementById('profileSummaryAvatarFallback');

        const updateAvatarPreview = file => {
            if (!file) {
                return;
            }

            const reader = new FileReader();
            reader.onload = event => {
                const src = event.target?.result;
                if (!src) {
                    return;
                }

                if (heroAvatar) {
                    heroAvatar.src = src;
                } else if (heroFallback) {
                    heroFallback.outerHTML = '<img src="' + src + '" alt="{{ e($profileDisplayName) }}" id="profileHeroAvatarImage">';
                }

                if (summaryAvatar) {
                    summaryAvatar.src = src;
                } else if (summaryFallback) {
                    summaryFallback.outerHTML = '<img src="' + src + '" alt="{{ e($profileDisplayName) }}" id="profileSummaryAvatarImage">';
                }
            };
            reader.readAsDataURL(file);
        };

        if (profilePictureInput) {
            profilePictureInput.addEventListener('change', function() {
                updateAvatarPreview(this.files?.[0]);
            });
        }

        if (shouldOpenProfileModal) {
            profileModal.show();
        }
    });
</script>