@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-7 col-lg-5">
            <div class="card shadow-sm custom-otp-card" style="background:transparent;border:none">
                <div class="card-body p-4" style="background:linear-gradient(180deg,rgba(255,255,255,0.98),#ffffff);border-radius:12px;">
                    <style>
                        /* OTP card and input styling (local to this view) */
                        .custom-otp-card { background: transparent; }
                        .custom-otp-card .card-body { box-shadow: 0 12px 30px rgba(18,38,63,0.08); }
                        .otp-digit { width:56px; height:56px; border-radius:8px; border:1px solid #e6eef2; background:#fff; box-shadow:inset 0 1px 0 rgba(255,255,255,0.6); font-size:20px; font-weight:600; }
                        .otp-digit:focus { outline: none; box-shadow:0 0 0 3px rgba(99,163,255,0.12); border-color:#63a3ff; }
                        #otpBoxes { gap:12px; }
                        .btn-resend { min-width:130px; }
                        .btn.disabled, .btn[aria-disabled="true"] { opacity:0.62; pointer-events:none; }
                        .verification-legend { color:#6b7280; font-size:14px; }
                        @media (max-width:576px) { .otp-digit { width:44px; height:44px; font-size:18px; } }
                        /* Error box and invalid input styles */
                        .otp-error-box { background:#fdecea; border:1px solid #f5c6cb; color:#842029; padding:12px 14px; border-radius:8px; }
                        .otp-error-box ul { margin:0; padding-left:18px; }
                        .otp-digit.invalid { border-color:#f44336; box-shadow:0 0 0 4px rgba(244,67,54,0.08); }
                        .otp-help-text { color:#6b7280; font-size:13px; }

                        /* Primary OTP button: large pill with gradient */
                        .btn-otp-primary { display:block; width:100%; padding:12px 18px; border-radius:28px; border:none; color:#fff; font-weight:700; font-size:16px; background:linear-gradient(90deg,#0b5ed7 0%,#2b8cff 50%,#3b82f6 100%); box-shadow:0 8px 20px rgba(11,94,215,0.18); }
                        .btn-otp-primary:active { transform:translateY(1px); }
                        .btn-otp-cancel { display:block; width:100%; padding:10px 16px; border-radius:22px; border:1px solid #d1d5db; background:#fff; color:#374151; text-align:center; }
                        .btn-otp-cancel:hover { background:#fafafa; }
                    </style>
                    <h4 class="card-title mb-3">Two-step verification</h4>

                    @if ($errors->any())
                        <div class="otp-error-box mb-3" role="alert" aria-live="polite">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="alert alert-info">{{ session('status') }}</div>
                    @endif

                    <p class="verification-legend">A verification code has been sent to <strong>{{ $masked_email ?? 'your email' }}</strong>. Enter it below to complete sign in.</p>

                    <form method="POST" action="{{ route('otp.verify') }}" id="otpForm" class="mb-2" autocomplete="off">
                        @csrf
                        <input type="hidden" name="otp_code" id="otp_code_hidden" value="{{ old('otp_code') }}">

                        <div class="mb-3 text-center">
                            <label class="form-label d-block mb-2">Verification code</label>
                            <div id="otpBoxes" class="d-flex justify-content-center" style="column-gap:12px;">
                                <input inputmode="numeric" pattern="\d*" maxlength="1" class="otp-digit text-center" id="otp_digit_1" />
                                <input inputmode="numeric" pattern="\d*" maxlength="1" class="otp-digit text-center" id="otp_digit_2" />
                                <input inputmode="numeric" pattern="\d*" maxlength="1" class="otp-digit text-center" id="otp_digit_3" />
                                <input inputmode="numeric" pattern="\d*" maxlength="1" class="otp-digit text-center" id="otp_digit_4" />
                                <input inputmode="numeric" pattern="\d*" maxlength="1" class="otp-digit text-center" id="otp_digit_5" />
                                <input inputmode="numeric" pattern="\d*" maxlength="1" class="otp-digit text-center" id="otp_digit_6" />
                            </div>
                            <div class="form-text text-center mt-2 otp-help-text">Enter the 6-digit code you received by email.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-otp-primary" id="verifyBtn">Verify</button>
                            <a href="{{ route('login') }}" class="btn btn-otp-cancel">Cancel</a>
                            <div class="text-end mt-1">
                                <button type="button" id="resendBtn" class="btn btn-outline-secondary btn-resend" aria-disabled="false">Resend code</button>
                            </div>
                        </div>
                    </form>

                    <div class="mt-3 text-center text-muted small">
                        <span id="countdown">Code expires in —</span>
                    </div>
                </div>
            </div>
            <div class="text-center mt-3 text-muted small">Questions? Contact IT support.</div>
        </div>
    </div>
    <form id="resendForm" method="POST" action="{{ route('otp.resend') }}">@csrf</form>
</div>

<script>
    (function(){
        const resendBtn = document.getElementById('resendBtn');
        const resendForm = document.getElementById('resendForm');
        const countdownEl = document.getElementById('countdown');
        const otpDigits = Array.from(document.querySelectorAll('.otp-digit'));
        const otpHidden = document.getElementById('otp_code_hidden');
        const otpForm = document.getElementById('otpForm');

        // Parse server-provided expiry if available
        let expiresAt = @json($otp_expires_at ?? null);
        let cooldownSeconds = 30; // short cooldown before allowing resend
        let resendEnabled = true;

        function startCooldown(seconds) {
            resendEnabled = false;
            resendBtn.classList.add('disabled');
            resendBtn.setAttribute('aria-disabled','true');
            let s = seconds;
            const tick = () => {
                if (s <= 0) {
                    resendEnabled = true;
                    resendBtn.classList.remove('disabled');
                    resendBtn.removeAttribute('aria-disabled');
                    resendBtn.textContent = 'Resend code';
                } else {
                    resendBtn.textContent = 'Resend in ' + s + 's';
                    s -= 1;
                    setTimeout(tick, 1000);
                }
            };
            tick();
        }

        resendBtn.addEventListener('click', function(){
            if (!resendEnabled) return;
            // submit the hidden form
            resendForm.submit();
            startCooldown(cooldownSeconds);
        });

        // OTP digit handling: auto-advance, backspace, paste
        otpDigits.forEach((el, idx) => {
            el.addEventListener('input', (e) => {
                const v = (e.target.value || '').replace(/\D/g, '');
                e.target.value = v.slice(0,1);
                if (v && idx < otpDigits.length - 1) {
                    otpDigits[idx+1].focus();
                }
            });

            el.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && idx > 0) {
                    otpDigits[idx-1].focus();
                }
            });

            el.addEventListener('paste', (e) => {
                e.preventDefault();
                const text = (e.clipboardData || window.clipboardData).getData('text') || '';
                const digits = text.replace(/\D/g, '').slice(0,6).split('');
                for (let i=0;i<digits.length && i<otpDigits.length;i++) {
                    otpDigits[i].value = digits[i];
                }
                if (digits.length >= otpDigits.length) {
                    otpDigits[otpDigits.length-1].focus();
                } else {
                    otpDigits[digits.length].focus();
                }
            });
        });

        // On submit, combine digits into hidden input
        otpForm.addEventListener('submit', function(e){
            const code = otpDigits.map(i => (i.value || '')).join('');
            if (code.length !== otpDigits.length) {
                e.preventDefault();
                // simple inline feedback
                countdownEl.textContent = 'Please enter the full 6-digit code.';
                otpDigits.find(i => !i.value)?.focus();
                return false;
            }
            otpHidden.value = code;
        });

        // Start countdown to OTP expiry
        function startExpiryCountdown(expiry) {
            if (!expiry) return;
            let expiryDate = new Date(expiry);
            function update() {
                const now = new Date();
                const diff = Math.max(0, Math.floor((expiryDate - now) / 1000));
                const m = Math.floor(diff / 60);
                const s = diff % 60;
                countdownEl.textContent = 'Code expires in ' + (m>0? m + 'm ' : '') + String(s).padStart(2,'0') + 's';
                if (diff <= 0) {
                    countdownEl.textContent = 'Code has expired. Please resend to get a new one.';
                    startCooldown(cooldownSeconds);
                } else {
                    setTimeout(update, 1000);
                }
            }
            update();
        }

        // initialize
        startCooldown(cooldownSeconds);
        if (expiresAt) {
            // ensure expiry is in ISO format
            startExpiryCountdown(expiresAt);
        }

        // If server-side errors exist, mark inputs invalid and focus
        const hasServerErrors = @json($errors->any());
        if (hasServerErrors) {
            otpDigits.forEach(d => d.classList.add('invalid'));
            // focus first empty digit or first digit
            const firstEmpty = otpDigits.find(i => !i.value);
            (firstEmpty || otpDigits[0]).focus();
        }
    })();
</script>

@endsection
