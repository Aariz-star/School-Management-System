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
    const target = document.getElementById(sectionId);
    if (!target) return;

    // 1. Hide all sibling sub-sections (Scoped to parent container)
    const container = target.closest('.form-content') || target.parentElement;
    if (container) {
        container.querySelectorAll('.sub-section').forEach(sec => sec.style.display = 'none');
    }

    // 2. Show the selected one
    target.style.display = 'block';

    // 3. Highlight the active button
    const buttons = document.querySelectorAll(`button[onclick*="toggleSubSection('${sectionId}')"]`);
    buttons.forEach(btn => {
        const nav = btn.closest('.sub-nav-grid');
        if (nav) {
            nav.querySelectorAll('button').forEach(b => b.classList.remove('active-sub-btn'));
            btn.classList.add('active-sub-btn');
        }
    });
}

/**
 * Load Class Subjects Table via AJAX
 */
function loadClassSubjectsTable(classId) {
    const container = document.getElementById('class_subjects_container');
    const tableWrapper = document.getElementById('class_subjects_table_wrapper');
    const noClassMsg = document.getElementById('no_class_selected_msg');
    const addSubjectClassId = document.getElementById('add_subject_class_id');
    
    console.log('🔍 loadClassSubjectsTable called with classId:', classId);
    
    if (!classId) {
        console.log('ℹ️ No class ID provided, hiding container');
        container.classList.remove('active');
        container.style.display = 'none';
        noClassMsg.style.display = 'block';
        return;
    }
    
    // Set the class ID for the add form
    addSubjectClassId.value = classId;
    
    // Show loading state
    tableWrapper.innerHTML = '<p style="text-align: center; color: #666; padding: 2rem;">Loading subjects...</p>';
    container.classList.add('active');
    container.style.display = 'block';
    noClassMsg.style.display = 'none';
    
    console.log('📤 Fetching: fetch_class_subjects_details.php?class_id=' + classId);
    
    // Fetch subjects for this class
    fetch(`fetch_class_subjects_details.php?class_id=${encodeURIComponent(classId)}`)
        .then(response => {
            console.log('📥 Response received:', response.status, response.ok);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            console.log('✅ HTML received, length:', html.length);
            console.log('📋 HTML Preview:', html.substring(0, 200));
            tableWrapper.innerHTML = html;
            console.log('✅ Container HTML updated');
        })
        .catch(error => {
            console.error('❌ Error loading subjects:', error);
            tableWrapper.innerHTML = '<p style="color: #ef4444; padding: 2rem;">Error loading subjects. Please try again. Error: ' + error.message + '</p>';
        });
}

/**
 * Remove Subject from Class
 */
function removeSubjectFromClass(compositeKey) {
    if (!confirm('Are you sure you want to remove this subject from the class?')) {
        return;
    }
    
    console.log('🗑️ Removing subject with composite key:', compositeKey);
    
    const formData = new FormData();
    formData.append('composite_key', compositeKey);
    
    fetch('delete_class_subject_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('✅ Remove response:', data);
        if (data.success) {
            // Reload the table
            const classId = document.getElementById('class_subjects_filter').value;
            loadClassSubjectsTable(classId);
            alert('Subject removed successfully!');
        } else {
            alert('Error: ' + (data.message || 'Could not remove subject'));
        }
    })
    .catch(error => {
        console.error('❌ Error removing subject:', error);
        alert('An error occurred. Please try again.');
    });
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

/**
 * Initialize Dashboard Charts (Attendance & Grades)
 */
