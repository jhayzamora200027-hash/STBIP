<!-- Login Modal Blade Partial -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content modal-login-bg border-0">
      <div class="modal-body p-0">
        <div class="modal-login-content">
          <h1>STB Inventory Portal</h1>
          {{-- <img src="/images/dattachments/DSWD STB Bagong Pil logo.png" style="width:400px; height: 100px;margin-bottom:16px;"> --}}
          <h2 id="loginModalLabel">Login</h2>
          @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
          @endif
          @if ($errors->login->any())
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" style="background:rgba(255,0,0,0.12);border:1.5px solid #ff4d4f;color:#f3f1f1;padding:18px 20px 18px 16px;border-radius:12px;box-shadow:0 2px 8px rgba(255,77,79,0.08);font-size:1.05rem;gap:12px;max-height: 300px; overflow-y: auto;" role="alert">
              <i class="bi bi-exclamation-triangle-fill" style="font-size:1.5rem;color:#ff4d4f;"></i>
              <div style="flex:1;">
                <ul class="mb-0" style="list-style:none;padding-left:0;">
                  @foreach ($errors->login->all() as $error)
                    <li style="word-wrap: break-word; white-space: normal;">{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
          <div id="loginErrorMsg" style="display:none;margin-bottom:12px;font-size:1.1rem;font-weight:bold;color:#fff;background:#ff4d4f;padding:12px 16px;border-radius:8px;text-align:center;box-shadow:0 2px 8px rgba(255,77,79,0.18);z-index:10;"></div>
          <form id="ajaxLoginForm" method="POST" action="{{ route('login') }}">
            @csrf
            <label for="loginEmail" style="float:left;width:100%;color:#fff;text-align:left;font-size:1rem;">Email</label>
            <input type="email" id="loginEmail" name="email" required autofocus>
            <label for="loginPassword" style="float:left;width:100%;color:#fff;text-align:left;font-size:1rem;">Password</label>
            <input type="password" id="loginPassword" name="password" required>
            <div class="actions"></div>
            <button type="submit">Log in</button>
          </form>
          <div class="register">
            Don't have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal">Register</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
// Show login modal if there are login errors
// (requires Bootstrap 5 JS to be loaded globally)
document.addEventListener('DOMContentLoaded', function() {
    if (@json($errors->login->any())) {
        let loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
        loginModal.show();
    }
  // AJAX login logic
  const loginForm = document.getElementById('ajaxLoginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(loginForm);
      const errorMsg = document.getElementById('loginErrorMsg');
      errorMsg.style.display = 'none';
      // Show loading state
      const loginBtn = loginForm.querySelector('button[type="submit"]');
      if (loginBtn) {
        loginBtn.disabled = true;
        loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging in...';
      }
      // Send AJAX request for login
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
        console.log('AJAX login response:', response);
        if (response.ok) {
          // Login success, reload page
          window.location.reload();
        } else {
          // Show error message
          let data;
          try {
            data = await response.json();
          } catch (e) {
            data = { message: 'Login failed. (Invalid server response)' };
          }
          console.log('AJAX login error data:', data);
          let msg = data.message || 'Login failed.';
          // Collect all error messages
          let errorSet = new Set();
          if (msg) errorSet.add(msg);
          if (data.errors) {
            for (const key in data.errors) {
              data.errors[key].forEach(function(err) {
                errorSet.add(err);
              });
            }
          }
          // Render only unique messages
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
            // Show modal if not visible
            if (typeof bootstrap !== 'undefined') {
              let loginModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('loginModal'));
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
          if (typeof bootstrap !== 'undefined') {
            let loginModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('loginModal'));
            loginModal.show();
          }
        }
      })
      .finally(() => {
        // Always re-enable button and restore text
        if (loginBtn) {
          loginBtn.disabled = false;
          loginBtn.innerHTML = 'Log in';
        }
      });
    });
  }
});
</script>
