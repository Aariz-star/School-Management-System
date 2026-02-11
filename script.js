/**
 * Toggle Sidebar Visibility
 */
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
}

/**
 * Show Specific Form/Section
 */
function showForm(formId) {
    // Hide all forms
    const forms = document.querySelectorAll('.form-content');
    forms.forEach(form => form.classList.remove('active'));

    // Show selected form
    const selectedForm = document.getElementById(formId);
    if (selectedForm) {
        selectedForm.classList.add('active');
    }

    // Update Active Button State
    const navBtns = document.querySelectorAll('.nav-btn');
    navBtns.forEach(btn => btn.classList.remove('active'));
    const activeBtn = document.querySelector(`.nav-btn[onclick*="showForm('${formId}')"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
    }
    
    // Close sidebar on mobile if open
    const sidebar = document.getElementById('sidebar');
    if (sidebar && sidebar.classList.contains('active')) {
        toggleSidebar();
    }
}

/**
 * Toggle Sub-Sections in Add Class Form
 */
function toggleSubSection(sectionId) {
    // Hide all sub-sections
    const sections = document.querySelectorAll('.sub-section');
    sections.forEach(sec => sec.style.display = 'none');

    // Show the selected one
    const target = document.getElementById(sectionId);
    if (target) {
        target.style.display = 'block';
    }
}

/**
 * Toggle Book Input in Add Class Form
 */
function toggleBookInput(checkbox) {
    const input = checkbox.closest('.subject-item').querySelector('.book-input');
    input.style.display = checkbox.checked ? 'block' : 'none';
}

/**
 * Load Grades via AJAX
 */
function loadGrades(prefix, mode = 'edit') {
    const classId = document.getElementById(prefix + '_class_select').value;
    const subjectSelect = document.getElementById(prefix + '_subject_select');
    const subjectId = subjectSelect ? subjectSelect.value : 0;
    const term = document.getElementById(prefix + '_term_input').value;
    
    if(!classId) {
        return;
    }

    // Update hidden inputs for form submission
    if (mode === 'edit') {
        const hClassId = document.getElementById(prefix + '_h_class_id');
        const hSubjectId = document.getElementById(prefix + '_h_subject_id');
        const hTerm = document.getElementById(prefix + '_h_term');

        if(hClassId) hClassId.value = classId;
        if(hSubjectId) hSubjectId.value = subjectId;
        if(hTerm) hTerm.value = term;
    }

    // Fetch data via AJAX
    fetch(`fetch_grades.php?class_id=${classId}&subject_id=${subjectId}&term=${encodeURIComponent(term)}&mode=${mode}`)
        .then(response => response.text())
        .then(html => {
            const tbody = document.getElementById(prefix + '_grade_table_body');
            if(tbody) tbody.innerHTML = html;
            // Show submit button if we have results
            const btn = document.getElementById(prefix + '_grade_submit_btn');
            if(btn) btn.style.display = 'block';
        })
        .catch(error => console.error('Error:', error));
}

/**
 * Redirect Helpers
 */
function filterAttendanceByClass(classId) {
    const page = window.location.pathname.split("/").pop();
    window.location.href = page + '?attendance_class_id=' + classId;
}

function filterGradeByClass(classId, section) {
    const page = window.location.pathname.split("/").pop();
    window.location.href = page + '?grade_class_id=' + classId + '&grade_section=' + section;
}

function filterFeeByClass(classId) {
    const page = window.location.pathname.split("/").pop();
    window.location.href = page + '?fee_class_id=' + classId;
}

function updateAttendanceDate(classId, date) {
    const page = window.location.pathname.split("/").pop();
    window.location.href = page + '?attendance_class_id=' + classId + '&attendance_date=' + date;
}

/**
 * Update Subjects Dropdown via AJAX (Prevents Page Refresh)
 */
function updateSubjects(classId, prefix) {
    const subjectSelect = document.getElementById(prefix + '_subject_select');
    const gradeTable = document.getElementById(prefix + '_grade_table_body');
    const submitBtn = document.getElementById(prefix + '_grade_submit_btn');
    
    // Reset UI
    if (subjectSelect) subjectSelect.innerHTML = '<option value="">Loading...</option>';
    if (gradeTable) gradeTable.innerHTML = '';
    if (submitBtn) submitBtn.style.display = 'none';

    if (!classId) {
        if (subjectSelect) subjectSelect.innerHTML = '<option value="">-- Select Subject --</option>';
        return;
    }

    fetch(`fetch_subjects.php?class_id=${classId}`)
        .then(response => response.text())
        .then(html => {
            if (subjectSelect) subjectSelect.innerHTML = html;
        })
        .catch(err => console.error('Error loading subjects:', err));
}

// Event Listeners for URL Parameters
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('attendance_class_id')) {
        showForm('attendance');
    }
    
    if (urlParams.has('grade_class_id')) {
        showForm('grade');
    }
    
    if (urlParams.has('fee_class_id')) {
        showForm('fee');
    }
    
    if (urlParams.has('section')) {
        showForm(urlParams.get('section'));
    }

    const gradeSection = urlParams.get('grade_section');
    if (gradeSection) {
        toggleSubSection('sub_' + gradeSection + '_grades');
    }

    // Disable autocomplete on all forms to prevent suggestions
    document.querySelectorAll('form').forEach(form => {
        form.setAttribute('autocomplete', 'off');
    });
});

function printAllDMCs() {
    const classId = document.getElementById('view_class_select').value;
    const term = document.getElementById('view_term_input').value;

    if (!classId) {
        alert("Please select a class first.");
        return;
    }
    if (!term) {
        alert("Please enter a term.");
        return;
    }

    if (confirm("Are you sure you want to print DMCs for all students in this class?")) {
        window.open(`print_all_dmcs.php?class_id=${classId}&term=${encodeURIComponent(term)}`, '_blank');
    }
}