<div class="modal fade" id="otpModal" tabindex="-1" aria-labelledby="otpModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered otp-modal-dialog-wide">
    <div class="modal-content otp-modal-card">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="otpModalLabel">Two-step verification</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <p class="verification-legend" id="otpInstructionText"><span id="otpInstructionPrefix">No verification code has been sent to</span> <strong id="otpMaskedEmail">your email</strong> <span id="otpInstructionSuffix">yet. Confirm the request first, then complete the CAPTCHA to send one.</span></p>

        <div class="otp-send-panel mb-3">
          <button type="button" class="btn btn-otp-primary w-100" id="otpOpenSendPopoverBtn">Request verification code</button>
          <div class="otp-send-popover" id="otpSendPopover" hidden>
            <p class="mb-2"><span id="otpSendPromptPrefix">Send a one-time password to</span> <strong id="otpConfirmMaskedEmail">your email</strong><span id="otpSendPromptSuffix">?</span></p>
            <p class="text-muted small mb-3">You can request a code up to 3 times. Additional requests will pause OTP access for 5 minutes.</p>
            @if(config('services.recaptcha.site_key'))
              <div class="otp-send-captcha-wrap">
                <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
              </div>
              <div class="text-danger small mt-2" id="otpSendCaptchaError" style="display:none;"></div>
            @else
              <div class="text-muted small mb-3">CAPTCHA is not configured in this environment.</div>
            @endif
            <div class="d-flex gap-2 mt-3">
              <button type="button" class="btn btn-otp-primary flex-fill" id="otpConfirmSendBtn">Confirm request</button>
              <button type="button" class="btn btn-otp-cancel flex-fill" id="otpCancelSendBtn">Cancel</button>
            </div>
          </div>
        </div>

        <form id="otpModalForm" method="POST" action="{{ route('otp.verify') }}" autocomplete="off">
          @csrf
          <input type="hidden" name="otp_code" id="otp_modal_code_hidden">
          <div class="mb-3 text-center">
            <label class="form-label d-block mb-2">Verification code</label>
            <div id="otpModalBoxes" class="d-flex justify-content-center" style="column-gap:12px;">
              <input inputmode="numeric" pattern="\d*" maxlength="1" class="otp-digit text-center" id="otp_modal_digit_1" />
              <input inputmode="numeric" pattern="\d*" maxlength="1" class="otp-digit text-center" id="otp_modal_digit_2" />
              <input inputmode="numeric" pattern="\d*" maxlength="1" class="otp-digit text-center" id="otp_modal_digit_3" />
              <input inputmode="numeric" pattern="\d*" maxlength="1" class="otp-digit text-center" id="otp_modal_digit_4" />
              <input inputmode="numeric" pattern="\d*" maxlength="1" class="otp-digit text-center" id="otp_modal_digit_5" />
              <input inputmode="numeric" pattern="\d*" maxlength="1" class="otp-digit text-center" id="otp_modal_digit_6" />
            </div>
            <div class="form-text text-center mt-2 otp-help-text">Enter the 6-digit code you received by email.</div>
          </div>

          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-otp-primary" id="otpVerifyBtn">Verify</button>
            <button type="button" class="btn btn-otp-cancel" data-bs-dismiss="modal">Cancel</button>
            <div class="text-center mt-2" id="otpResendWrap" style="display:none;">
              <small class="text-muted">Need another code? <a href="#" id="otpResendBtn" class="otp-resend-link">Request another OTP</a></small>
            </div>
          </div>
        </form>

        <div class="mt-3 text-center text-muted small">
          <span id="otpModalCountdown">Code expires in —</span>
        </div>

        <div class="mt-2 text-center text-muted small" id="otpSendMeta"></div>

        <div id="otpModalError" class="otp-error-box mt-3" role="alert" aria-live="polite" style="display:none;"></div>
      </div>
    </div>
  </div>

</div>