function initDashboardCharts() {
    // Check if chart data is available
    if (typeof window.schoolChartData === 'undefined') {
        console.warn('Chart data not available');
        return;
    }

    const data = window.schoolChartData;

    // 1. Attendance Trend Chart (Line Chart)
    const attendanceCtx = document.getElementById('attendanceChart');
    if (attendanceCtx && data.dates && data.dates.length > 0) {
        new Chart(attendanceCtx, {
            type: 'line',
            data: {
                labels: data.dates,
                datasets: [
                    {
                        label: 'Present',
                        data: data.present,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#fff',
                        pointRadius: 5,
                        pointHoverRadius: 7
                    },
                    {
                        label: 'Absent',
                        data: data.absent,
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#ef4444',
                        pointBorderColor: '#fff',
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            color: '#e0e0e0',
                            font: { size: 12 }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.95)',
                        titleColor: '#00d4ff',
                        titleFont: { size: 14, weight: 'bold' },
                        bodyColor: '#e0e0e0',
                        bodyFont: { size: 13 },
                        borderColor: '#00d4ff',
                        borderWidth: 2,
                        padding: 12,
                        displayColors: true,
                        boxPadding: 8,
                        cornerRadius: 6,
                        titleMarginBottom: 8,
                        usePointStyle: true,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.parsed.y + ' students';
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: '#999' },
                        grid: { color: 'rgba(255,255,255,0.1)' }
                    },
                    x: {
                        ticks: { color: '#999' },
                        grid: { color: 'rgba(255,255,255,0.1)' }
                    }
                }
            }
        });
    }

    // 2. Student Performance (Grades) Chart (Bar Chart)
    const performanceCtx = document.getElementById('performanceChart');
    if (performanceCtx && data.grades && data.grades.length > 0) {
        new Chart(performanceCtx, {
            type: 'bar',
            data: {
                labels: ['Excellent (80+)', 'Good (60-79)', 'Average (40-59)', 'Fail (<40)'],
                datasets: [
                    {
                        label: 'Number of Students',
                        data: data.grades,
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.8)',   // Green for Excellent
                            'rgba(59, 130, 246, 0.8)',   // Blue for Good
                            'rgba(245, 158, 11, 0.8)',   // Orange for Average
                            'rgba(239, 68, 68, 0.8)'     // Red for Fail
                        ],
                        borderColor: [
                            '#10b981',
                            '#3b82f6',
                            '#f59e0b',
                            '#ef4444'
                        ],
                        borderWidth: 1.5,
                        borderRadius: 5,
                        hoverBackgroundColor: [
                            'rgba(16, 185, 129, 1)',
                            'rgba(59, 130, 246, 1)',
                            'rgba(245, 158, 11, 1)',
                            'rgba(239, 68, 68, 1)'
                        ]
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'x',
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            color: '#e0e0e0',
                            font: { size: 12 }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.95)',
                        titleColor: '#00d4ff',
                        titleFont: { size: 14, weight: 'bold' },
                        bodyColor: '#e0e0e0',
                        bodyFont: { size: 13 },
                        borderColor: '#00d4ff',
                        borderWidth: 2,
                        padding: 12,
                        displayColors: true,
                        boxPadding: 8,
                        cornerRadius: 6,
                        titleMarginBottom: 8,
                        usePointStyle: true,
                        callbacks: {
                            label: function(context) {
                                return 'Students: ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: '#999' },
                        grid: { color: 'rgba(255,255,255,0.1)' }
                    },
                    x: {
                        ticks: { color: '#999' },
                        grid: { color: 'rgba(255,255,255,0.1)' }
                    }
                }
            }
        });
    }
}

// Event Listeners for URL Parameters
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    // Get return_to section from window variable or URL params
    let sectionToShow = window.returnToSection || urlParams.get('return_to') || 'dashboard';
    
    // Validate section exists before showing it
    const targetSection = document.getElementById(sectionToShow);
    if (!targetSection) {
        sectionToShow = 'dashboard';
    }
    
    // Show the appropriate section
    showForm(sectionToShow);
    
    // Fallback for URL parameters
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

    // Initialize Sub-section Buttons State (Highlight default visible sections)
    document.querySelectorAll('.sub-section').forEach(section => {
        if (section.style.display !== 'none') {
            const id = section.id;
            const btn = document.querySelector(`button[onclick*="toggleSubSection('${id}')"]`);
            if (btn) btn.classList.add('active-sub-btn');
        }
    });

    // Initialize Dashboard Charts on page load
    setTimeout(initDashboardCharts, 100);
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

// Delete Handler
document.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('delete-btn')) {
        const id = e.target.getAttribute('data-id');
        const type = e.target.getAttribute('data-type');
        const csrf = e.target.getAttribute('data-csrf');
        
        if (confirm('Are you sure you want to delete this record?')) {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('csrf_token', csrf);
            
            const url = type === 'teacher' ? 'teacher_delete.php' : 'student_delete.php';
            
            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }
    }
});

/**
 * Fetch Subjects for a specific class (Generic)
 */
function fetchClassSubjects(classId, targetId) {
    const target = document.getElementById(targetId);
    if (!target) return;

    if (!classId) {
        target.innerHTML = '<option value="">Select Class First</option>';
        return;
    }

    target.innerHTML = '<option value="">Loading...</option>';

    fetch(`fetch_subjects.php?class_id=${classId}`)
        .then(response => response.text())
        .then(html => {
            target.innerHTML = html;
        })
        .catch(error => console.error('Error:', error));
}

/**
 * Load Students for Promotion
 */
