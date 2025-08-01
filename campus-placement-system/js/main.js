// Campus Placement Management System - Main JavaScript

// Utility Functions
const Utils = {
    // Show alert messages
    showAlert: function(message, type = 'info') {
        const alertContainer = document.getElementById('alert-container') || this.createAlertContainer();
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} fade-in`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">×</button>
        `;
        
        alertContainer.appendChild(alert);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alert.parentElement) {
                alert.remove();
            }
        }, 5000);
    },
    
    createAlertContainer: function() {
        const container = document.createElement('div');
        container.id = 'alert-container';
        container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
        document.body.appendChild(container);
        return container;
    },
    
    // Format date
    formatDate: function(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    },
    
    // Format currency
    formatCurrency: function(amount) {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR'
        }).format(amount);
    },
    
    // Validate email
    validateEmail: function(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },
    
    // Validate phone
    validatePhone: function(phone) {
        const re = /^[6-9]\d{9}$/;
        return re.test(phone);
    },
    
    // Show loading spinner
    showLoading: function(element) {
        const spinner = document.createElement('div');
        spinner.className = 'spinner';
        spinner.id = 'loading-spinner';
        element.appendChild(spinner);
    },
    
    hideLoading: function() {
        const spinner = document.getElementById('loading-spinner');
        if (spinner) {
            spinner.remove();
        }
    },
    
    // AJAX helper
    ajax: function(url, method = 'GET', data = null) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open(method, url);
            xhr.setRequestHeader('Content-Type', 'application/json');
            
            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        resolve(response);
                    } catch (e) {
                        resolve(xhr.responseText);
                    }
                } else {
                    reject(new Error(`HTTP ${xhr.status}: ${xhr.statusText}`));
                }
            };
            
            xhr.onerror = function() {
                reject(new Error('Network error'));
            };
            
            xhr.send(data ? JSON.stringify(data) : null);
        });
    }
};

// Modal Management
const Modal = {
    show: function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    },
    
    hide: function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
        }
    },
    
    hideAll: function() {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.classList.remove('show');
        });
        document.body.style.overflow = 'auto';
    }
};

// Form Validation
const FormValidator = {
    validate: function(form) {
        const errors = [];
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        
        inputs.forEach(input => {
            const value = input.value.trim();
            const fieldName = input.getAttribute('data-field') || input.name || input.id;
            
            // Required field validation
            if (!value) {
                errors.push(`${fieldName} is required`);
                input.classList.add('error');
            } else {
                input.classList.remove('error');
                
                // Specific validations
                if (input.type === 'email' && !Utils.validateEmail(value)) {
                    errors.push(`Please enter a valid email address`);
                    input.classList.add('error');
                }
                
                if (input.type === 'tel' && !Utils.validatePhone(value)) {
                    errors.push(`Please enter a valid phone number`);
                    input.classList.add('error');
                }
                
                if (input.type === 'password' && value.length < 6) {
                    errors.push(`Password must be at least 6 characters long`);
                    input.classList.add('error');
                }
            }
        });
        
        return {
            isValid: errors.length === 0,
            errors: errors
        };
    },
    
    showErrors: function(errors) {
        const errorMessage = errors.join('<br>');
        Utils.showAlert(errorMessage, 'error');
    }
};

// Authentication
const Auth = {
    login: function(email, password) {
        return Utils.ajax('api/login.php', 'POST', { email, password });
    },
    
    logout: function() {
        return Utils.ajax('api/logout.php', 'POST');
    },
    
    checkSession: function() {
        return Utils.ajax('api/check-session.php');
    }
};

// Data Tables
const DataTable = {
    init: function(tableId, options = {}) {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        // Add search functionality
        this.addSearch(table, options.searchInputId);
        
        // Add sorting
        this.addSorting(table);
        
        // Add pagination
        if (options.pagination) {
            this.addPagination(table, options.pageSize || 10);
        }
    },
    
    addSearch: function(table, searchInputId) {
        const searchInput = document.getElementById(searchInputId);
        if (!searchInput) return;
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    },
    
    addSorting: function(table) {
        const headers = table.querySelectorAll('th[data-sort]');
        
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                const column = this.getAttribute('data-sort');
                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));
                const isAscending = this.classList.contains('sort-asc');
                
                // Remove sort classes from all headers
                headers.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
                
                // Add sort class to current header
                this.classList.add(isAscending ? 'sort-desc' : 'sort-asc');
                
                // Sort rows
                rows.sort((a, b) => {
                    const aValue = a.querySelector(`[data-column="${column}"]`).textContent.trim();
                    const bValue = b.querySelector(`[data-column="${column}"]`).textContent.trim();
                    
                    if (isAscending) {
                        return bValue.localeCompare(aValue);
                    } else {
                        return aValue.localeCompare(bValue);
                    }
                });
                
                // Reorder rows in DOM
                rows.forEach(row => tbody.appendChild(row));
            });
        });
    },
    
    addPagination: function(table, pageSize) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const totalPages = Math.ceil(rows.length / pageSize);
        let currentPage = 1;
        
        // Create pagination container
        const paginationContainer = document.createElement('div');
        paginationContainer.className = 'pagination-container d-flex justify-center mt-3';
        table.parentElement.appendChild(paginationContainer);
        
        // Show page function
        function showPage(page) {
            currentPage = page;
            const start = (page - 1) * pageSize;
            const end = start + pageSize;
            
            rows.forEach((row, index) => {
                row.style.display = (index >= start && index < end) ? '' : 'none';
            });
            
            updatePaginationButtons();
        }
        
        // Update pagination buttons
        function updatePaginationButtons() {
            paginationContainer.innerHTML = '';
            
            // Previous button
            const prevBtn = document.createElement('button');
            prevBtn.className = `btn btn-sm ${currentPage === 1 ? 'btn-secondary' : 'btn-primary'}`;
            prevBtn.textContent = 'Previous';
            prevBtn.disabled = currentPage === 1;
            prevBtn.onclick = () => showPage(currentPage - 1);
            paginationContainer.appendChild(prevBtn);
            
            // Page buttons
            for (let i = 1; i <= totalPages; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.className = `btn btn-sm ${i === currentPage ? 'btn-primary' : 'btn-outline'}`;
                pageBtn.textContent = i;
                pageBtn.onclick = () => showPage(i);
                paginationContainer.appendChild(pageBtn);
            }
            
            // Next button
            const nextBtn = document.createElement('button');
            nextBtn.className = `btn btn-sm ${currentPage === totalPages ? 'btn-secondary' : 'btn-primary'}`;
            nextBtn.textContent = 'Next';
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.onclick = () => showPage(currentPage + 1);
            paginationContainer.appendChild(nextBtn);
        }
        
        // Initialize
        showPage(1);
    }
};

