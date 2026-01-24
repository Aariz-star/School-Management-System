/**
 * Toggle Teachers List Visibility
 */
function toggleTeachersList() {
    const container = document.getElementById('teachers-list-container');
    const button = event?.target || document.activeElement;
    
    if (container.classList.contains('show')) {
        container.classList.remove('show');
        button.textContent = 'Show All Teachers';
        button.style.backgroundColor = '';
    } else {
        container.classList.add('show');
        button.textContent = 'Hide Teachers';
        button.style.backgroundColor = '#d63031';
    }
}

/**
 * Show/Hide Form Based on Tab Click
 * When user clicks a navigation button, show that form and hide others
 */
function showForm(formId) {
    // Get all forms
    const forms = document.querySelectorAll('.form-content');
    
    // Get all nav buttons
    const navBtns = document.querySelectorAll('.nav-btn');
    
    // Hide all forms
    forms.forEach(form => {
        form.classList.remove('active');
    });
    
    // Remove active class from all buttons
    navBtns.forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show the selected form
    const selectedForm = document.getElementById(formId);
    if (selectedForm) {
        selectedForm.classList.add('active');
    }
    
    // Add active class to clicked button
    event.target.classList.add('active');
    
    // Smooth scroll to form (optional)
    selectedForm?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

/**
 * Auto-hide notifications after 5 seconds
 */
document.addEventListener('DOMContentLoaded', function() {
    const notification = document.getElementById('notification');
    
    if (notification) {
        setTimeout(function() {
            notification.style.animation = 'slideOutRight 0.5s ease-out forwards';
            setTimeout(function() {
                notification.remove();
            }, 500);
        }, 5000); // Hide after 5 seconds
    }
    
    // Form submission - optional client-side validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Uncomment below if you want to add custom validation
            // if (!validateForm(this)) {
            //     e.preventDefault();
            // }
        });
    });
});

/**
 * Create and show a notification (used by AJAX flows)
 */
function showNotification(message, type = 'success') {
    // Remove any existing notification
    const existing = document.getElementById('notification');
    if (existing) existing.remove();

    const div = document.createElement('div');
    div.id = 'notification';
    div.className = 'notification ' + (type === 'error' ? 'notification-error' : 'notification-success');
    div.innerHTML = message;
    document.body.insertBefore(div, document.body.firstChild);

    // Auto-hide after 5s
    setTimeout(function() {
        div.style.animation = 'slideOutRight 0.5s ease-out forwards';
        setTimeout(function() { div.remove(); }, 500);
    }, 5000);
}

/**
 * Handle delete button clicks via event delegation
 */
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.delete-btn');
    if (!btn) return;
    e.preventDefault();

    const id = btn.getAttribute('data-id');
    const type = btn.getAttribute('data-type');
    if (!id || !type) return;

    const nameCell = btn.closest('tr')?.querySelector('td:nth-child(2)');
    const name = nameCell ? nameCell.textContent.trim() : '';

    if (!confirm(`Delete ${type} ${name} ?`)) return;

    const endpoint = type === 'student' ? 'student_delete.php' : 'teacher_delete.php';

    // Use FormData for POST
    const data = new FormData();
    data.append('id', id);

    fetch(endpoint, { method: 'POST', body: data })
    .then(resp => resp.json())
    .then(json => {
        if (json.success) {
            // remove row
            const row = btn.closest('tr');
            if (row) row.remove();
            showNotification(json.message || 'Deleted successfully', 'success');
        } else {
            showNotification(json.message || 'Delete failed', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showNotification('Network or server error', 'error');
    });
});

/**
 * Optional: Client-side form validation
 * Uncomment and customize as needed
 */
/*
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    for (let input of inputs) {
        if (!input.value.trim()) {
            alert('Please fill in all required fields!');
            input.focus();
            return false;
        }
    }
    
    return true;
}
*/

