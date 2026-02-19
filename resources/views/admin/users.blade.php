@extends('layouts.app')


@section('content')

    <!-- Add Additional User Button -->
    <div class="mb-3">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus"></i> Add Additional User
        </button>
    </div>

    <!-- Add Additional User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add Additional User</h5>
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

                        <button type="submit" class="btn btn-success">Add User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card" style="background:#fff; border:1px solid #e0e0e0; box-shadow:0 2px 8px rgba(0,0,0,0.08); border-radius:8px;">
        <div class="card-header">
            <h5 class="mb-0">All Users ({{ $users->count() }})</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <!-- Profile column removed -->
                            <th>Name</th>
                            <th>Email</th>
                            <th>User Group</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <!-- Profile cell removed -->
                                <td>{{ trim($user->firstname . ' ' . ($user->middlename ? $user->middlename . ' ' : '') . $user->lastname) }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->usergroup == 'admin')
                                        <span class="badge bg-danger">Admin</span>
                                    @elseif($user->usergroup == 'sysadmin')
                                        <span class="badge bg-primary">System Admin</span>
                                    @else
                                        <span class="badge bg-secondary">User</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-warning">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                                <td>
                                        <button class="btn btn-sm btn-primary view-user-btn" 
                                            data-user-id="{{ $user->id }}"
                                            data-user-firstname="{{ $user->firstname }}"
                                            data-user-middlename="{{ $user->middlename }}"
                                            data-user-lastname="{{ $user->lastname }}"
                                            data-user-email="{{ $user->email }}"
                                            data-user-group="{{ $user->usergroup }}"
                                            data-user-active="{{ $user->active }}"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#userModal">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No users found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- User Edit Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="userUpdateForm" method="POST" action="">
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
                @csrf
                @method('PUT')
                <div class="modal-body">

                    <!-- First Name -->
                    <div class="mb-3">
                        <label for="modalFirstname" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="modalFirstname" name="firstname" value="{{ old('firstname') }}" required>
                    </div>
                    <!-- Middle Name -->
                    <div class="mb-3">
                        <label for="modalMiddlename" class="form-label">Middle Name</label>
                        <input type="text" class="form-control" id="modalMiddlename" name="middlename" value="{{ old('middlename') }}">
                    </div>
                    <!-- Last Name -->
                    <div class="mb-3">
                        <label for="modalLastname" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="modalLastname" name="lastname" value="{{ old('lastname') }}" required>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="modalEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="modalEmail" name="email" value="{{ old('email') }}" required readonly>
                    </div>

                    <!-- User Group -->
                    <div class="mb-3">
                        <label for="modalUserGroup" class="form-label">User Group</label>
                        <select class="form-select" id="modalUserGroup" name="usergroup" required>
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                        @if(Auth::user()->usergroup == 'sysadmin')
                            <option value="sysadmin">System Admin</option>
                        @endif
                        </select>
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label for="modalActive" class="form-label">Status</label>
                        <select class="form-select" id="modalActive" name="active" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <!-- Password (Optional) -->
                    <div class="mb-3">
                        <label for="modalPassword" class="form-label">New Password (Optional)</label>
                        <input type="password" class="form-control" id="modalPassword" name="password" placeholder="Leave blank to keep current password">
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-3">
                        <label for="modalPasswordConfirm" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="modalPasswordConfirm" name="password_confirmation">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // If there are validation errors for adding user, reopen Add User modal
        @if ($errors->adduser->any())
            const addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
            addUserModal.show();
        @endif

        // If there are validation errors, open the modal and fill with old input
        @if ($errors->any() && session('editing_user_id'))
            const userModal = new bootstrap.Modal(document.getElementById('userModal'));
            userModal.show();
            document.getElementById('userUpdateForm').action = `/users/{{ session('editing_user_id') }}`;
            document.getElementById('modalName').value = @json(old('name', ''));
            document.getElementById('modalEmail').value = @json(old('email', ''));
            document.getElementById('modalUserGroup').value = @json(old('usergroup', ''));
            document.getElementById('modalActive').value = @json(old('active', ''));
        @endif

        // Normal operation: fill modal fields from button data
        document.querySelectorAll('.view-user-btn').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.dataset.userId;
                const userFirstname = this.dataset.userFirstname;
                const userMiddlename = this.dataset.userMiddlename;
                const userLastname = this.dataset.userLastname;
                const userEmail = this.dataset.userEmail;
                const userGroup = this.dataset.userGroup;
                const userActive = this.dataset.userActive;

                // Update form action
                document.getElementById('userUpdateForm').action = `/users/${userId}`;

                // Populate form fields
                document.getElementById('modalFirstname').value = userFirstname || '';
                document.getElementById('modalMiddlename').value = userMiddlename || '';
                document.getElementById('modalLastname').value = userLastname || '';
                document.getElementById('modalEmail').value = userEmail || '';
                document.getElementById('modalUserGroup').value = userGroup || '';
                document.getElementById('modalActive').value = userActive || '';
                // Clear password fields
                document.getElementById('modalPassword').value = '';
                document.getElementById('modalPasswordConfirm').value = '';
            });
        });

        // AJAX submission for Add Additional User (no full page reload on errors)
        const addUserForm = document.getElementById('addUserForm');
        if (addUserForm) {
            addUserForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(addUserForm);

                // Remove existing error alert (server-rendered or previous AJAX)
                const modalBody = addUserForm.closest('.modal-body');
                const existingAlert = modalBody.querySelector('.alert-danger');
                if (existingAlert) {
                    existingAlert.remove();
                }

                // Prepare or reuse error container (styled like login/register alerts)
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
                errorContainer.innerHTML = '<div class="d-flex align-items-start gap-2">'
                    + '<i class="bi bi-exclamation-triangle-fill" style="font-size:1.3rem;color:#ff4d4f;margin-top:2px;"></i>'
                    + '<div style="flex:1;"><strong style="display:block;margin-bottom:4px;">Validation failed.</strong><div id="addUserErrorList"></div></div>'
                    + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
                    + '</div>';

                const submitBtn = addUserForm.querySelector('button[type="submit"]');
                let originalBtnHtml = '';
                if (submitBtn) {
                    originalBtnHtml = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...';
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
                        // On success, reload to show new user in list
                        try {
                            await response.json();
                        } catch (e) {}
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
                        } catch (e) {
                            data = { message: 'Failed to add user. (Invalid server response)' };
                        }

                        const messages = new Set();
                        if (data.message) messages.add(data.message);
                        if (data.errors) {
                            Object.keys(data.errors).forEach(key => {
                                data.errors[key].forEach(err => messages.add(err));
                            });
                        }

                        if (messages.size > 0 && errorContainer) {
                            let html = '<ul class="mb-0" style="padding-left:18px;">';
                            messages.forEach(msg => {
                                html += '<li>' + msg + '</li>';
                            });
                            html += '</ul>';
                            const listDiv = errorContainer.querySelector('#addUserErrorList');
                            if (listDiv) {
                                listDiv.innerHTML = html;
                            }
                            errorContainer.style.display = 'block';
                        }
                    }
                })
                .catch(err => {
                    console.error('AJAX add user error:', err);
                    if (errorContainer) {
                        const listDiv = errorContainer.querySelector('#addUserErrorList');
                        if (listDiv) {
                            listDiv.innerHTML = 'An error occurred. Please try again.';
                        }
                        errorContainer.style.display = 'block';
                    }
                })
                .finally(() => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnHtml || 'Add User';
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