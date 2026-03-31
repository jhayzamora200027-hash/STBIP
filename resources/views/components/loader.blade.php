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
        document.getElementById('loading-overlay').classList.remove('hidden');
    }

    function hideLoader() {
        document.getElementById('loading-overlay').classList.add('hidden');
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
