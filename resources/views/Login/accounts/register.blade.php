<!-- Register Modal -->
<div class="modal fade portal-auth-modal portal-register-modal" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl portal-register-dialog">
        <div class="modal-content border-0 portal-login-frame portal-register-frame">
            <div class="modal-body p-0">
                <div class="portal-register-shell">
                    <section class="portal-register-brand-panel portal-auth-pane">
                        <div class="portal-register-brand-copy">
                            <span class="portal-register-kicker">Account Request</span>
                            <h2>Create your STBIP access</h2>
                            <p>Submit your DSWD account request for administrator approval. Use accurate identity details so the account can be reviewed quickly.</p>
                        </div>

                        <div class="portal-register-highlights">
                            <div class="portal-register-highlight-card">
                                <i class="bi bi-envelope-check"></i>
                                <div>
                                    <strong>DSWD email required</strong>
                                    <span>Use your official <code>@dswd.gov.ph</code> address.</span>
                                </div>
                            </div>
                            <div class="portal-register-highlight-card">
                                <i class="bi bi-shield-lock"></i>
                                <div>
                                    <strong>Strong password policy</strong>
                                    <span>Passwords must include upper, lower, number, and symbol.</span>
                                </div>
                            </div>
                            <div class="portal-register-highlight-card">
                                <i class="bi bi-person-check"></i>
                                <div>
                                    <strong>Admin approval required</strong>
                                    <span>You can sign in only after your registration is approved.</span>
                                </div>
                            </div>
                        </div>

                        <div class="portal-register-illustration-wrap">
                            <div class="portal-register-illustration-glow"></div>
                            <div class="portal-register-stat-card portal-register-stat-card-main">
                                <strong>Approval flow</strong>
                                <span>Submit request</span>
                                <span>Admin review (Approve/Reject)</span>
                                <span>Portal access</span>
                            </div>
                            <div class="portal-register-stat-card portal-register-stat-card-alt">
                                <span class="portal-register-stat-badge">DSWD only</span>
                                <small>Use a valid government email for verification.</small>
                            </div>
                        </div>
                    </section>

                    <section class="portal-register-form-panel portal-auth-pane">
                        <div class="portal-register-form-wrap">
                            <p class="portal-login-eyebrow">Join the portal</p>
                            <h1 id="registerModalLabel">Register your account</h1>
                            <p class="portal-register-subtitle">Complete the form below. Required fields help us verify and approve your access request.</p>

                            @if (session('error'))
                                <div class="alert alert-danger portal-login-alert" role="alert">
                                    {{ session('error') }}
                                </div>
                            @endif

                            <div id="registerErrorMsg" class="portal-login-alert portal-login-alert-inline" style="display:none;"></div>

                            @if ($errors->register->any())
                                <div class="alert alert-danger alert-dismissible fade show portal-login-alert" role="alert">
                                    <div class="portal-login-alert-body">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        <ul class="mb-0">
                                            @foreach ($errors->register->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('register') }}" id="ajaxRegisterForm">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="portal-register-field">
                                            <label for="registerFirstName" class="portal-register-label">First Name *</label>
                                            <input type="text" id="registerFirstName" name="firstname" placeholder="Juan" value="{{ old('firstname') }}" class="portal-register-input">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="portal-register-field">
                                            <label for="registerMiddleName" class="portal-register-label">Middle Name</label>
                                            <input type="text" id="registerMiddleName" name="middlename" placeholder="Santos" value="{{ old('middlename') }}" class="portal-register-input">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="portal-register-field">
                                            <label for="registerLastName" class="portal-register-label">Last Name *</label>
                                            <input type="text" id="registerLastName" name="lastname" placeholder="Dela Cruz" value="{{ old('lastname') }}" class="portal-register-input">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="portal-register-field">
                                            <label for="registerEmail" class="portal-register-label">DSWD Email Address *</label>
                                            <input type="email" id="registerEmail" name="email" placeholder="name@dswd.gov.ph" value="{{ old('email') }}" class="portal-register-input">
                                            <small class="portal-register-help">Only official DSWD email addresses are accepted.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="portal-register-field">
                                            <label for="registerPhone" class="portal-register-label">Phone Number</label>
                                            <input type="text" id="registerPhone" name="phonenumber" placeholder="09XXXXXXXXX" value="{{ old('phonenumber') }}" class="portal-register-input">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="portal-register-field">
                                            <label for="registerGender" class="portal-register-label">Gender</label>
                                            <select name="gender" id="registerGender" class="portal-register-input portal-register-select">
                                                <option value="">Select Gender</option>
                                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                                <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Prefer not to say</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="portal-register-field portal-register-password-field">
                                            <label for="registerPassword" class="portal-register-label">Password *</label>
                                            <div class="portal-register-input-wrap">
                                                <input type="password" id="registerPassword" name="password" placeholder="Create a secure password" class="portal-register-input" aria-describedby="pwRequirements">
                                                <button type="button" class="portal-password-toggle portal-password-toggle-register" data-password-toggle data-target="registerPassword" aria-label="Show password" aria-pressed="false">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                            <div id="pwRequirements" class="pw-requirements" aria-live="polite">
                                                <div class="pw-popover-arrow"></div>
                                                <div class="pw-req-header">Password must include</div>
                                                <div class="pw-req-list">
                                                    <div class="pw-req-item" data-test="length"><span class="pw-req-icon" aria-hidden="true">○</span><span class="pw-req-text">At least 8 characters</span></div>
                                                    <div class="pw-req-item" data-test="upper"><span class="pw-req-icon" aria-hidden="true">○</span><span class="pw-req-text">At least 1 uppercase letter</span></div>
                                                    <div class="pw-req-item" data-test="lower"><span class="pw-req-icon" aria-hidden="true">○</span><span class="pw-req-text">At least 1 lowercase letter</span></div>
                                                    <div class="pw-req-item" data-test="number"><span class="pw-req-icon" aria-hidden="true">○</span><span class="pw-req-text">At least 1 number</span></div>
                                                    <div class="pw-req-item" data-test="symbol"><span class="pw-req-icon" aria-hidden="true">○</span><span class="pw-req-text">At least 1 symbol (e.g., !@#$%)</span></div>
                                                </div>
                                                <div class="pw-strength mt-2">
                                                    <div class="progress">
                                                        <div id="pwStrengthBar" class="progress-bar" role="progressbar" style="width:0%;background:#d9534f;" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"></div>
                                                    </div>
                                                    <div id="pwStrengthText" class="small mt-1 pw-strength-text">Strength: Very weak</div>
                                                </div>
                                                <div id="pwMatchMsg" class="small mt-1 pw-match-msg" style="display:none;">Passwords do not match.</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="portal-register-field">
                                            <label for="registerPasswordConfirmation" class="portal-register-label">Confirm Password *</label>
                                            <div class="portal-register-input-wrap">
                                                <input type="password" id="registerPasswordConfirmation" name="password_confirmation" placeholder="Re-enter your password" class="portal-register-input">
                                                <button type="button" class="portal-password-toggle portal-password-toggle-register" data-password-toggle data-target="registerPasswordConfirmation" aria-label="Show password" aria-pressed="false">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="portal-register-field">
                                            <label for="registerAddress" class="portal-register-label">Address</label>
                                            <textarea id="registerAddress" name="address" placeholder="Office or mailing address" rows="3" class="portal-register-input portal-register-textarea">{{ old('address') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                @if(config('services.recaptcha.site_key'))
                                    <div class="portal-register-field mt-3">
                                        <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
                                        @if ($errors->register->has('g-recaptcha-response'))
                                            <div class="text-danger small mt-2">{{ $errors->register->first('g-recaptcha-response') }}</div>
                                        @endif
                                    </div>

                                    <div class="portal-register-actions">
                                        @push('recaptcha_script')
                                            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                                        @endpush
                                @else
                                    <div class="portal-register-field mt-3">
                                        <div class="text-muted small">reCAPTCHA is not configured. Please contact the administrator.</div>
                                    </div>

                                    <div class="portal-register-actions">
                                @endif
                                    <button type="button" class="portal-login-secondary-btn" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">
                                        Back to login
                                    </button>
                                    <button type="submit" class="portal-login-primary-btn">Submit registration</button>
                                </div>
                            </form>
                        </div>
                    </section>
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

<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const registerModalEl = document.getElementById('registerModal');
        const registerModal = typeof bootstrap !== 'undefined' && registerModalEl
            ? bootstrap.Modal.getOrCreateInstance(registerModalEl)
            : null;
        const registerFormPanel = registerModalEl
            ? registerModalEl.querySelector('.portal-register-form-panel')
            : null;

        if (registerModalEl && registerFormPanel) {
            registerModalEl.addEventListener('show.bs.modal', function() {
                registerFormPanel.scrollTop = 0;
            });
        }

        function bindPasswordToggles(scope) {
            const toggleButtons = scope.querySelectorAll('[data-password-toggle]');

            toggleButtons.forEach(function(toggleButton) {
                const targetId = toggleButton.getAttribute('data-target');
                const targetInput = targetId ? document.getElementById(targetId) : null;
                const icon = toggleButton.querySelector('i');

                if (!targetInput || !icon) {
                    return;
                }

                toggleButton.addEventListener('click', function() {
                    const shouldShow = targetInput.type === 'password';
                    targetInput.type = shouldShow ? 'text' : 'password';
                    toggleButton.setAttribute('aria-label', shouldShow ? 'Hide password' : 'Show password');
                    toggleButton.setAttribute('aria-pressed', shouldShow ? 'true' : 'false');
                    icon.className = shouldShow ? 'bi bi-eye-slash' : 'bi bi-eye';
                    targetInput.focus({ preventScroll: true });
                    const valueLength = targetInput.value.length;
                    targetInput.setSelectionRange(valueLength, valueLength);
                });
            });
        }

        if (registerModalEl) {
            bindPasswordToggles(registerModalEl);
        }

        @if ($errors->register->any())
            if (registerModal) {
                registerModal.show();
            }
        @endif
        
        @if (session('success') === 'Registration Successful')
            let successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
        @endif

        const registerForm = document.getElementById('ajaxRegisterForm');
        if (registerForm) {
            const pwdInputEl = document.getElementById('registerPassword');
            const pwdConfirmEl = document.getElementById('registerPasswordConfirmation');
            const popEl = document.getElementById('pwRequirements');
            let autoFadeTimeout = null;
            let hideAnimationTimeout = null;

            function evaluatePassword(pwd) {
                return {
                    length: pwd.length >= 8,
                    upper: /[A-Z]/.test(pwd),
                    lower: /[a-z]/.test(pwd),
                    number: /[0-9]/.test(pwd),
                    symbol: /[^A-Za-z0-9]/.test(pwd),
                };
            }

            function updatePwdUI() {
                const pwd = pwdInputEl ? pwdInputEl.value : '';
                const conf = pwdConfirmEl ? pwdConfirmEl.value : '';
                const checks = evaluatePassword(pwd);
                const activePop = popEl;
                const reqItems = (activePop && activePop.querySelectorAll) ? activePop.querySelectorAll('.pw-req-item') : registerForm.querySelectorAll('.pw-req-item');
                reqItems.forEach(item => {
                    const test = item.getAttribute('data-test');
                    const met = !!checks[test];
                    item.classList.toggle('met', met);
                    const icon = item.querySelector('.pw-req-icon');
                    if (icon) icon.textContent = met ? '✓' : '○';
                });

                const score = Object.values(checks).filter(Boolean).length;
                const percent = Math.round((score / 5) * 100);
                const bar = (activePop && activePop.querySelector) ? activePop.querySelector('.progress-bar') : document.getElementById('pwStrengthBar');
                const text = (activePop && activePop.querySelector) ? activePop.querySelector('.pw-strength-text') : document.getElementById('pwStrengthText');
                if (bar) {
                    bar.style.width = percent + '%';
                    bar.setAttribute('aria-valuenow', percent);
                    if (score <= 2) { bar.style.background = '#d9534f'; if (text) text.textContent = 'Strength: Weak'; }
                    else if (score === 3) { bar.style.background = '#f0ad4e'; if (text) text.textContent = 'Strength: Fair'; }
                    else if (score >= 4) { bar.style.background = '#28a745'; if (text) text.textContent = 'Strength: Strong'; }
                }

                if (score >= 4) {
                    scheduleAutoFade();
                } else {
                    cancelAutoFade();
                }

                const matchMsg = (activePop && activePop.querySelector) ? activePop.querySelector('.pw-match-msg') : document.getElementById('pwMatchMsg');
                if (matchMsg) {
                    if (pwd && conf && pwd !== conf) { matchMsg.style.display = 'block'; }
                    else { matchMsg.style.display = 'none'; }
                }
            }

            function hidePopoverWithFade(active) {
                if (!active) return;
                active.classList.remove('show');
                if (hideAnimationTimeout) clearTimeout(hideAnimationTimeout);
                hideAnimationTimeout = setTimeout(() => {
                    try { active.style.display = 'none'; } catch(e){}
                    hideAnimationTimeout = null;
                }, 320);
            }

            function hidePopoverImmediate(active) {
                if (!active) return;
                if (hideAnimationTimeout) { clearTimeout(hideAnimationTimeout); hideAnimationTimeout = null; }
                active.classList.remove('show');
                try { active.style.display = 'none'; } catch(e){}
                if (autoFadeTimeout) { clearTimeout(autoFadeTimeout); autoFadeTimeout = null; }
            }

            function scheduleAutoFade() {
                if (autoFadeTimeout) clearTimeout(autoFadeTimeout);
                autoFadeTimeout = setTimeout(() => {
                    const active = popEl;
                    if (active) hidePopoverWithFade(active);
                    autoFadeTimeout = null;
                }, 2000);
            }

            function cancelAutoFade() {
                if (autoFadeTimeout) { clearTimeout(autoFadeTimeout); autoFadeTimeout = null; }
            }

            if (pwdInputEl) pwdInputEl.addEventListener('input', updatePwdUI);
            if (pwdConfirmEl) pwdConfirmEl.addEventListener('input', updatePwdUI);
            updatePwdUI();

            if (popEl) {
                popEl.style.display = 'none';
                let inputHovered = false;
                let popHovered = false;
                let hideTimeout = null;

                function showPopover() {
                    const active = popEl;
                    if (!active) return;
                    cancelAutoFade();
                    if (hideAnimationTimeout) { clearTimeout(hideAnimationTimeout); hideAnimationTimeout = null; }
                    active.style.display = 'block';
                    requestAnimationFrame(() => active.classList.add('show'));
                }

                function hideIfNotActive() {
                    if (hideTimeout) clearTimeout(hideTimeout);
                    hideTimeout = setTimeout(() => {
                        if (!pwdInputEl) return;
                        if (document.activeElement === pwdInputEl || inputHovered || popHovered) return;
                        hidePopoverImmediate(popEl);
                    }, 150);
                }

                if (pwdInputEl) {
                    pwdInputEl.addEventListener('focus', showPopover);
                    pwdInputEl.addEventListener('input', showPopover);
                    pwdInputEl.addEventListener('blur', hideIfNotActive);
                    pwdInputEl.addEventListener('mouseenter', () => { inputHovered = true; showPopover(); });
                    pwdInputEl.addEventListener('mouseleave', () => { inputHovered = false; hideIfNotActive(); });
                }

                popEl.addEventListener('mouseenter', () => { popHovered = true; });
                popEl.addEventListener('mouseleave', () => { popHovered = false; hideIfNotActive(); });
            }

            registerForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const existingAlert = registerForm.closest('.portal-register-form-wrap').querySelector('.alert-danger');
                if (existingAlert) {
                    existingAlert.remove();
                }

                let errorContainer = document.getElementById('registerErrorMsg');
                if (errorContainer) {
                    errorContainer.style.display = 'none';
                    errorContainer.textContent = '';
                }

                

                const pwdVal = pwdInputEl ? pwdInputEl.value : '';
                const pwdConfirmVal = pwdConfirmEl ? pwdConfirmEl.value : '';
                const checks = evaluatePassword(pwdVal);
                const allGood = Object.values(checks).every(Boolean);
                if (!allGood) {
                    if (errorContainer) {
                        const escHtml = s => String(s == null ? '' : s)
                            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
                        errorContainer.textContent = 'Password must be at least 8 characters and include uppercase, lowercase, number, and symbol.';
                        errorContainer.style.display = 'block';
                    }
                    return;
                }
                if (pwdVal !== pwdConfirmVal) {
                    if (errorContainer) {
                        const escHtml = s => String(s == null ? '' : s)
                            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
                        errorContainer.textContent = 'Passwords do not match.';
                        errorContainer.style.display = 'block';
                    }
                    return;
                }

                const formData = new FormData(registerForm);

                const submitBtn = registerForm.querySelector('button[type="submit"]');
                let originalBtnHtml = '';
                if (submitBtn) {
                    originalBtnHtml = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = sanitizeHtml('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Registering...');
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
                            const escHtml = s => String(s == null ? '' : s)
                                .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
                            let html;
                            const arr = Array.from(messages);
                            if (arr.length > 1) {
                                html = escHtml(arr[0]) + '<ul style="margin:0;padding-left:18px;font-size:0.875rem;">';
                                for (let i = 1; i < arr.length; i++) {
                                    html += '<li style="word-wrap: break-word; white-space: normal;">' + escHtml(arr[i]) + '</li>';
                                }
                                html += '</ul>';
                            } else {
                                html = escHtml(arr[0]);
                            }
                            errorContainer.innerHTML = sanitizeHtml(html);
                            errorContainer.style.display = 'block';
                        }
                    }
                })
                .catch(err => {
                    console.error('AJAX register fetch error:', err);
                    if (errorContainer) {
                        const escHtml = s => String(s == null ? '' : s)
                            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
                        errorContainer.innerHTML = sanitizeHtml(escHtml('An error occurred. Please try again.'));
                        errorContainer.style.display = 'block';
                    }
                })
                .finally(() => {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = sanitizeHtml(originalBtnHtml || 'Register');
                        }
                });
            });


        }
    });
</script>