<div id="loading-overlay" class="loading-overlay">
    <div class="loader10">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>
</div>

<script>
    // Show loader function
    function showLoader() {
        document.getElementById('loading-overlay').classList.remove('hidden');
    }

    // Hide loader function
    function hideLoader() {
        document.getElementById('loading-overlay').classList.add('hidden');
    }


    // (Removed auto-hide on window load; background image preloader will hide loader)

    // Show loader on page navigation (optional)
    document.addEventListener('DOMContentLoaded', function() {
        // Intercept form submissions
        document.querySelectorAll('form').forEach(function(form) {
            // Skip AJAX forms (handled manually) or any form marked with class
            if (form.classList.contains('ajax-form') 
                || form.id === 'ajaxLoginForm' || form.id === 'ajaxRegisterForm' || form.id === 'addUserForm') return;
            form.addEventListener('submit', function(event) {
                // If another handler (like an onsubmit confirm) prevented submission, don't show loader
                if (event.defaultPrevented) {
                    return;
                }
                showLoader();
            });
        });

        // Intercept link clicks (optional - for AJAX navigation)
        // Uncomment if you want loader on all link clicks
        // document.querySelectorAll('a:not([target="_blank"])').forEach(function(link) {
        //     link.addEventListener('click', function(e) {
        //         if (this.href && !this.href.startsWith('#')) {
        //             showLoader();
        //         }
        //     });
        // });
    });
</script>
