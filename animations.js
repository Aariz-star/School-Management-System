/* ============================================
   SMOOTH ANIMATIONS & TRANSITIONS JS
   CMS Dashboard - Interactive Effects
   ============================================ */

// ─── Initialize Animations on Page Load ───
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded fired for animations.js');
    initializeAnimations();
    setupButtonRipples();
    setupFormTransitions();
    setupLogoutAnimation();
    setupTableRowAnimations();
    
    // Call chart initialization after scripts fully load
    setTimeout(function() {
        console.log('DOMContentLoaded afterDelay - checking for chart init function');
        if (typeof window.initChartsWhenReady === 'function') {
            console.log('Calling window.initChartsWhenReady()');
            window.initChartsWhenReady();
        } else if (typeof initDashboardCharts === 'function') {
            console.log('initChartsWhenReady not found, calling initDashboardCharts directly');
            initDashboardCharts();
        }
    }, 100);
});

/* ============================================
   BUTTON RIPPLE EFFECT
   ============================================ */
function setupButtonRipples() {
    const buttons = document.querySelectorAll(
        'button, .nav-btn, .submit-btn, .btn, a.nav-btn'
    );

    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            createRipple(e, this);
        });
    });
}

function createRipple(event, element) {
    const ripple = document.createElement('span');
    const rect = element.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;

    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = x + 'px';
    ripple.style.top = y + 'px';
    ripple.classList.add('ripple-element');

    // Remove previous ripple if exists
    const existingRipple = element.querySelector('.ripple-element');
    if (existingRipple) {
        existingRipple.remove();
    }

    element.appendChild(ripple);

    // Add animation class
    setTimeout(() => ripple.classList.add('ripple-animate'), 0);

    // Remove ripple after animation
    setTimeout(() => ripple.remove(), 600);
}

/* ============================================
   FORM TRANSITION ANIMATIONS
   ============================================ */
function initializeAnimations() {
    // Ensure forms are properly initialized
    const formContent = document.querySelectorAll('.form-content');
    let activeFormFound = false;
    
    formContent.forEach(form => {
        // Remove any inline display styles to let CSS handle it
        form.style.display = '';
        
        // Check if this is the active form
        if (form.classList.contains('active')) {
            activeFormFound = true;
        }
    });
    
    // If no active form found, show dashboard_view by default
    if (!activeFormFound) {
        const dashboardForm = document.getElementById('dashboard_view');
        if (dashboardForm) {
            dashboardForm.classList.add('active');
            // Force chart initialization after dashboard is set active
            setTimeout(() => {
                console.log('[animations.js] Forcing chart initialization after dashboard set active');
                if (typeof initDashboardCharts === 'function') {
                    try {
                        initDashboardCharts();
                        console.log('[animations.js] Charts initialized successfully');
                    } catch(e) {
                        console.error('[animations.js] Chart initialization error:', e);
                    }
                } else {
                    console.warn('[animations.js] initDashboardCharts not available');
                }
            }, 500);
        }
    } else {
        // If dashboard is already active, force chart initialization
        const dashboardView = document.getElementById('dashboard_view');
        if (dashboardView && dashboardView.classList.contains('active')) {
            setTimeout(() => {
                console.log('[animations.js] Forcing chart initialization (dashboard already active)');
                if (typeof initDashboardCharts === 'function') {
                    try {
                        initDashboardCharts();
                        console.log('[animations.js] Charts initialized successfully');
                    } catch(e) {
                        console.error('[animations.js] Chart initialization error:', e);
                    }
                } else {
                    console.warn('[animations.js] initDashboardCharts not available');
                }
            }, 500);
        }
    }
}

