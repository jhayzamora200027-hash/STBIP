<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-login-bg border-0">
            <div class="modal-body p-3">
                <div class="modal-login-content" style="padding: 15px;">
                    <h2 id="registerModalLabel" style="margin-bottom: 15px; font-size: 1.5rem;">Register Your Account</h2>

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    <div id="registerErrorMsg" style="display:none;margin-bottom:12px;font-size:1.1rem;font-weight:bold;color:#fff;background:#ff4d4f;padding:12px 16px;border-radius:8px;text-align:center;box-shadow:0 2px 8px rgba(255,77,79,0.18);z-index:10;"></div>
                    @if ($errors->register->any())
                        <div class="alert alert-danger alert-dismissible fade show py-2" style="max-height: 200px; overflow-y: auto;" role="alert">
                            <ul class="mb-0" style="font-size: 0.875rem;">
                                @foreach ($errors->register->all() as $error)
                                    <li style="word-wrap: break-word; white-space: normal;">{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    <style>
                        .register-form-label {
                            color: #fff;
                            text-align: left;
                            font-size: 0.875rem;
                            margin-bottom: 4px;
                            display: block;
                        }
                        .register-form-input {
                            width: 100%;
                            padding: 8px 10px;
                            border: none;
                            border-radius: 6px;
                            background: rgba(255,255,255,0.2);
                            color: #fff;
                            font-size: 0.875rem;
                        }
                        .register-form-input::placeholder {
                            color: rgba(255,255,255,0.6);
                        }
                        select.register-form-input {
                            color: #fff !important;
                        }
                        select.register-form-input option {
                            color: #000 !important;
                        }
                        .form-group-compact {
                            margin-bottom: 12px;
                        }
                    </style>
                    
                    <form method="POST" action="{{ route('register') }}" id="ajaxRegisterForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-compact">
                                    <label for="registerFirstName" class="register-form-label">First Name</label>
                                    <input type="text" id="registerFirstName" name="firstname" placeholder="First Name" value="{{ old('firstname') }}" class="register-form-input">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-compact">
                                    <label for="registerMiddleName" class="register-form-label">Middle Name</label>
                                    <input type="text" id="registerMiddleName" name="middlename" placeholder="Middle Name" value="{{ old('middlename') }}" class="register-form-input">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-compact">
                                    <label for="registerLastName" class="register-form-label">Last Name</label>
                                    <input type="text" id="registerLastName" name="lastname" placeholder="Last Name" value="{{ old('lastname') }}" class="register-form-input">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-compact">
                                    <label for="registerEmail" class="register-form-label">Email Address</label>
                                    <input type="email" id="registerEmail" name="email" placeholder="Email address" value="{{ old('email') }}" class="register-form-input">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-compact">
                                    <label for="registerPhone" class="register-form-label">Phone Number</label>
                                    <input type="text" id="registerPhone" name="phonenumber" placeholder="Phone Number" value="{{ old('phonenumber') }}" class="register-form-input">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-compact">
                                    <label for="registerGender" class="register-form-label">Gender</label>
                                    <select name="gender" id="registerGender" class="register-form-input">
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-compact">
                                    <label for="registerPassword" class="register-form-label">Password</label>
                                    <input type="password" id="registerPassword" name="password" placeholder="Password" class="register-form-input">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-compact">
                                    <label for="registerPasswordConfirmation" class="register-form-label">Confirm Password</label>
                                    <input type="password" id="registerPasswordConfirmation" name="password_confirmation" placeholder="Confirm Password" class="register-form-input">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group-compact">
                            <label for="registerAddress" class="register-form-label">Address</label>
                            <textarea id="registerAddress" name="address" placeholder="Address" rows="2" class="register-form-input">{{ old('address') }}</textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mt-2" style="padding: 10px;">Register</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Success Modal --}}
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="successModalLabel">
                    <i class="bi bi-check-circle-fill me-2"></i>Registration Successful
                </h5>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-hourglass-split" style="font-size: 3rem; color: #0d6efd;"></i>
                <h4 class="mt-3 mb-3">Thank You for Registering!</h4>
                <p class="mb-0">Your registration has been submitted successfully.</p>
                <p class="text-muted">Please wait for approval from the administrator before you can log in.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Show register modal if there are register errors (server-side fallback)
    document.addEventListener('DOMContentLoaded', function() {
        @if ($errors->register->any())
            let registerModal = new bootstrap.Modal(document.getElementById('registerModal'));
            registerModal.show();
        @endif
        
        @if (session('success') === 'Registration Successful')
            let successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
        @endif

        // AJAX registration logic (mirrors AJAX login)
        const registerForm = document.getElementById('ajaxRegisterForm');
        if (registerForm) {
            registerForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(registerForm);

                // Clear any existing error list in the alert (server-rendered)
                const existingAlert = registerForm.closest('.modal-login-content').querySelector('.alert-danger');
                if (existingAlert) {
                    existingAlert.remove();
                }

                // Use dedicated inline error container (styled like login)
                let errorContainer = document.getElementById('registerErrorMsg');
                if (errorContainer) {
                    errorContainer.style.display = 'none';
                    errorContainer.innerHTML = '';
                }

                // Show loading state on button
                const submitBtn = registerForm.querySelector('button[type="submit"]');
                let originalBtnHtml = '';
                if (submitBtn) {
                    originalBtnHtml = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Registering...';
                }

                const fetchWithTimeout = (url, options, timeout = 10000) => {
                    return Promise.race([
                        fetch(url, options),
                        new Promise((_, reject) => setTimeout(() => reject(new Error('timeout')), timeout))
                    ]);
                };

                fetchWithTimeout(registerForm.action, {
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
                        // On success, show success modal and close register modal
                        try {
                            const data = await response.json();
                            if (typeof bootstrap !== 'undefined') {
                                const regModalInstance = bootstrap.Modal.getOrCreateInstance(document.getElementById('registerModal'));
                                regModalInstance.hide();
                                const successModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('successModal'));
                                successModal.show();
                            } else if (data.redirect) {
                                window.location.href = data.redirect;
                            } else {
                                window.location.reload();
                            }
                        } catch (e) {
                            window.location.reload();
                        }
                    } else {
                        let data;
                        try {
                            data = await response.json();
                        } catch (e) {
                            data = { message: 'Registration failed. (Invalid server response)' };
                        }

                        const messages = new Set();
                        if (data.message) messages.add(data.message);
                        if (data.errors) {
                            Object.keys(data.errors).forEach(key => {
                                data.errors[key].forEach(err => messages.add(err));
                            });
                        }

                        if (messages.size > 0 && errorContainer) {
                            let html;
                            const arr = Array.from(messages);
                            if (arr.length > 1) {
                                html = arr[0] + '<ul style="margin:0;padding-left:18px;font-size:0.875rem;">';
                                for (let i = 1; i < arr.length; i++) {
                                    html += '<li style="word-wrap: break-word; white-space: normal;">' + arr[i] + '</li>';
                                }
                                html += '</ul>';
                            } else {
                                html = arr[0];
                            }
                            errorContainer.innerHTML = html;
                            errorContainer.style.display = 'block';
                        }
                    }
                })
                .catch(err => {
                    console.error('AJAX register fetch error:', err);
                    if (errorContainer) {
                        errorContainer.innerHTML = 'An error occurred. Please try again.';
                        errorContainer.style.display = 'block';
                    }
                })
                .finally(() => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnHtml || 'Register';
                    }
                });
            });
        }
    });
</script>