<div class="modal fade" id="otpRestrictionModal" tabindex="-1" aria-labelledby="otpRestrictionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content otp-restriction-card">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title" id="otpRestrictionModalLabel">OTP temporarily restricted</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-2">
        <p class="mb-2" id="otpRestrictionMessage">You have reached the OTP request limit. Please wait 5 minutes before trying again.</p>
        <div class="otp-restriction-timer" id="otpRestrictionCountdown">Try again in 05:00.</div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-otp-primary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<style>
  /* Make modal card opaque and visually separate from page */
  #otpModal .modal-content.otp-modal-card { background: #ffffff; border-radius: 8px; box-shadow: 0 8px 24px rgba(4,15,36,0.12); overflow: visible; }
  #otpModal .modal-dialog { max-width: 520px; }
  #otpModal .otp-digit { width:56px; height:44px; border-radius:6px; border:1px solid rgba(16,24,40,0.08); background:#f8fbff; font-size:18px; padding:6px; }
  #otpModal .btn-otp-primary { background:#0a66ff; border-color:#0a66ff; color:#fff; }
  #otpModal .btn-otp-cancel { background:#f3f4f6; color:#111827; }
  #otpModal .otp-error-box { background:#fff5f5; border:1px solid #f8d7da; color:#842029; padding:10px; border-radius:6px; display:none; }
  #otpModal .otp-send-panel { position: relative; }
  #otpModal .otp-send-popover { position: relative; inset: auto; margin-top: 12px; z-index: 1085; padding: 16px; border-radius: 10px; background: #ffffff; border: 1px solid rgba(15, 23, 42, 0.12); box-shadow: 0 18px 50px rgba(15, 23, 42, 0.18); }
  #otpModal .otp-send-popover::before { display: none; }
  #otpModal .otp-send-captcha-wrap { display:flex; justify-content:center; align-items:center; min-height: 78px; overflow: visible; }
  #otpModal .otp-send-captcha-wrap iframe { position: relative; z-index: 1086; }
  #otpModal .otp-send-popover[hidden] { display: none !important; }

  /* resend link styling */
  #otpModal .otp-resend-link { color: #0a66ff; text-decoration:underline; cursor:pointer; }
  #otpModal .otp-resend-link:hover { color: #064edc; }
  #otpModal .otp-resend-link.disabled, #otpModal .otp-resend-link[aria-disabled='true'] { pointer-events:none; opacity:0.5; cursor:default; text-decoration:none; }

  #otpRestrictionModal .otp-restriction-card { border-radius: 12px; box-shadow: 0 18px 40px rgba(15, 23, 42, 0.2); }
  #otpRestrictionModal .otp-restriction-timer { display:flex; align-items:center; justify-content:center; min-height:48px; border-radius:10px; background:#f8fafc; color:#0f172a; font-weight:600; }

  /* Blur the page behind the modal via backdrop element */
  .modal-backdrop.show {
    /* solid, dark backdrop like the login screen */
    backdrop-filter: none;
    -webkit-backdrop-filter: none;
    background-color: rgba(2,8,20,0.94) !important;
    /* keep backdrop under Bootstrap modals (default modal z-index ~1055) */
    z-index: 1040 !important;
  }

  /* Ensure body doesn't show through modal content */
  #otpModal .modal-body { background: transparent; }

  /* ensure the OTP modal itself sits above the backdrop so it's clickable */
  #otpModal.modal {
    z-index: 12050 !important;
  }

  #otpModal .modal-dialog,
  #otpModal .modal-content,
  #otpModal .modal-body {
    position: relative;
    z-index: 12051;
    overflow: visible;
  }

  #otpModal + .modal-backdrop,
  .modal-backdrop.show {
    z-index: 12040 !important;
  }

  /* When OTP modal is active, heavily dim/blur the rest of the page.
     Exclude any other `.modal` elements so other Bootstrap modals
     remain interactive when opened while the OTP modal class exists. */
  .otp-modal-open > *:not(#otpModal):not(.modal-backdrop):not(.modal) {
    /* dim to near-black like the login overlay, but keep layout sharp */
    filter: none !important;
    opacity: 0.10 !important;
    transition: opacity 160ms linear !important;
    pointer-events: none !important;
    user-select: none !important;
    -webkit-user-select: none !important;
  }

  @media (max-width: 576px) {
    #otpModal .modal-dialog {
      max-width: calc(100vw - 24px);
      margin: 0.75rem auto;
    }

    #otpModal .modal-body {
      padding: 1rem !important;
    }

    #otpModal .otp-send-popover {
      padding: 12px;
    }

    #otpModal .otp-send-captcha-wrap {
      justify-content: flex-start;
      overflow-x: auto;
      padding-bottom: 4px;
    }
  }
