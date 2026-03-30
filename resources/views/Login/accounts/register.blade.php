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
                        .pw-requirements {
                            margin-top: 6px;
                            background: rgba(20,20,20,0.94);
                            padding: 12px;
                            border-radius: 8px;
                            display: none; 
                            position: fixed;
                            z-index: 3050;
                            width: 320px;
                            max-width: 90vw;
                            box-shadow: 0 6px 18px rgba(0,0,0,0.45);
                            opacity: 0;
                            transition: opacity 0.28s ease;
                        }
                        .pw-requirements.show {
                            opacity: 1;
                        }
                        .pw-popover-arrow {
                            position: absolute;
                            width: 10px;
                            height: 10px;
                            background: rgba(255,255,255,0.03);
                            transform: rotate(45deg);
                            left: 16px;
                            top: -6px;
                            box-shadow: 0 6px 18px rgba(0,0,0,0.12);
                        }
                        .pw-req-list {
                            display: flex;
                            flex-direction: column;
                        }
                        .pw-req-item {
                            display: flex;
                            align-items: center;
                            color: #bbb;
                            margin: 4px 0;
                            font-size: 0.85rem;
                        }
                        .pw-req-icon {
                            width: 20px;
                            text-align: center;
                            margin-right: 8px;
                            font-weight: 700;
                            color: #bbb;
                        }
                        .pw-req-item.met { color: #b8ffb8; }
                        .pw-req-item.met .pw-req-icon { color: #b8ffb8; }
                        .pw-strength .progress { height: 8px; border-radius: 8px; overflow: hidden; }
                        .pw-strength .progress-bar { transition: width 0.22s ease, background 0.22s ease; }
                    </style>
                    
                    <form method="POST" action="{{ route('register') }}" id="ajaxRegisterForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-compact">
                                    <label for="registerFirstName" class="register-form-label">First Name *</label>
                                    <input type="text" id="registerFirstName" name="firstname" placeholder="First Name" value="{{ old('firstname') }}" class="register-form-input">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-compact">
                                    <label for="registerMiddleName" class="register-form-label">Middle Name *</label>
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
                                    <label for="registerEmail" class="register-form-label">Email Address *</label>
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
                                        <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Prefer not to say</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-compact">
                                            <label for="registerPassword" class="register-form-label">Password</label>
                                            <input type="password" id="registerPassword" name="password" placeholder="Password" class="register-form-input" aria-describedby="pwRequirements">
                                            <div id="pwRequirements" class="pw-requirements" aria-live="polite">
                                                <div class="pw-req-header" style="font-size:0.85rem;color:#ddd;margin-bottom:6px;">Password must include</div>
                                                <div class="pw-req-list">
                                                    <div class="pw-req-item" data-test="length"><span class="pw-req-icon" aria-hidden="true">○</span><span class="pw-req-text">At least 8 characters</span></div>
                                                    <div class="pw-req-item" data-test="upper"><span class="pw-req-icon" aria-hidden="true">○</span><span class="pw-req-text">At least 1 uppercase letter</span></div>
                                                    <div class="pw-req-item" data-test="lower"><span class="pw-req-icon" aria-hidden="true">○</span><span class="pw-req-text">At least 1 lowercase letter</span></div>
                                                    <div class="pw-req-item" data-test="number"><span class="pw-req-icon" aria-hidden="true">○</span><span class="pw-req-text">At least 1 number</span></div>
                                                    <div class="pw-req-item" data-test="symbol"><span class="pw-req-icon" aria-hidden="true">○</span><span class="pw-req-text">At least 1 symbol (e.g., !@#$%)</span></div>
                                                </div>
                                                <div class="pw-strength mt-2">
                                                    <div class="progress" style="background:rgba(255,255,255,0.12);">
                                                        <div id="pwStrengthBar" class="progress-bar" role="progressbar" style="width:0%;background:#d9534f;" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"></div>
                                                    </div>
                                                    <div id="pwStrengthText" class="small text-muted mt-1 pw-strength-text" style="color:#ddd;">Strength: Very weak</div>
                                                </div>
                                                <div id="pwMatchMsg" class="small mt-1 pw-match-msg" style="display:none;color:#ffb3b3;">Passwords do not match.</div>
                                            </div>
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
    document.addEventListener('DOMContentLoaded', function() {
        @if ($errors->register->any())
            let registerModal = new bootstrap.Modal(document.getElementById('registerModal'));
            registerModal.show();
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
            let popClone = null;
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
                const activePop = popClone || popEl;
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
                    const active = popClone || popEl;
                    if (active) hidePopoverWithFade(active);
                    autoFadeTimeout = null;
                }, 2000);
            }

            function cancelAutoFade() {
                if (autoFadeTimeout) { clearTimeout(autoFadeTimeout); autoFadeTimeout = null; }
            }

            if (pwdInputEl) pwdInputEl.addEventListener('input', updatePwdUI);
            if (pwdConfirmEl) pwdConfirmEl.addEventListener('input', updatePwdUI);

            if (popEl) {
                try {
                    popClone = popEl.cloneNode(true);
                    popClone.id = 'pwRequirements_clone';
                    const innerText = popClone.querySelector('#pwStrengthText');
                    if (innerText) innerText.classList.add('pw-strength-text');
                    const innerMatch = popClone.querySelector('#pwMatchMsg');
                    if (innerMatch) innerMatch.classList.add('pw-match-msg');
                    const clonedBar = popClone.querySelector('#pwStrengthBar');
                    if (clonedBar) clonedBar.classList.add('progress-bar');
                    popClone.style.display = 'none';
                    popClone.style.position = 'fixed';
                    popClone.style.zIndex = '9999';
                    document.body.appendChild(popClone);
                } catch (e) {
                    console.debug('pw pop clone failed', e);
                    popClone = null;
                }
            }
            updatePwdUI();

            if (popEl) {
                popEl.style.display = 'none';
                let inputHovered = false;
                let popHovered = false;
                let hideTimeout = null;

                function getActivePop() { return popClone || popEl; }

                function positionPopover() {
                    const active = getActivePop();
                    if (!pwdInputEl || !active) return;
                    const wasHidden = active.style.display === 'none';
                    if (wasHidden) {
                        active.style.visibility = 'hidden';
                        active.style.display = 'block';
                    }
                    const rect = pwdInputEl.getBoundingClientRect();
                    const popRect = active.getBoundingClientRect();
                    let left = rect.left;
                    let top = rect.bottom + 8;
                    if (top + popRect.height > window.innerHeight) {
                        top = rect.top - popRect.height - 8;
                    }
                    if (left + popRect.width > window.innerWidth) {
                        left = window.innerWidth - popRect.width - 8;
                    }
                    if (left < 8) left = 8;
                    if (top < 8) top = 8;
                    active.style.left = left + 'px';
                    active.style.top = top + 'px';
                    if (wasHidden) {
                        active.style.display = 'none';
                        active.style.visibility = '';
                    }
                }

                function showPopover() {
                    const active = getActivePop();
                    if (!active) return;
                    try {
                        if (active.parentElement !== document.body) {
                            document.body.appendChild(active);
                            active.style.position = 'fixed';
                        }
                    } catch (e) {
                    }
                    cancelAutoFade();
                    if (hideAnimationTimeout) { clearTimeout(hideAnimationTimeout); hideAnimationTimeout = null; }
                    active.style.display = 'block';
                    active.style.zIndex = 9999;
                    positionPopover();
                    requestAnimationFrame(() => active.classList.add('show'));
                    if (window.console && console.debug) console.debug('pw popover shown');
                }

                function hideIfNotActive() {
                    if (hideTimeout) clearTimeout(hideTimeout);
                    hideTimeout = setTimeout(() => {
                        if (!pwdInputEl) return;
                        if (document.activeElement === pwdInputEl || inputHovered || popHovered) return;
                        const active = getActivePop();
                        hidePopoverImmediate(active);
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
                if (popClone) {
                    popClone.addEventListener('mouseenter', () => { popHovered = true; });
                    popClone.addEventListener('mouseleave', () => { popHovered = false; hideIfNotActive(); });
                }

                window.addEventListener('resize', positionPopover);
                window.addEventListener('scroll', () => { const active = getActivePop(); if (active && active.style.display === 'block') positionPopover(); }, true);
            }

            registerForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const existingAlert = registerForm.closest('.modal-login-content').querySelector('.alert-danger');
                if (existingAlert) {
                    existingAlert.remove();
                }

                let errorContainer = document.getElementById('registerErrorMsg');
                if (errorContainer) {
                    errorContainer.style.display = 'none';
                    errorContainer.innerHTML = '';
                }

                

                const pwdVal = pwdInputEl ? pwdInputEl.value : '';
                const pwdConfirmVal = pwdConfirmEl ? pwdConfirmEl.value : '';
                const checks = evaluatePassword(pwdVal);
                const allGood = Object.values(checks).every(Boolean);
                if (!allGood) {
                    if (errorContainer) {
                        errorContainer.innerHTML = 'Password must be at least 8 characters and include uppercase, lowercase, number, and symbol.';
                        errorContainer.style.display = 'block';
                    }
                    return;
                }
                if (pwdVal !== pwdConfirmVal) {
                    if (errorContainer) {
                        errorContainer.innerHTML = 'Passwords do not match.';
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