function showForm(formId) {
    // Hide all forms
    const forms = document.querySelectorAll('.form-content');
    let hasActiveForm = false;
    
    forms.forEach(form => {
        if (form.id === formId) return; // Skip animating out the form we are trying to show

        if (form.classList.contains('active')) {
            hasActiveForm = true;
            form.style.animation = 'slideOutLeft 0.4s ease-in forwards';
            setTimeout(() => {
                form.classList.remove('active');
                form.style.animation = '';
            }, 400);
        } else {
            form.classList.remove('active');
        }
    });

    // Show selected form
    const selectedForm = document.getElementById(formId);
    if (selectedForm) {
        const wasAlreadyActive = selectedForm.classList.contains('active');
        selectedForm.classList.add('active');
        // Only apply animation if switching from another form
        if (hasActiveForm && !wasAlreadyActive) {
            selectedForm.style.animation = 'slideInUp 0.5s ease-out';
            setTimeout(() => {
                selectedForm.style.animation = '';
            }, 500);
        }
    }

    // Update Active Button State
    const navBtns = document.querySelectorAll('.nav-btn');
    navBtns.forEach(btn => btn.classList.remove('active'));
    const activeBtn = document.querySelector(`.nav-btn[onclick*="showForm('${formId}')"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
    }

    // Optimize: Only initialize charts if Dashboard is active, otherwise destroy them
    if (formId === 'dashboard_view') {
        setTimeout(() => {
            if (typeof initDashboardCharts === 'function') {
                try {
                    initDashboardCharts();
                } catch(e) {
                    console.log('Error initializing charts:', e.message);
                }
            }
        }, 200);
    } else if (typeof destroyDashboardCharts === 'function') {
        try {
            destroyDashboardCharts();
        } catch(e) {
            console.log('Error destroying charts:', e.message);
        }
    }
    
    // Close sidebar on mobile if open
    const sidebar = document.getElementById('sidebar');
    if (sidebar && sidebar.classList.contains('active')) {
        toggleSidebar();
    }
    
    // Scroll to top
    setTimeout(() => {
        const container = document.querySelector('.container');
        if (container) {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    }, 100);
}

function updateActiveNav(formId) {
    const navButtons = document.querySelectorAll('.nav-btn');
    navButtons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('onclick')?.includes(formId)) {
            btn.classList.add('active');
        }
    });
}

/* ============================================
   SIDEBAR TOGGLE ANIMATIONS
   ============================================ */
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (!sidebar) return;

    // Check if sidebar is open
    const isOpen = sidebar.classList.contains('active');

    if (isOpen) {
        closeSidebar();
    } else {
        openSidebar();
    }
}

function openSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (!sidebar) return;

    sidebar.classList.add('active');
    overlay.classList.add('active');
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
}

function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (!sidebar) return;

    sidebar.classList.remove('active');
    overlay.classList.remove('active');
    
    // Restore body scroll
    document.body.style.overflow = '';
}

/* ============================================
   FORM TRANSITIONS FOR SUBSECTIONS
   ============================================ */
function toggleSubSection(sectionId) {
    const section = document.getElementById(sectionId);
    const allSubSections = document.querySelectorAll(
        '[id^="sub_"]'
    );

    if (!section) return;

    // Hide all other subsections
    allSubSections.forEach(subsection => {
        if (subsection.id === sectionId) return;
        
        if (!subsection.classList.contains('hidden')) {
            subsection.style.animation = 'slideOutLeft 0.3s ease-in forwards';
            
            setTimeout(() => {
                subsection.classList.add('hidden');
                subsection.style.animation = '';
                subsection.style.display = 'none';
            }, 300);
        }
    });

    // Show target subsection
    if (section.classList.contains('hidden')) {
        section.classList.remove('hidden');
        section.style.display = 'block';
        section.style.animation = 'slideInUp 0.4s ease-out';
        
        // Smooth scroll to section
        setTimeout(() => {
            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    }
}

/* ============================================
   LOGOUT ANIMATION & CONFIRMATION
   ============================================ */
function setupLogoutAnimation() {
    const logoutButtons = document.querySelectorAll('a[href="logout.php"]');
    
    logoutButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            showLogoutConfirmation();
        });
    });
}

function showLogoutConfirmation() {
    // Create modal overlay
    const overlay = document.createElement('div');
    overlay.className = 'logout-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2000;
        animation: fadeIn 0.3s ease-out;
    `;

    // Create modal
    const modal = document.createElement('div');
    modal.className = 'logout-modal';
    modal.style.cssText = `
        background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
        border: 1px solid rgba(0, 212, 255, 0.3);
        border-radius: 15px;
        padding: 2rem;
        text-align: center;
        max-width: 400px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        animation: scaleIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    `;

    // Modal content
    modal.innerHTML = `
        <h2 style="margin-top: 0; color: #00d4ff; margin-bottom: 1rem;">Confirm Logout</h2>
        <p style="color: #999; margin-bottom: 2rem;">Are you sure you want to logout?</p>
        <div style="display: flex; gap: 1rem; justify-content: center;">
            <button class="logout-cancel" style="
                padding: 0.8rem 2rem;
                border: none;
                border-radius: 8px;
                background: rgba(255, 255, 255, 0.1);
                color: #e0e0e0;
                font-size: 1rem;
                cursor: pointer;
                transition: all 0.3s ease;
                font-weight: 600;
            ">Cancel</button>
            <button class="logout-confirm" style="
                padding: 0.8rem 2rem;
                border: none;
                border-radius: 8px;
                background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                color: white;
                font-size: 1rem;
                cursor: pointer;
                transition: all 0.3s ease;
                font-weight: 600;
            ">Logout</button>
        </div>
    `;

    overlay.appendChild(modal);
    document.body.appendChild(overlay);

    // Cancel button
    modal.querySelector('.logout-cancel').addEventListener('click', function() {
        overlay.style.animation = 'fadeOut 0.3s ease-in forwards';
        setTimeout(() => overlay.remove(), 300);
    });

    // Confirm button
    modal.querySelector('.logout-confirm').addEventListener('click', function() {
        // Add fade out animation to entire page
        document.body.style.animation = 'fadeOut 0.5s ease-out forwards';
        
        setTimeout(() => {
            // Redirect to logout
            window.location.href = 'logout.php';
        }, 500);
    });

    // Close on overlay click
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            overlay.style.animation = 'fadeOut 0.3s ease-in forwards';
            setTimeout(() => overlay.remove(), 300);
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function handler(e) {
        if (e.key === 'Escape') {
            overlay.style.animation = 'fadeOut 0.3s ease-in forwards';
            setTimeout(() => {
                overlay.remove();
                document.removeEventListener('keydown', handler);
            }, 300);
        }
    });
}

