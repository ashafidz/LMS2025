// Place this in a public JS file like resources/js/csrf-refresh.js

/**
 * Function to refresh the CSRF token
 * Use this in case of 419 errors to get a fresh token
 */
function refreshCsrfToken() {
    return new Promise((resolve, reject) => {
        fetch('/csrf-refresh', {
            method: 'GET',
            credentials: 'same-origin', // This ensures cookies are sent
        })
        .then(response => {
            if (response.ok) {
                return response.json();
            }
            throw new Error('Network response was not ok.');
        })
        .then(data => {
            // Update the token in the meta tag
            const metaToken = document.querySelector('meta[name="csrf-token"]');
            if (metaToken) {
                metaToken.setAttribute('content', data.csrf_token);
            }
            
            // Update token in Axios headers
            if (window.axios) {
                window.axios.defaults.headers.common['X-CSRF-TOKEN'] = data.csrf_token;
            }
            
            // Update all form tokens
            document.querySelectorAll('input[name="_token"]').forEach(input => {
                input.value = data.csrf_token;
            });
            
            resolve(data.csrf_token);
        })
        .catch(error => {
            console.error('Error refreshing CSRF token:', error);
            reject(error);
        });
    });
}

// Detect 419 responses and automatically refresh the token
document.addEventListener('DOMContentLoaded', function() {
    // Add a global AJAX error handler
    $(document).ajaxError(function(event, jqXHR, ajaxSettings, thrownError) {
        if (jqXHR.status === 419) {
            console.log('CSRF token mismatch detected. Refreshing token...');
            refreshCsrfToken().then(() => {
                // Retry the failed request with the new token
                $.ajax({
                    ...ajaxSettings,
                    headers: {
                        ...ajaxSettings.headers,
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
            });
        }
    });
    
    // Add retry logic for form submissions
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            // Store the form for potential resubmission
            const thisForm = this;
            
            // Add error handling for the submission
            thisForm.addEventListener('error', function(err) {
                if (err.detail && err.detail.status === 419) {
                    e.preventDefault();
                    refreshCsrfToken().then(() => {
                        thisForm.submit();
                    });
                }
            });
        });
    });
});
