/**
 * Student Dashboard Specific Scripts
 */

/**
 * Initialize notification auto-dismiss
 */
function initNotifications() {
    const notifications = document.querySelectorAll('#notification');
    notifications.forEach(notif => {
        const isError = notif.classList.contains('notification-error');
        const duration = isError ? 5000 : 4000;
        
        setTimeout(() => {
            notif.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => notif.remove(), 300);
        }, duration);
    });
}

/**
 * Initialize file upload button state change
 */
function initFileUploadHandlers() {
    const fileInputs = document.querySelectorAll('.file-input');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const label = this.closest('.file-upload-label');
            const span = label.querySelector('.upload-text');
            span.innerText = 'Image Selected ✅';
            label.style.background = '#00d4ff';
            label.style.color = '#000';
        });
    });
}

/**
 * Filter and display grades by term
 */
function filterResults() {
    const filter = document.getElementById('termFilter').value;
    const rows = document.querySelectorAll('.result-row');
    const isDefault = filter === "";
    
    let totalObtained = 0;
    let maxMarks = 0;
    let visibleCount = 0;

    rows.forEach(row => {
        const term = row.getAttribute('data-term');
        const scoreCell = row.querySelector('.score-cell');
        const statusCell = row.querySelector('.status-cell');

        if (isDefault || term === filter) {
            row.style.display = '';
            if (!isDefault && scoreCell) {
                const score = parseFloat(scoreCell.textContent.trim()) || 0;
                totalObtained += score;
                maxMarks += 100;
                visibleCount++;
            }
        } else {
            row.style.display = 'none';
        }

        if (scoreCell) scoreCell.style.visibility = isDefault ? 'hidden' : 'visible';
        if (statusCell) statusCell.style.visibility = isDefault ? 'hidden' : 'visible';
    });

    // Update Summary
    const summaryDiv = document.getElementById('termSummary');
    if (summaryDiv) {
        if (!isDefault && visibleCount > 0) {
            summaryDiv.style.display = 'block';
            document.getElementById('summaryObtained').innerText = totalObtained;
            document.getElementById('summaryTotal').innerText = maxMarks;
            const percent = maxMarks > 0 ? ((totalObtained / maxMarks) * 100).toFixed(2) : 0;
            document.getElementById('summaryPercentage').innerText = percent + '%';
            
            const statusElem = document.getElementById('summaryStatus');
            statusElem.innerText = percent >= 40 ? 'PASS' : 'FAIL';
            statusElem.style.color = percent >= 40 ? '#10b981' : '#ef4444';
        } else {
            summaryDiv.style.display = 'none';
        }
    }
}

/**
 * Initialize on page load
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Student Dashboard Loaded');
    initNotifications();
    initFileUploadHandlers();
    filterResults();
});