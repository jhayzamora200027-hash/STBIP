@extends('layouts.app')

@section('content')
<div class="container">
    @if(Auth::user()->usergroup !== 'sysadmin' && Auth::user()->usergroup !== 'admin')
        <div class="alert alert-danger">
            You do not have permission to access this page.
            <a href="{{ route('main') }}" class="btn btn-primary btn-sm ms-3">Go Back</a>
        </div>
    @else

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card" style="background:#fff; border:1px solid #e0e0e0; box-shadow:0 2px 8px rgba(0,0,0,0.08); border-radius:8px;">
            <div class="card-header">
                <h5 class="mb-0">Pending Registrations ({{ $pendingUsers->count() }})</h5>
            </div>
            <div class="card-body">
                @if($pendingUsers->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-check-circle" style="font-size: 3rem; color: #28a745;"></i>
                        <p class="mt-3 text-muted">No pending registrations at this time.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Registered On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingUsers as $index => $user)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->created_at->format('M d, Y h:i A') }}</td>
                                        <td>
                                            <button class="btn btn-info btn-sm" 
                                                    onclick="showUserDetails({{ $user->id }})"
                                                    data-db-id="{{ $user->id }}"
                                                    data-user-id="{{ $user->user_id }}"
                                                    data-name="{{ $user->name }}"
                                                    data-email="{{ $user->email }}"
                                                    data-phone="{{ $user->phonenumber }}"
                                                    data-gender="{{ $user->gender }}"
                                                    data-address="{{ $user->address }}"
                                                    data-usergroup="{{ $user->usergroup }}"
                                                    data-created="{{ $user->created_at->format('M d, Y h:i A') }}">
                                                <i class="bi bi-eye"></i> View Details
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

{{-- Modal for User Details --}}
<div class="modal fade" id="userDetailsModal" tabindex="-1" aria-labelledby="userDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userDetailsModalLabel">Registration Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">User ID:</label>
                        <p id="detail-user-id" class="form-control-plaintext"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Full Name:</label>
                        <p id="detail-name" class="form-control-plaintext"></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Email Address:</label>
                        <p id="detail-email" class="form-control-plaintext"></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Phone Number:</label>
                        <p id="detail-phone" class="form-control-plaintext"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Gender:</label>
                        <p id="detail-gender" class="form-control-plaintext"></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">User Group:</label>
                        <select id="detail-usergroup-select" class="form-select" required>
                            <option value="">Select User Group</option>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        @if(Auth::user()->usergroup === 'sysadmin')
                            <option value="sysadmin">Sysadmin</option>
                        @endif
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Registered On:</label>
                        <p id="detail-created" class="form-control-plaintext"></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Address:</label>
                        <p id="detail-address" class="form-control-plaintext"></p>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Approval Decision:</label>
                        <form id="approvalForm" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" id="userId" name="user_id">
                            <div class="d-flex gap-3 mt-3">
                                <button type="button" class="btn btn-success btn-lg flex-fill" onclick="submitApproval('A')">
                                    <i class="bi bi-check-circle"></i> Approve
                                </button>
                                <button type="button" class="btn btn-danger btn-lg flex-fill" onclick="showRejectionModal()">
                                    <i class="bi bi-x-circle"></i> Reject
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            {{-- Modal for Rejection Reason --}}
            <div class="modal fade" id="rejectionReasonModal" tabindex="-1" aria-labelledby="rejectionReasonModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="rejectionReasonModalLabel">Rejection Reason</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="rejectionForm">
                                <div class="mb-3">
                                    <label for="rejectionReason" class="form-label">Please provide a reason for rejection:</label>
                                    <textarea class="form-control" id="rejectionReason" name="rejectionReason" rows="3" required></textarea>
                                </div>
                                <button type="button" class="btn btn-danger" onclick="submitApproval('R')">Submit Rejection</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentUserId = null;
    let userDetailsModal = null;
    let rejectionReasonModal = null;

    document.addEventListener('DOMContentLoaded', function() {
        const modalElement = document.getElementById('userDetailsModal');
        if (modalElement && typeof bootstrap !== 'undefined') {
            userDetailsModal = new bootstrap.Modal(modalElement);
        }
        const rejectionModalElement = document.getElementById('rejectionReasonModal');
        if (rejectionModalElement && typeof bootstrap !== 'undefined') {
            rejectionReasonModal = new bootstrap.Modal(rejectionModalElement);
        }
    });

    function showUserDetails(dbId) {
        const button = event.target.closest('button');
        
        // Populate modal with user data
        document.getElementById('detail-user-id').textContent = button.dataset.userId || 'N/A';
        document.getElementById('detail-name').textContent = button.dataset.name;
        document.getElementById('detail-email').textContent = button.dataset.email;
        document.getElementById('detail-phone').textContent = button.dataset.phone || 'N/A';
        document.getElementById('detail-gender').textContent = button.dataset.gender || 'N/A';
        document.getElementById('detail-address').textContent = button.dataset.address || 'N/A';
        const usergroupSelect = document.getElementById('detail-usergroup-select');
        if (usergroupSelect) {
            usergroupSelect.value = button.dataset.usergroup || '';
        }
        document.getElementById('detail-created').textContent = button.dataset.created;
        
        // Store current database ID (primary key)
        currentUserId = dbId;
        document.getElementById('userId').value = dbId;
        
        // Show modal
        if (userDetailsModal) {
            userDetailsModal.show();
        }
    }

    function showRejectionModal() {
        if (rejectionReasonModal) {
            document.getElementById('rejectionReason').value = '';
            rejectionReasonModal.show();
        }
    }

    function submitApproval(status) {
        if (!currentUserId) {
            alert('Error: User ID not found');
            return;
        }

        let approvalcomment = '';
        if (status === 'R') {
            approvalcomment = document.getElementById('rejectionReason').value.trim();
            if (!approvalcomment) {
                alert('Please provide a reason for rejection.');
                return;
            }
        }

        let usergroup = null;
        if (status === 'A') {
            const usergroupSelect = document.getElementById('detail-usergroup-select');
            usergroup = usergroupSelect ? usergroupSelect.value : '';
            if (!usergroup) {
                alert('Please select a user group before approval.');
                return;
            }
        }

        // Get approver (logged in user)
        const approvedby = '{{ Auth::user()->name }}';

        // Show loading state
        if (status === 'A') {
            const form = document.getElementById('approvalForm');
            form.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div><p>Processing...</p></div>';
        } else if (status === 'R') {
            document.getElementById('rejectionForm').innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div><p>Processing...</p></div>';
        }

        // Submit approval decision
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
                // Close modals and reload page
                if (userDetailsModal) {
                    userDetailsModal.hide();
                }
                if (rejectionReasonModal) {
                    rejectionReasonModal.hide();
                }
                alert(data.message);
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to update approval status'));
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing the request');
            window.location.reload();
        });
    }
</script>

<style>
    .form-control-plaintext {
        padding: 0.375rem 0;
        margin-bottom: 0;
        border: none;
        background-color: transparent;
    }
    
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        cursor: pointer;
    }
</style>
@endsection