function loadPromoteStudents() {
    const classId = document.getElementById('promote_current_class').value;
    const container = document.getElementById('promote_student_list_container');
    const btn = document.getElementById('promote_btn');

    if (!classId) {
        container.innerHTML = '';
        btn.style.display = 'none';
        return;
    }

    container.innerHTML = '<p style="text-align:center;">Loading students...</p>';

    fetch(`fetch_promote_students.php?class_id=${classId}`)
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
            if (html.includes('<table')) {
                btn.style.display = 'block';
            } else {
                btn.style.display = 'none';
            }
        })
        .catch(err => {
            console.error(err);
            container.innerHTML = '<p class="error">Error loading students.</p>';
        });
}

function toggleAllPromote(source) {
    const checkboxes = document.querySelectorAll('.promote-checkbox');
    checkboxes.forEach(cb => cb.checked = source.checked);
}

/**
 * Invoice Management Functions
 */

/**
 * Filter invoices by class and load them into table
 */
function filterInvoicesByClass() {
    const classId = document.getElementById('delete_class_filter').value;
    if (!classId) {
        document.getElementById('invoices_list').style.display = 'none';
        return;
    }

    fetch('get_invoices.php?class_id=' + encodeURIComponent(classId))
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('invoices_tbody');
            tbody.innerHTML = '';

            if (data.invoices.length === 0) {
                document.getElementById('no_invoices_msg').style.display = 'block';
                document.getElementById('invoices_list').style.display = 'block';
                return;
            }

            document.getElementById('no_invoices_msg').style.display = 'none';
            document.getElementById('selected_class_name').innerText = data.class_name;
            window.currentInvoices = data.invoices;

            data.invoices.forEach(invoice => {
                const statusColor = invoice.status === 'Paid' ? '#10b981' : 
                                  invoice.status === 'Partially Paid' ? '#f59e0b' : '#ef4444';
                const row = `<tr>
                    <td data-label="Student">${invoice.student_name}</td>
                    <td data-label="Invoice No." style="color: #00d4ff; font-weight: bold;">${invoice.invoice_number}</td>
                    <td data-label="Title">${invoice.title}</td>
                    <td data-label="Amount">Rs. ${parseFloat(invoice.amount).toLocaleString('en-PK', {minimumFractionDigits: 2})}</td>
                    <td data-label="Due Date">${invoice.due_date}</td>
                    <td data-label="Status" style="color: ${statusColor}; font-weight: bold;">${invoice.status}</td>
                    <td data-label="Action"><button class="btn btn-reset" onclick="deleteInvoice(${invoice.id})" style="background: #ef4444; padding: 0.5rem 1rem; border-radius: 4px; color: white; border: none; cursor: pointer;">Delete</button></td>
                </tr>`;
                tbody.insertAdjacentHTML('beforeend', row);
            });

            document.getElementById('invoices_list').style.display = 'block';
        })
        .catch(error => {
            console.error('Error loading invoices:', error);
            alert('Failed to load invoices. Please try again.');
        });
}

/**
 * Delete an invoice with confirmation
 */
function deleteInvoice(invoiceId) {
    if (confirm('Are you sure you want to delete this invoice? This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('action', 'delete_invoice');
        formData.append('invoice_id', invoiceId);
        formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);

        fetch('fee_backend.php', { 
            method: 'POST', 
            body: formData 
        })
        .then(response => response.text())
        .then(text => {
            console.log('Raw response:', text);
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    alert(data.message);
                    filterInvoicesByClass();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response text:', text);
                alert('Server error. Check console for details.');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Network error: ' + error.message);
        });
    }
}

/**
 * Sort invoices by student, amount, or status
 */
