<div class="modal fade" id="otpModal" tabindex="-1" aria-labelledby="otpModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content otp-modal-card">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="otpModalLabel">Two-step verification</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <p class="verification-legend">A verification code has been sent to <strong id="otpMaskedEmail">your email</strong>. Enter it below to complete sign in.</p>

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
            <button type="submit" class="btn btn-otp-primary">Verify</button>
            <button type="button" class="btn btn-otp-cancel" data-bs-dismiss="modal">Cancel</button>
            <div class="text-center mt-2">
              <small class="text-muted">Didn't receive the code? <a href="#" id="otpResendBtn" class="otp-resend-link">Resend OTP</a></small>
            </div>
          </div>
        </form>

        <div class="mt-3 text-center text-muted small">
          <span id="otpModalCountdown">Code expires in —</span>
        </div>

        <div id="otpModalError" class="otp-error-box mt-3" role="alert" aria-live="polite" style="display:none;"></div>
      </div>
    </div>
  </div>

</div>

<style>
  /* Make modal card opaque and visually separate from page */
  #otpModal .modal-content.otp-modal-card { background: #ffffff; border-radius: 8px; box-shadow: 0 8px 24px rgba(4,15,36,0.12); }
  #otpModal .modal-dialog { max-width: 480px; }
  #otpModal .otp-digit { width:56px; height:44px; border-radius:6px; border:1px solid rgba(16,24,40,0.08); background:#f8fbff; font-size:18px; padding:6px; }
  #otpModal .btn-otp-primary { background:#0a66ff; border-color:#0a66ff; color:#fff; }
  #otpModal .btn-otp-cancel { background:#f3f4f6; color:#111827; }
  #otpModal .otp-error-box { background:#fff5f5; border:1px solid #f8d7da; color:#842029; padding:10px; border-radius:6px; display:none; }

  /* resend link styling */
  #otpModal .otp-resend-link { color: #0a66ff; text-decoration:underline; cursor:pointer; }
  #otpModal .otp-resend-link:hover { color: #064edc; }
  #otpModal .otp-resend-link.disabled, #otpModal .otp-resend-link[aria-disabled='true'] { pointer-events:none; opacity:0.5; cursor:default; text-decoration:none; }

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
    z-index: 1070 !important;
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
</style>

<script>
  (function(){
    if (!document.getElementById('otpModal')) return;
    const otpModalEl = document.getElementById('otpModal');
    const otpModal = typeof bootstrap !== 'undefined' ? bootstrap.Modal.getOrCreateInstance(otpModalEl) : null;
    const otpDigits = Array.from(otpModalEl.querySelectorAll('.otp-digit'));
    const otpHidden = document.getElementById('otp_modal_code_hidden');
    const otpForm = document.getElementById('otpModalForm');
    const resendBtn = document.getElementById('otpResendBtn');
    const countdownEl = document.getElementById('otpModalCountdown');
    const maskedEmailEl = document.getElementById('otpMaskedEmail');
    const errorBox = document.getElementById('otpModalError');
    let _autoSubmitTimer = null;
    let _autoSubmitting = false;

    // digit handlers (same behavior as page version)
    otpDigits.forEach((el, idx) => {
      el.addEventListener('input', (e) => {
        const v = (e.target.value || '').replace(/\D/g, '');
        e.target.value = v.slice(0,1);
        if (v && idx < otpDigits.length - 1) otpDigits[idx+1].focus();
        // auto-submit when all digits are filled
        maybeCheckAutoSubmit();
      });
      el.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !e.target.value && idx > 0) otpDigits[idx-1].focus();
      });
      el.addEventListener('paste', (e) => {
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

    // Resend
    resendBtn.addEventListener('click', function(){
      hideError();
      fetch('{{ route('otp.resend') }}', {
        method: 'POST',
        headers: { 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('#otpModalForm input[name="_token"]').value, 'Accept':'application/json' },
      }).then(async res => {
        if (res.ok) {
          const d = await res.json().catch(()=>({}));
          startCooldown(30);
        } else {
          const d = await res.json().catch(()=>({ message:'Failed to resend.' }));
          showError(d.message||'Failed to resend code.');
        }
      }).catch(err=>{ console.error(err); showError('Failed to resend.'); });
    });

    function startCooldown(sec){ resendBtn.classList.add('disabled'); resendBtn.setAttribute('aria-disabled','true'); let s=sec; const tick=()=>{ if (s<=0){ resendBtn.classList.remove('disabled'); resendBtn.removeAttribute('aria-disabled'); resendBtn.textContent='Resend code'; } else { resendBtn.textContent='Resend in '+s+'s'; s-=1; setTimeout(tick,1000);} }; tick(); }

    // Expiry countdown
    let expiryTimer = null;
    function startExpiry(iso){ if (!iso) return; clearInterval(expiryTimer); const expiryDate = new Date(iso); expiryTimer = setInterval(()=>{ const diff = Math.max(0, Math.floor((expiryDate - new Date())/1000)); const m = Math.floor(diff/60); const s = diff%60; countdownEl.textContent = 'Code expires in ' + (m>0? m+'m ':'') + String(s).padStart(2,'0')+'s'; if (diff<=0){ clearInterval(expiryTimer); showError('The verification code has expired. Please resend to get a new one.'); } }, 1000); }

    // Expose helper to open modal with server-provided data
    window.openOtpModal = function(opts){
      hideError();
      if (opts && opts.masked_email) maskedEmailEl.textContent = opts.masked_email;
      if (opts && opts.otp_expires_at) startExpiry(opts.otp_expires_at);
      otpDigits.forEach(i=>i.value='');
      otpDigits[0].focus();
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
    }

    // Add body class while OTP modal is visible to dim/blur background
    if (typeof bootstrap !== 'undefined') {
      otpModalEl.addEventListener('shown.bs.modal', function(){ document.body.classList.add('otp-modal-open'); });
      otpModalEl.addEventListener('hidden.bs.modal', function(){
        document.body.classList.remove('otp-modal-open');
        try {
          const loginModalEl = document.getElementById('loginModal');
          if (loginModalEl) {
            const lm = bootstrap.Modal.getInstance(loginModalEl) || bootstrap.Modal.getOrCreateInstance(loginModalEl);
            lm.show();
          }
        } catch (e){}
      });
    }
  })();
</script>