/* ============================================
   SETUP FORM TRANSITIONS
   ============================================ */
function setupFormTransitions() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Add fade out before submission
            document.body.style.animation = 'fadeOut 0.3s ease-out forwards';
        });
    }
}

/* ============================================
   TABLE ROW ANIMATIONS ON LOAD
   ============================================ */
function setupTableRowAnimations() {
    const tables = document.querySelectorAll('table');
    tables.forEach(table => {
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach((row, index) => {
            row.style.animation = `slideInUp 0.4s ease-out ${index * 0.05}s backwards`;
        });
    });
}

/* ============================================
   SMOOTH NOTIFICATION ANIMATIONS
   ============================================ */
function setupNotifications() {
    const notifications = document.querySelectorAll('.notification');
    
    notifications.forEach(notification => {
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.4s ease-in forwards';
            setTimeout(() => {
                notification.remove();
            }, 400);
        }, 5000);

        // Click to dismiss
        notification.addEventListener('click', function() {
            this.style.animation = 'slideOutRight 0.4s ease-in forwards';
            setTimeout(() => {
                this.remove();
            }, 400);
        });
    });
}

document.addEventListener('DOMContentLoaded', setupNotifications);

/* ============================================
   SMOOTH PAGE TRANSITIONS BETWEEN ROUTES
   ============================================ */
