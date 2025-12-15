// Order Confirmation Page JavaScript
document.addEventListener('DOMContentLoaded', function () {
    // Print invoice functionality
    const printBtn = document.querySelector('button[onclick*="print"]');
    if (printBtn) {
        printBtn.addEventListener('click', function (e) {
            e.preventDefault();
            window.print();
        });
    }

    // Add status badge styles
    const statusBadges = document.querySelectorAll('.status-badge');
    statusBadges.forEach(badge => {
        const status = badge.classList.contains('status-pending') ? 'pending' :
            badge.classList.contains('status-processing') ? 'processing' :
                badge.classList.contains('status-completed') ? 'completed' :
                    badge.classList.contains('status-cancelled') ? 'cancelled' : 'pending';

        // Add appropriate colors
        switch (status) {
            case 'pending':
                badge.style.background = '#fff3cd';
                badge.style.color = '#856404';
                break;
            case 'processing':
                badge.style.background = '#cce5ff';
                badge.style.color = '#004085';
                break;
            case 'completed':
                badge.style.background = '#d4edda';
                badge.style.color = '#155724';
                break;
            case 'cancelled':
                badge.style.background = '#f8d7da';
                badge.style.color = '#721c24';
                break;
        }

        badge.style.padding = '5px 10px';
        badge.style.borderRadius = '20px';
        badge.style.fontSize = '0.9em';
        badge.style.fontWeight = 'bold';
        badge.style.display = 'inline-block';
    });
});