@extends('layouts.app')

@section('content')
<style>
    .profile-picture-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .profile-picture-container {
        position: relative;
        cursor: pointer;
        display: inline-block;
    }
    .profile-picture-container img,
    .profile-default-icon {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 50%;
        display: block;
        border: 3px solid #ddd;
        margin: 0;
        padding: 0;
    }
    .profile-default-icon {
        background: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .camera-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s;
        color: white;
        pointer-events: none;
    }
    .profile-picture-container:hover .camera-overlay {
        opacity: 1;
    }
    .camera-overlay i {
        font-size: 2rem;
        margin-bottom: 5px;
    }
    .camera-overlay span {
        font-size: 0.75rem;
        text-align: center;
        line-height: 1.2;
    }
</style>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card border border-secondary" style="background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-radius: 8px;">
                <div class="card-header">Profile</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')


                        <div class="input-group mb-3">
                            <span class="input-group-text" id="inputGroup-sizing-sm">First Name</span>
                                <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $user->firstname ?? '') }}" class="form-control @error('first_name') is-invalid @enderror" readonly aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm" disabled = "true">
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="inputGroup-sizing-sm">Middle Name</span>
                                <input type="text" name="middle_name" id="middle_name" value="{{ old('middle_name', $user->middlename ?? '') }}" class="form-control @error('middle_name') is-invalid @enderror" readonly aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm" disabled = "true">
                            @error('middle_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="inputGroup-sizing-sm">Last Name</span>
                                <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $user->lastname ?? '') }}" class="form-control @error('last_name') is-invalid @enderror" readonly aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm" disabled = "true">
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="input-group mb-3">
                            <span class="input-group-text" id="inputGroup-sizing-sm">User ID</span>
                            <input type="text" name="user_id" id="user_id" value="{{ old('user_id', $user->user_id) }}" class="form-control" readonly aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm" disabled = "true">
                        </div>

                        <div class="input-group mb-3">
                            <span class="input-group-text" id="inputGroup-sizing-sm">Email</span>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="input-group mb-3">
                            <span class="input-group-text" id="inputGroup-sizing-sm">Phone Number</span>
                            <input type="text" name="phonenumber" id="phonenumber" value="{{ old('phonenumber', $user->phonenumber) }}" class="form-control @error('phonenumber') is-invalid @enderror" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
                            @error('phonenumber')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="input-group mb-3">
                            <span class="input-group-text" id="inputGroup-sizing-sm">Gender</span>
                            <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
                                <option value="">Select Gender</option>
                                <option value="Male" {{ old('gender', $user->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender', $user->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                    
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="2" aria-label="Address">{{ old('address', $user->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="input-group mb-3">
                            <span class="input-group-text" id="inputGroup-sizing-sm">User Group</span>
                            <select name="usergroup" id="usergroup" class="form-select @error('usergroup') is-invalid @enderror" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm" {{ $user->usergroup == 'user' ? 'disabled' : '' }}>
                                <option value="admin" {{ old('usergroup', $user->usergroup) == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="user" {{ old('usergroup', $user->usergroup) == 'user' ? 'selected' : '' }}>User</option>
                            @if($user->usergroup == 'sysadmin')
                                <option value="sysadmin" {{ old('usergroup', $user->usergroup) == 'sysadmin' ? 'selected' : '' }}>System Admin</option>
                            @endif
                            </select>
                            @if($user->usergroup == 'user')
                                <input type="hidden" name="usergroup" value="{{ $user->usergroup }}">
                            @endif
                            @error('usergroup')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password (required to save changes)</label>
                            <input type="password" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror">
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <h6 class="mb-3">Change Password (Optional)</h6>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" name="new_password" id="new_password" class="form-control @error('new_password') is-invalid @enderror">
                            <small class="text-muted">Leave blank to keep current password</small>
                            @error('new_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                            <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                    </form>

                    <!-- No profile attachments/history section -->
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Profile Update Success Modal --}}
<div class="modal fade" id="profileSuccessModal" tabindex="-1" aria-labelledby="profileSuccessModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="profileSuccessModalLabel">
                    <i class="bi bi-check-circle-fill me-2"></i>Profile Updated
                </h5>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-check-circle-fill" style="font-size: 3rem; color: #28a745;"></i>
                <h4 class="mt-3 mb-3">Profile Updated Successfully</h4>
                <p class="mb-0">Your profile information has been saved.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-dismiss alerts after 3 seconds
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            let alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                let bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 3000);

        // Show success modal when profile is successfully updated
        @if(session('success'))
            if (typeof bootstrap !== 'undefined') {
                var successModal = new bootstrap.Modal(document.getElementById('profileSuccessModal'));
                successModal.show();
            }
        @endif
    });
</script>

@endsection