document.addEventListener('click', function(e) {
    const link = e.target.closest('a[href*=".php"]');
    
    if (link && !link.href.includes('javascript:')) {
        // Skip animation for logout (has its own handling)
        if (link.href.includes('logout.php')) return;
        
        // Fade out animation
        document.body.style.animation = 'fadeOut 0.4s ease-out forwards';
    }
});

/* ============================================
   ANIMATE STATS CARDS ON SCROLL
   ============================================ */
function setupScrollAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'slideInUp 0.6s ease-out forwards';
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    // Observe stat cards and chart boxes
    document.querySelectorAll('.stat-card, .chart-box, table').forEach(el => {
        observer.observe(el);
    });
}

document.addEventListener('DOMContentLoaded', setupScrollAnimations);

/* ============================================
   ADD GLOW EFFECT ON BUTTON HOVER
   ============================================ */
function addGlowEffects() {
    const buttons = document.querySelectorAll('button, .nav-btn, .submit-btn, .btn');
    
    buttons.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.boxShadow = '0 0 20px rgba(0, 212, 255, 0.6)';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.boxShadow = '';
        });
    });
}

document.addEventListener('DOMContentLoaded', addGlowEffects);

/* ============================================
   FLOATING LABEL ANIMATION FOR INPUTS
   ============================================ */
function setupFloatingLabels() {
    const inputs = document.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        // Add focus animation
        input.addEventListener('focus', function() {
            this.style.transform = 'scale(1.02)';
        });
        
        input.addEventListener('blur', function() {
            if (!this.value) {
                this.style.transform = 'scale(1)';
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', setupFloatingLabels);

/* ============================================
   UTILITY FUNCTIONS
   ============================================ */

// Smooth scroll to element
function smoothScrollTo(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Add loading animation
function showLoadingAnimation(element) {
    if (!element) return;
    
    const spinner = document.createElement('div');
    spinner.className = 'loading-spinner';
    element.appendChild(spinner);
    
    return spinner;
}

// Remove loading animation
function hideLoadingAnimation(spinner) {
    if (spinner) {
        spinner.remove();
    }
}

/* ============================================
   HANDLE WINDOW RESIZE FOR RESPONSIVE ANIMATIONS
   ============================================ */
window.addEventListener('resize', function() {
    // Ensure sidebar is properly closed on resize to smaller screens
    if (window.innerWidth < 768) {
        closeSidebar();
    }
});

/* ============================================
   PASSWORD TOGGLE (For Login Page)
   ============================================ */
window.togglePassword = function() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');
    
    if (passwordInput && eyeIcon) {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07-2.3 2.3"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
        } else {
            passwordInput.type = 'password';
            eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
        }
    }
};

/* ============================================
   INITIALIZE ALL ANIMATIONS ON PAGE LOAD
   ============================================ */
window.addEventListener('load', function() {
    // Ensure all animations are set up
    setupButtonRipples();
    setupFormTransitions();
    setupLogoutAnimation();
    setupTableRowAnimations();
    setupNotifications();
    setupScrollAnimations();
    addGlowEffects();
    setupFloatingLabels();
});

// Export functions for global use
window.showForm = showForm;
window.toggleSidebar = toggleSidebar;
window.openSidebar = openSidebar;
window.closeSidebar = closeSidebar;
window.toggleSubSection = toggleSubSection;
window.smoothScrollTo = smoothScrollTo;

// Backup: Initialize charts on window load if not already done
window.addEventListener('load', function() {
    setTimeout(function() {
        // Check if charts exist on dashboard
        const dashboard = document.getElementById('dashboard_view');
        if (dashboard && dashboard.classList.contains('active')) {
            // Check if charts exist
            const attCanvas = document.getElementById('attendanceChart');
            const perfCanvas = document.getElementById('performanceChart');
            
            // If canvases exist but no chart data has been initialized
            if (attCanvas && perfCanvas && attCanvas.chart === undefined && perfCanvas.chart === undefined) {
                console.log('Forcing chart initialization on window load...');
                if (typeof initDashboardCharts === 'function') {
                    initDashboardCharts();
                }
            }
        }
    }, 500);
});
