<div id="loading-overlay" class="loading-overlay">
    <div class="loader10">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>
</div>

<script>
    function showLoader() {
        const overlay = document.getElementById('loading-overlay');
        if (!overlay) {
            return;
        }

        overlay.classList.remove('hidden');
    }

    function hideLoader() {
        const overlay = document.getElementById('loading-overlay');
        if (!overlay) {
            return;
        }

        overlay.classList.add('hidden');
    }



    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('form').forEach(function(form) {
            if (form.classList.contains('ajax-form') 
                || form.id === 'ajaxLoginForm' || form.id === 'ajaxRegisterForm' || form.id === 'addUserForm') return;
            form.addEventListener('submit', function(event) {
                if (event.defaultPrevented) {
                    return;
                }
                showLoader();
            });
        });

        
    });
</script>
