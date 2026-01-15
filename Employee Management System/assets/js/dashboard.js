/**
 * Dashboard JavaScript
 * Session timer and dashboard-specific functionality
 */

document.addEventListener('DOMContentLoaded', function () {

    // Session timeout countdown timer
    const sessionTimer = document.getElementById('session-timer');
    if (sessionTimer) {
        // 5 minutes = 300 seconds
        let timeLeft = 300;

        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            sessionTimer.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;

            if (timeLeft <= 60) {
                sessionTimer.style.color = '#EF4444'; // Red color when less than 1 minute
            }

            if (timeLeft <= 0) {
                alert('Your session has expired. You will be redirected to the login page.');
                window.location.href = '../auth/login.php?session_expired=1';
                return;
            }

            timeLeft--;
        }

        // Update timer every second
        updateTimer();
        setInterval(updateTimer, 1000);

        // Reset timer on user activity
        const resetTimer = () => {
            timeLeft = 300;
            sessionTimer.style.color = '#F59E0B'; // Reset to orange
        };

        document.addEventListener('click', resetTimer);
        document.addEventListener('keypress', resetTimer);
        document.addEventListener('mousemove', resetTimer);
    }

    // Table sorting (optional enhancement)
    const tableHeaders = document.querySelectorAll('.data-table th');
    tableHeaders.forEach((header, index) => {
        header.style.cursor = 'pointer';
        header.title = 'Click to sort';
    });
});