</style>

<script>
  (function(){
    if (!document.getElementById('otpModal')) return;
    const otpModalEl = document.getElementById('otpModal');
    const otpModal = typeof bootstrap !== 'undefined' ? bootstrap.Modal.getOrCreateInstance(otpModalEl) : null;
    const otpRestrictionModalEl = document.getElementById('otpRestrictionModal');
    const otpRestrictionModal = typeof bootstrap !== 'undefined' && otpRestrictionModalEl
      ? bootstrap.Modal.getOrCreateInstance(otpRestrictionModalEl)
      : null;
    const otpDigits = Array.from(otpModalEl.querySelectorAll('.otp-digit'));
    const otpHidden = document.getElementById('otp_modal_code_hidden');
    const otpForm = document.getElementById('otpModalForm');
    const verifyBtn = document.getElementById('otpVerifyBtn');
    const openSendPopoverBtn = document.getElementById('otpOpenSendPopoverBtn');
    const sendPopover = document.getElementById('otpSendPopover');
    const sendPromptPrefixEl = document.getElementById('otpSendPromptPrefix');
    const sendPromptSuffixEl = document.getElementById('otpSendPromptSuffix');
    const confirmSendBtn = document.getElementById('otpConfirmSendBtn');
    const cancelSendBtn = document.getElementById('otpCancelSendBtn');
    const resendBtn = document.getElementById('otpResendBtn');
    const resendWrap = document.getElementById('otpResendWrap');
    const countdownEl = document.getElementById('otpModalCountdown');
    const instructionPrefixEl = document.getElementById('otpInstructionPrefix');
    const instructionSuffixEl = document.getElementById('otpInstructionSuffix');
    const maskedEmailEl = document.getElementById('otpMaskedEmail');
    const confirmMaskedEmailEl = document.getElementById('otpConfirmMaskedEmail');
    const sendMetaEl = document.getElementById('otpSendMeta');
    const errorBox = document.getElementById('otpModalError');
    const restrictionMessageEl = document.getElementById('otpRestrictionMessage');
    const restrictionCountdownEl = document.getElementById('otpRestrictionCountdown');
    const sendCaptchaError = document.getElementById('otpSendCaptchaError');
    let _autoSubmitTimer = null;
    let _autoSubmitting = false;
    let expiryTimer = null;
    let restrictionTimer = null;
    let sendRequestActive = false;
    let sendPopoverMode = 'initial';
    let restrictionModalShownForLock = null;
    let otpState = {
      otp_sent: false,
      send_count: 0,
      send_limit: 3,
      remaining_sends: 3,
      locked_until: null,
      otp_expires_at: null,
      masked_email: null,
    };

    function getSendCaptchaToken() {
      const field = sendPopover ? sendPopover.querySelector('textarea[name="g-recaptcha-response"]') : null;
      return field ? String(field.value || '').trim() : '';
    }

    function resetSendCaptcha() {
      @if(config('services.recaptcha.site_key'))
      try {
        if (window.grecaptcha && typeof window.grecaptcha.reset === 'function') {
          window.grecaptcha.reset();
        }
      } catch (e) {
      }
      const field = sendPopover ? sendPopover.querySelector('textarea[name="g-recaptcha-response"]') : null;
      if (field) {
        field.value = '';
      }
      @endif
    }

    function hideSendPopover() {
      if (sendPopover) {
        sendPopover.hidden = true;
      }
      hideCaptchaError();
    }

    function showSendPopover(mode) {
      if (!sendPopover || !openSendPopoverBtn || openSendPopoverBtn.disabled) {
        return;
      }

      sendPopoverMode = mode || 'initial';
      if (sendPromptPrefixEl && sendPromptSuffixEl && confirmSendBtn) {
        if (sendPopoverMode === 'resend') {
          sendPromptPrefixEl.textContent = 'Request another one-time password for';
          sendPromptSuffixEl.textContent = '?';
          confirmSendBtn.textContent = 'Confirm resend';
        } else {
          sendPromptPrefixEl.textContent = 'Send a one-time password to';
          sendPromptSuffixEl.textContent = '?';
          confirmSendBtn.textContent = 'Confirm request';
        }
      }

      sendPopover.hidden = false;
      resetSendCaptcha();
      if (confirmSendBtn) {
        confirmSendBtn.focus();
      }
    }

    function showCaptchaError(message) {
      if (!sendCaptchaError) {
        showError(message);
        return;
      }

      sendCaptchaError.textContent = message || '';
      sendCaptchaError.style.display = message ? 'block' : 'none';
    }

    function hideCaptchaError() {
      if (!sendCaptchaError) {
        return;
      }

      sendCaptchaError.textContent = '';
      sendCaptchaError.style.display = 'none';
    }

    function clearOtpDigits() {
      otpDigits.forEach((input) => {
        input.value = '';
      });
      otpHidden.value = '';
    }

    function isLocked() {
      if (!otpState.locked_until) {
        return false;
      }

      const lockedUntil = new Date(otpState.locked_until).getTime();
      return !Number.isNaN(lockedUntil) && lockedUntil > Date.now();
    }

    function setOtpInputsDisabled(disabled) {
      otpDigits.forEach((input) => {
        input.disabled = disabled;
      });
      if (verifyBtn) {
        verifyBtn.disabled = disabled;
      }
    }

    function renderSendMeta() {
      if (!sendMetaEl) {
        return;
      }

      if (isLocked()) {
        sendMetaEl.textContent = 'OTP requests are paused. Wait for the restriction timer to expire.';
        return;
      }

      const limit = Number(otpState.send_limit || 3);
      const count = Number(otpState.send_count || 0);
      const remaining = Math.max(0, Number(otpState.remaining_sends ?? (limit - count)));
      sendMetaEl.textContent = 'Requests used: ' + count + ' / ' + limit + '. Remaining before lock: ' + remaining + '.';
    }

    function updateInstruction() {
      if (!instructionPrefixEl || !instructionSuffixEl) {
        return;
      }

      if (isLocked()) {
        instructionPrefixEl.textContent = 'OTP input for';
        instructionSuffixEl.textContent = 'is temporarily restricted. Wait for the timer to expire before requesting another code.';
        return;
      }

      if (otpState.otp_sent) {
        instructionPrefixEl.textContent = 'A verification code was sent to';
        instructionSuffixEl.textContent = 'Enter it below to complete sign in.';
        return;
      }

      instructionPrefixEl.textContent = 'No verification code has been sent to';
      instructionSuffixEl.textContent = 'yet. Confirm the request first, then complete the CAPTCHA to send one.';
    }

    function startExpiry(iso) {
      clearInterval(expiryTimer);

      if (!iso || !otpState.otp_sent || isLocked()) {
        countdownEl.textContent = otpState.otp_sent ? 'Code expires in —' : 'No verification code has been sent yet.';
        return;
      }

      const expiryDate = new Date(iso);
      if (Number.isNaN(expiryDate.getTime())) {
        countdownEl.textContent = 'Code expires in —';
        return;
      }

      const tick = function() {
        const diff = Math.max(0, Math.floor((expiryDate.getTime() - Date.now()) / 1000));
        const minutes = Math.floor(diff / 60);
        const seconds = diff % 60;
        countdownEl.textContent = 'Code expires in ' + (minutes > 0 ? minutes + 'm ' : '') + String(seconds).padStart(2, '0') + 's';

        if (diff <= 0) {
          clearInterval(expiryTimer);
          otpState.otp_sent = false;
          otpState.otp_expires_at = null;
          setOtpInputsDisabled(true);
          clearOtpDigits();
          countdownEl.textContent = 'No active verification code. Send a new OTP to continue.';
          updateInstruction();
          if (resendWrap) {
            resendWrap.style.display = 'none';
          }
          showError('The verification code has expired. Confirm and send a new code to continue.');
        }
      };

      tick();
      expiryTimer = setInterval(tick, 1000);
    }

    function startRestrictionCountdown(iso) {
      clearInterval(restrictionTimer);

      if (!restrictionCountdownEl || !iso) {
        return;
      }

      const lockedUntil = new Date(iso);
      if (Number.isNaN(lockedUntil.getTime())) {
        restrictionCountdownEl.textContent = 'Try again in 05:00.';
        return;
      }

      const tick = function() {
        const diff = Math.max(0, Math.floor((lockedUntil.getTime() - Date.now()) / 1000));
        const minutes = Math.floor(diff / 60);
        const seconds = diff % 60;
        restrictionCountdownEl.textContent = 'Try again in ' + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0') + '.';

        if (diff <= 0) {
          clearInterval(restrictionTimer);
          otpState.locked_until = null;
          otpState.otp_sent = false;
          otpState.otp_expires_at = null;
          otpState.send_count = 0;
          otpState.remaining_sends = otpState.send_limit || 3;
          restrictionModalShownForLock = null;
          applyOtpState(otpState);
          hideError();
        }
      };

      tick();
      restrictionTimer = setInterval(tick, 1000);
    }

    function showRestrictionModal(message, iso) {
      if (restrictionMessageEl) {
        restrictionMessageEl.textContent = message || 'You have reached the OTP request limit. Please wait 5 minutes before trying again.';
      }

      if (iso) {
        startRestrictionCountdown(iso);
      }

      if (otpRestrictionModal && restrictionModalShownForLock !== iso) {
        restrictionModalShownForLock = iso;
        otpRestrictionModal.show();
      }
    }

    function applyOtpState(nextState) {
      otpState = Object.assign({}, otpState, nextState || {});
      const maskedEmail = otpState.masked_email || 'your email';

      if (maskedEmailEl) {
        maskedEmailEl.textContent = maskedEmail;
      }
      if (confirmMaskedEmailEl) {
        confirmMaskedEmailEl.textContent = maskedEmail;
      }

      const locked = isLocked();
      const hasActiveCode = !!otpState.otp_sent && !!otpState.otp_expires_at && !locked;

      openSendPopoverBtn.disabled = locked;
      openSendPopoverBtn.style.display = hasActiveCode ? 'none' : 'block';
      setOtpInputsDisabled(!hasActiveCode);
      if (resendWrap) {
        resendWrap.style.display = hasActiveCode ? 'block' : 'none';
      }
      if (resendBtn) {
        resendBtn.classList.toggle('disabled', locked);
        if (locked) {
          resendBtn.setAttribute('aria-disabled', 'true');
        } else {
          resendBtn.removeAttribute('aria-disabled');
        }
      }

      updateInstruction();
      renderSendMeta();
      hideSendPopover();
      startExpiry(otpState.otp_expires_at || null);

      if (locked) {
        clearOtpDigits();
        setOtpInputsDisabled(true);
      }
    }

    function requestOtpSend() {
      if (sendRequestActive || !confirmSendBtn) {
        return;
      }

      hideError();
      hideCaptchaError();

      const captchaToken = getSendCaptchaToken();
      @if(config('services.recaptcha.site_key'))
      if (!captchaToken) {
        showCaptchaError('Please complete the CAPTCHA before sending an OTP.');
        return;
      }
      @endif

      const fd = new FormData();
      const csrf = document.querySelector('#otpModalForm input[name="_token"]');
      if (csrf) {
        fd.append('_token', csrf.value);
      }
      if (captchaToken) {
        fd.append('g-recaptcha-response', captchaToken);
      }

      sendRequestActive = true;
      confirmSendBtn.disabled = true;
      confirmSendBtn.textContent = sendPopoverMode === 'resend' ? 'Resending...' : 'Sending...';

      fetch('{{ route('otp.send') }}', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
        },
        body: fd,
      }).then(async (res) => {
        const data = await res.json().catch(() => ({ message: 'Unable to process the OTP request.' }));
        applyOtpState(data);

        if (res.ok) {
          hideError();
          hideCaptchaError();
          if (otpDigits[0] && !otpDigits[0].disabled) {
            otpDigits[0].focus();
          }
          return;
        }

        if (res.status !== 429) {
          showSendPopover(sendPopoverMode);
        }
        if (data && data.errors && data.errors['g-recaptcha-response']) {
          showCaptchaError(data.errors['g-recaptcha-response'][0]);
        }
        showError(data.message || 'Failed to send the verification code.');
        if (res.status === 429) {
          showRestrictionModal(data.message, data.locked_until || otpState.locked_until);
        }
      }).catch((err) => {
        console.error(err);
        showSendPopover(sendPopoverMode);
        showError('Failed to send the verification code. Please try again.');
      }).finally(() => {
        sendRequestActive = false;
        confirmSendBtn.disabled = false;
        confirmSendBtn.textContent = sendPopoverMode === 'resend' ? 'Confirm resend' : 'Confirm request';
      });
    }

    // digit handlers (same behavior as page version)
    otpDigits.forEach((el, idx) => {
      el.addEventListener('input', (e) => {
        if (e.target.disabled) {
          return;
        }
        const v = (e.target.value || '').replace(/\D/g, '');
        e.target.value = v.slice(0,1);
        if (v && idx < otpDigits.length - 1) otpDigits[idx+1].focus();
        // auto-submit when all digits are filled
        maybeCheckAutoSubmit();
      });
      el.addEventListener('keydown', (e) => {
        if (e.target.disabled) {
          return;
        }
        if (e.key === 'Backspace' && !e.target.value && idx > 0) otpDigits[idx-1].focus();
      });
      el.addEventListener('paste', (e) => {
        if (e.target.disabled) {
          return;
        }
        e.preventDefault();
        const text = (e.clipboardData || window.clipboardData).getData('text') || '';
        const digits = text.replace(/\D/g, '').slice(0,6).split('');
        for (let i=0;i<digits.length && i<otpDigits.length;i++) otpDigits[i].value = digits[i];
        const focusIndex = Math.min(digits.length, otpDigits.length-1);
        otpDigits[focusIndex].focus();
        // after paste, check auto-submit
        maybeCheckAutoSubmit();
      });
    });

    function maybeCheckAutoSubmit(){
      try { clearTimeout(_autoSubmitTimer); } catch(e){}
      if (_autoSubmitting) return;
      if (verifyBtn && verifyBtn.disabled) return;
      const filled = otpDigits.every(i => (i.value || '').length === 1);
      if (!filled) return;
      // small debounce to allow last input event to settle
      _autoSubmitTimer = setTimeout(()=>{
        if (_autoSubmitting) return;
        _autoSubmitting = true;
        // Trigger form submit (use requestSubmit if available)
        if (typeof otpForm.requestSubmit === 'function') {
          otpForm.requestSubmit();
        } else {
          const btn = otpForm.querySelector('button[type="submit"]');
          if (btn) btn.click();
          else otpForm.submit();
        }
        // safety: allow subsequent submits after short delay
        setTimeout(()=>{ _autoSubmitting = false; }, 1200);
      }, 200);
    }

    otpForm.addEventListener('submit', function(e){
      e.preventDefault();
      if (verifyBtn && verifyBtn.disabled) {
        showError(isLocked()
          ? 'OTP input is temporarily restricted. Please wait for the lock period to end.'
          : 'Confirm and send a verification code before entering one.');
        return;
      }
      const code = otpDigits.map(i => (i.value || '')).join('');
      if (code.length !== otpDigits.length) {
        showError('Please enter the full 6-digit code.');
        (otpDigits.find(i => !i.value) || otpDigits[0]).focus();
        return;
      }
      otpHidden.value = code;
      // POST via fetch
      const fd = new FormData(otpForm);
      fetch(otpForm.action, {
        method: 'POST',
        headers: { 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN': fd.get('_token'), 'Accept':'application/json' },
        body: fd
      }).then(async res => {
        if (res.ok) {
          const d = await res.json().catch(()=>({}));
          window.location.href = d.redirect || '/main';
        } else {
          const d = await res.json().catch(()=>({ message: 'Verification failed.' }));
          if (d && (d.locked_until || res.status === 429)) {
            applyOtpState(d);
            showRestrictionModal(d.message, d.locked_until || otpState.locked_until);
          }
          showError((d.message) || 'Verification failed.');
        }
      }).catch(err => {
        console.error(err); showError('An error occurred.');
      });
    });

    function showError(msg){
      try {
        errorBox.style.display = 'block';
        // build safe DOM nodes instead of using innerHTML
        while (errorBox.firstChild) errorBox.removeChild(errorBox.firstChild);
        const ul = document.createElement('ul');
        ul.style.margin = '0'; ul.style.paddingLeft = '18px';
        const li = document.createElement('li');
        li.textContent = msg || '';
        ul.appendChild(li);
        errorBox.appendChild(ul);
      } catch(e) {
        try { errorBox.textContent = String(msg || ''); errorBox.style.display = 'block'; } catch(_){}
      }
    }
    function hideError(){
      try { errorBox.style.display = 'none'; while (errorBox.firstChild) errorBox.removeChild(errorBox.firstChild); } catch(e){ try { errorBox.textContent = ''; } catch(_) { errorBox.innerHTML = sanitizeHtml(''); } }
    }

    if (openSendPopoverBtn) {
      openSendPopoverBtn.addEventListener('click', function() {
        hideError();
        showSendPopover('initial');
      });
    }

    if (cancelSendBtn) {
      cancelSendBtn.addEventListener('click', function() {
        hideSendPopover();
      });
    }

    if (confirmSendBtn) {
      confirmSendBtn.addEventListener('click', function() {
        requestOtpSend();
      });
    }

    if (resendBtn) {
      resendBtn.addEventListener('click', function(e) {
        e.preventDefault();
        if (resendBtn.classList.contains('disabled')) {
          return;
        }
        hideError();
        showSendPopover('resend');
      });
    }

    // Expose helper to open modal with server-provided data
    window.openOtpModal = function(opts){
      hideError();
      hideCaptchaError();
      clearOtpDigits();
      applyOtpState(opts || {});
      // hide login modal if present
      try {
        const loginModalEl = document.getElementById('loginModal');
        if (loginModalEl && typeof bootstrap !== 'undefined') {
          const lm = bootstrap.Modal.getInstance(loginModalEl) || bootstrap.Modal.getOrCreateInstance(loginModalEl);
          lm.hide();
        }
      } catch (e) {
        // ignore
      }
      if (otpModal) otpModal.show();
      if (isLocked()) {
        showRestrictionModal((opts && opts.message) || 'You have reached the OTP request limit. Please wait 5 minutes before trying again.', otpState.locked_until);
      } else if (otpState.otp_sent && otpDigits[0] && !otpDigits[0].disabled) {
        otpDigits[0].focus();
      } else if (openSendPopoverBtn) {
        openSendPopoverBtn.focus();
      }
    }

    // Add body class while OTP modal is visible to dim/blur background
    if (typeof bootstrap !== 'undefined') {
      otpModalEl.addEventListener('shown.bs.modal', function(){ document.body.classList.add('otp-modal-open'); });
      otpModalEl.addEventListener('hidden.bs.modal', function(){
        document.body.classList.remove('otp-modal-open');
        hideSendPopover();
        try {
          const loginModalEl = document.getElementById('loginModal');
          if (loginModalEl) {
            const lm = bootstrap.Modal.getInstance(loginModalEl) || bootstrap.Modal.getOrCreateInstance(loginModalEl);
            lm.show();
          }
        } catch (e){}
      });
    }

    applyOtpState(otpState);
  })();
</script>