// File Upload
const FileUpload = {
    init: function(inputId, options = {}) {
        const input = document.getElementById(inputId);
        if (!input) return;
        
        const allowedTypes = options.allowedTypes || ['pdf', 'doc', 'docx'];
        const maxSize = options.maxSize || 5 * 1024 * 1024; // 5MB
        
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;
            
            // Validate file type
            const fileExtension = file.name.split('.').pop().toLowerCase();
            if (!allowedTypes.includes(fileExtension)) {
                Utils.showAlert(`Please select a file with one of these extensions: ${allowedTypes.join(', ')}`, 'error');
                this.value = '';
                return;
            }
            
            // Validate file size
            if (file.size > maxSize) {
                Utils.showAlert(`File size must be less than ${Math.round(maxSize / 1024 / 1024)}MB`, 'error');
                this.value = '';
                return;
            }
            
            // Show file name
            const label = this.parentElement.querySelector('.file-upload-label');
            if (label) {
                label.textContent = file.name;
            }
        });
    }
};

// Notifications
const Notifications = {
    load: function() {
        Utils.ajax('api/notifications.php')
            .then(response => {
                if (response.success) {
                    this.display(response.notifications);
                }
            })
            .catch(error => {
                console.error('Failed to load notifications:', error);
            });
    },
    
    display: function(notifications) {
        const container = document.getElementById('notifications-container');
        if (!container) return;
        
        container.innerHTML = '';
        
        notifications.forEach(notification => {
            const notificationElement = document.createElement('div');
            notificationElement.className = `notification-item ${notification.is_read ? 'read' : 'unread'}`;
            notificationElement.innerHTML = `
                <div class="notification-title">${notification.title}</div>
                <div class="notification-message">${notification.message}</div>
                <div class="notification-time">${Utils.formatDate(notification.created_at)}</div>
            `;
            
            if (!notification.is_read) {
                notificationElement.addEventListener('click', () => {
                    this.markAsRead(notification.notification_id);
                });
            }
            
            container.appendChild(notificationElement);
        });
    },
    
    markAsRead: function(notificationId) {
        Utils.ajax('api/mark-notification-read.php', 'POST', { notification_id: notificationId })
            .then(response => {
                if (response.success) {
                    this.load(); // Reload notifications
                }
            });
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize modals
    document.querySelectorAll('[data-modal]').forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const modalId = this.getAttribute('data-modal');
            Modal.show(modalId);
        });
    });
    
    // Close modals when clicking outside
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                Modal.hide(this.id);
            }
        });
    });
    
    // Close modals with close button
    document.querySelectorAll('.modal-close').forEach(closeBtn => {
        closeBtn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                Modal.hide(modal.id);
            }
        });
    });
    
    // Initialize forms
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const validation = FormValidator.validate(this);
            if (!validation.isValid) {
                FormValidator.showErrors(validation.errors);
                return;
            }
            
            // Submit form if valid
            this.submit();
        });
    });
    
    // Initialize file uploads
    document.querySelectorAll('input[type="file"]').forEach(input => {
        FileUpload.init(input.id);
    });
    
    // Initialize data tables
    document.querySelectorAll('.data-table').forEach(table => {
        DataTable.init(table.id, {
            searchInputId: table.getAttribute('data-search'),
            pagination: table.hasAttribute('data-pagination'),
            pageSize: parseInt(table.getAttribute('data-page-size')) || 10
        });
    });
    
    // Load notifications if container exists
    if (document.getElementById('notifications-container')) {
        Notifications.load();
    }
    
    // Auto-hide alerts
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            if (alert.parentElement) {
                alert.remove();
            }
        }, 5000);
    });
});

// Export for global use
window.Utils = Utils;
window.Modal = Modal;
window.FormValidator = FormValidator;
window.Auth = Auth;
window.DataTable = DataTable;
window.FileUpload = FileUpload;
window.Notifications = Notifications;