function sortInvoices(by) {
    const invoices = window.currentInvoices || [];
    
    if (invoices.length === 0) {
        alert('No invoices to sort');
        return;
    }

    if (by === 'student') {
        invoices.sort((a, b) => a.student_name.localeCompare(b.student_name));
    } else if (by === 'amount') {
        invoices.sort((a, b) => parseFloat(b.amount) - parseFloat(a.amount));
    } else if (by === 'status') {
        const statusOrder = {'Paid': 1, 'Partially Paid': 2, 'Unpaid': 3, 'Overdue': 4};
        invoices.sort((a, b) => (statusOrder[a.status] || 99) - (statusOrder[b.status] || 99));
    }

    const tbody = document.getElementById('invoices_tbody');
    tbody.innerHTML = '';
    
    invoices.forEach(invoice => {
        const statusColor = invoice.status === 'Paid' ? '#10b981' : 
                          invoice.status === 'Partially Paid' ? '#f59e0b' : '#ef4444';
        const row = `<tr>
            <td data-label="Student">${invoice.student_name}</td>
            <td data-label="Invoice No." style="color: #00d4ff; font-weight: bold;">${invoice.invoice_number}</td>
            <td data-label="Title">${invoice.title}</td>
            <td data-label="Amount">Rs. ${parseFloat(invoice.amount).toLocaleString('en-PK', {minimumFractionDigits: 2})}</td>
            <td data-label="Due Date">${invoice.due_date}</td>
            <td data-label="Status" style="color: ${statusColor}; font-weight: bold;">${invoice.status}</td>
            <td data-label="Action"><button class="btn btn-reset" onclick="deleteInvoice(${invoice.id})" style="background: #ef4444; padding: 0.5rem 1rem; border-radius: 4px; color: white; border: none; cursor: pointer;">Delete</button></td>
        </tr>`;
        tbody.insertAdjacentHTML('beforeend', row);
    });
}

/**
 * Switch between edit tabs (Bulk/Individual)
 */
function switchEditTab(tab) {
    document.querySelectorAll('.edit-tab').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.edit-content').forEach(content => content.style.display = 'none');
    
    const tabBtn = document.querySelector(`[data-tab="${tab}"]`);
    const tabContent = document.getElementById(tab + '-tab');
    
    if (tabBtn && tabContent) {
        tabBtn.classList.add('active');
        tabContent.style.display = 'block';
    }
}

/**
 * Load bulk invoice details and show warning count
 */
function loadBulkInvoiceDetails() {
    const select = document.getElementById('bulk_invoice_select');
    const selectedOption = select.options[select.selectedIndex];
    const count = selectedOption.getAttribute('data-count');
    
    if (count) {
        document.getElementById('bulk_student_count').innerText = count;
        document.getElementById('warn_count').innerText = count;
        document.getElementById('bulk_details').style.display = 'block';
    } else {
        document.getElementById('bulk_details').style.display = 'none';
    }
}

/**
 * Load student invoices for individual edit
 */
function loadStudentInvoices() {
    const studentId = document.getElementById('single_student_select').value;
    const invoiceSelect = document.getElementById('single_invoice_select');
    
    if (!studentId) {
        invoiceSelect.innerHTML = '<option value="">Select a student first</option>';
        document.getElementById('single_details').style.display = 'none';
        return;
    }

    invoiceSelect.innerHTML = '<option value="">Loading...</option>';

    fetch('get_student_invoices.php?student_id=' + encodeURIComponent(studentId))
        .then(response => response.json())
        .then(data => {
            invoiceSelect.innerHTML = '<option value="">-- Select Invoice --</option>';
            
            if (data.invoices.length === 0) {
                document.getElementById('single_details').style.display = 'none';
                return;
            }

            data.invoices.forEach(invoice => {
                const option = document.createElement('option');
                option.value = invoice.id;
                option.setAttribute('data-title', invoice.title);
                option.setAttribute('data-amount', invoice.amount);
                option.setAttribute('data-status', invoice.status);
                option.setAttribute('data-due-date', invoice.due_date);
                option.textContent = `${invoice.invoice_number} - ${invoice.title} - Rs. ${invoice.amount}`;
                invoiceSelect.appendChild(option);
            });

            invoiceSelect.onchange = loadSingleInvoiceDetails;
        })
        .catch(error => {
            console.error('Error loading invoices:', error);
            invoiceSelect.innerHTML = '<option value="">Error loading invoices</option>';
        });
}

/**
 * Load single invoice details when selected
 */
function loadSingleInvoiceDetails() {
    const select = document.getElementById('single_invoice_select');
    const selectedOption = select.options[select.selectedIndex];
    
    if (!selectedOption.value) {
        document.getElementById('single_details').style.display = 'none';
        return;
    }

    const title = selectedOption.getAttribute('data-title');
    const amount = selectedOption.getAttribute('data-amount');
    const status = selectedOption.getAttribute('data-status');
    const dueDate = selectedOption.getAttribute('data-due-date');

    if (title && amount) {
        document.getElementById('single_invoice_title').innerText = title;
        document.getElementById('single_invoice_amount').innerText = 'Rs. ' + parseFloat(amount).toLocaleString('en-PK', {minimumFractionDigits: 2});
        document.getElementById('single_invoice_status').innerText = status;
        document.getElementById('single_details').style.display = 'block';
    } else {
        document.getElementById('single_details').style.display = 'none';
    }
}