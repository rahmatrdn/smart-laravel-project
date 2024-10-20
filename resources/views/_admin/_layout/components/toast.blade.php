<style>
    .toast {
        transition: transform 0.4s ease, opacity 0.4s ease;
        margin-bottom: 10px; /* space between toasts */
    }
    
    .toast-container .toast {
        transform: translateY(0);
    }
    
    .toast.slide-out {
        opacity: 0;
        transform: translateY(-20px); /* animate upward when hiding */
    }
</style>

<div class="toast-container position-fixed top-0 end-0 p-3" id="toastContainer">
    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <svg class="bd-placeholder-img rounded me-2" width="20" height="20" xmlns="http://www.w3.org/2000/svg"
                aria-hidden="true" preserveAspectRatio="xMidYMid slice" focusable="false">
                <rect id="toast-icon" width="100%" height="100%" fill="">
                </rect>
            </svg>
            <strong class="me-auto">Notifikasi</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div id="toast-body" class="toast-body fs-4"
            style="border-bottom-left-radius: 10px;border-bottom-right-radius: 10px;"></div>
    </div>
</div>


<script>
    function showToast(type, message) {
        // Create a new toast element
        var newToast = document.createElement('div');
        newToast.classList.add('toast');
        newToast.setAttribute('role', 'alert');
        newToast.setAttribute('aria-live', 'assertive');
        newToast.setAttribute('aria-atomic', 'true');
        
        // Set the toast inner HTML
        newToast.innerHTML = `
            <div class="toast-header">
                <svg class="bd-placeholder-img rounded me-2" width="20" height="20" xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true" preserveAspectRatio="xMidYMid slice" focusable="false">
                    <rect width="100%" height="100%" fill=""></rect>
                </svg>
                <strong class="me-auto">Notifikasi</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body fs-4"
                style="border-bottom-left-radius: 10px;border-bottom-right-radius: 10px;"></div>
        `;

        // Set the message and type (success or error)
        var toastBody = newToast.querySelector('.toast-body');
        var toastIcon = newToast.querySelector('rect');
        toastBody.textContent = message;

        if (type === 'success') {
            toastIcon.setAttribute('fill', 'green');
            toastBody.classList.remove('text-danger');
            toastBody.classList.add('text-success', 'fw-bolder', 'bg-success-subtle');
        } else if (type === 'error') {
            toastIcon.setAttribute('fill', 'red');
            toastBody.classList.remove('text-success');
            toastBody.classList.add('text-danger', 'bg-danger-subtle');
        }

        // Append the new toast to the container
        var toastContainer = document.getElementById('toastContainer');
        toastContainer.appendChild(newToast);

        // Initialize the Bootstrap toast
        var toast = new bootstrap.Toast(newToast, {
            autohide: true,
            delay: 4000
        });

        // Add animations for show/hide
        newToast.addEventListener('show.bs.toast', function() {
            newToast.classList.add('slide-in');
        });

        newToast.addEventListener('hide.bs.toast', function() {
            newToast.classList.remove('slide-in');
            newToast.classList.add('slide-out');
        });

        newToast.addEventListener('hidden.bs.toast', function() {
            newToast.classList.remove('slide-out');
            // Optionally remove the toast from DOM after hidden
            newToast.remove();
            
            // Recalculate positions to make the transition smooth
            var toasts = document.querySelectorAll('.toast-container .toast');
            toasts.forEach((toast, index) => {
                toast.style.transform = `translateY(${index * 10}px)`;
            });
        });

        toast.show();
    }
</script>