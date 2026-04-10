<div class="modal fade portal-auth-modal portal-login-modal" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl portal-login-dialog">
    <div class="modal-content border-0 portal-login-frame">
      <div class="modal-body p-0">
        <div class="portal-login-shell">
          <section class="portal-login-brand-panel portal-auth-pane">
            <div class="portal-login-brand-copy">
              <span class="portal-login-kicker">Social Technology Bureau</span>
              <h2>Inventory Portal</h2>
              <p>Centralized monitoring for approved social technology records, uploads, and regional reporting.</p>
            </div>
            <div class="portal-login-illustration">
              <div class="portal-login-illustration-ring"></div>
              <img src="{{ request()->isSecure() ? secure_asset('images/dattachments/Asset 7@1080x.png') : asset('images/dattachments/Asset 7@1080x.png') }}" alt="Portal login illustration">
            </div>
            <div class="portal-login-brand-footer">
              <img src="{{ request()->isSecure() ? secure_asset('images/dattachments/social technology bureau innovating solution logo.png') : asset('images/dattachments/social technology bureau innovating solution logo.png') }}" alt="Social Technology Bureau logo">
              <div>
                <strong>STB Digital Services</strong>
                <span>Operational access for active and approved users only.</span>
              </div>
            </div>
          </section>

          <section class="portal-login-form-panel portal-auth-pane">
            <div class="portal-login-form-wrap">
              <p class="portal-login-eyebrow">Welcome back</p>
              <h1 id="loginModalLabel">Sign in to your account</h1>
              <p class="portal-login-subtitle">Please use your portal credentials to continue.</p>

              @if(session('error'))
                <div class="alert alert-danger portal-login-alert" role="alert">
                  {{ session('error') }}
                </div>
              @endif

              @if ($errors->login->any())
                <div class="alert alert-danger alert-dismissible fade show portal-login-alert" role="alert">
                  <div class="portal-login-alert-body">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <ul class="mb-0">
                      @foreach ($errors->login->all() as $error)
                        <li>{{ $error }}</li>
                      @endforeach
                    </ul>
                  </div>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              @endif

              <div id="loginErrorMsg" class="portal-login-alert portal-login-alert-inline" style="display:none;"></div>

              <form id="ajaxLoginForm" method="POST" action="{{ route('login') }}">
                @csrf

                <div class="portal-login-field">
                  <label for="loginEmail">Email address</label>
                  <div class="portal-login-input-wrap">
                    <i class="bi bi-person"></i>
                    <input type="email" id="loginEmail" name="email" value="{{ old('email') }}" placeholder="name@example.com" required autofocus>
                  </div>
                </div>

                <div class="portal-login-field">
                  <label for="loginPassword">Password</label>
                  <div class="portal-login-input-wrap">
                    <i class="bi bi-lock"></i>
                    <input type="password" id="loginPassword" name="password" placeholder="Enter your password" required>
                    <button type="button" class="portal-password-toggle" data-password-toggle data-target="loginPassword" aria-label="Show password" aria-pressed="false">
                      <i class="bi bi-eye"></i>
                    </button>
                  </div>
                </div>

                <div class="portal-login-utility-row">
                  <label class="portal-login-check" for="loginRemember">
                    <input type="checkbox" id="loginRemember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                    <span>Remember me</span>
                  </label>

                  <span class="portal-login-help portal-login-help-muted">Forgot password? Contact your administrator.</span>
                </div>

                <div class="portal-login-action-row">
                  <button type="button" class="portal-login-secondary-btn" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal">
                    Register
                  </button>
                  <button type="submit" class="portal-login-primary-btn">Log in</button>
                </div>
              </form>
            </div>
          </section>
        </div>
      </div>
    </div>
  </div>
</div>
<script>

document.addEventListener('DOMContentLoaded', function() {
  const loginModalEl = document.getElementById('loginModal');
  const loginModal = typeof bootstrap !== 'undefined' && loginModalEl
    ? bootstrap.Modal.getOrCreateInstance(loginModalEl)
    : null;

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

  if (loginModalEl) {
    bindPasswordToggles(loginModalEl);
  }

  if (@json($errors->login->any()) && loginModal) {
    loginModal.show();
  }

  const loginForm = document.getElementById('ajaxLoginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(loginForm);
      const errorMsg = document.getElementById('loginErrorMsg');
      errorMsg.style.display = 'none';
      const loginBtn = loginForm.querySelector('button[type="submit"]');
      if (loginBtn) {
        loginBtn.disabled = true;
        loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging in...';
      }
      fetch(loginForm.action, {
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
          let data = {};
          try {
            data = await response.json();
          } catch (e) {
            data = {};
          }

          window.location.href = data.redirect || window.location.href;
        } else {
          let data;
          try {
            data = await response.json();
          } catch (e) {
            data = { message: 'Login failed. (Invalid server response)' };
          }
          let msg = data.message || 'Login failed.';
          let errorSet = new Set();
          if (msg) errorSet.add(msg);
          if (data.errors) {
            for (const key in data.errors) {
              data.errors[key].forEach(function(err) {
                errorSet.add(err);
              });
            }
          }
          let errorArr = Array.from(errorSet);
          if (errorArr.length > 1) {
            msg = errorArr[0] + '<ul style="margin:0;padding-left:18px;">';
            for (let i = 1; i < errorArr.length; i++) {
              msg += '<li>' + errorArr[i] + '</li>';
            }
            msg += '</ul>';
          } else {
            msg = errorArr[0];
          }
          if (errorMsg) {
            errorMsg.innerHTML = msg;
            errorMsg.style.display = 'block';
            if (loginModal) {
              loginModal.show();
            }
          }
        }
      })
      .catch((err) => {
        console.error('AJAX login fetch error:', err);
        if (errorMsg) {
          errorMsg.innerHTML = 'An error occurred. Please try again.';
          errorMsg.style.display = 'block';
          if (loginModal) {
            loginModal.show();
          }
        }
      })
      .finally(() => {
        if (loginBtn) {
          loginBtn.disabled = false;
          loginBtn.innerHTML = 'Log in';
        }
      });
    });
  }
});
</script>
