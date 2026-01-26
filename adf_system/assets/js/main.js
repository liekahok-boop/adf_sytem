// ============================================
// NARAYANA HOTEL MANAGEMENT SYSTEM
// Main JavaScript - Modern & Interactive
// ============================================

// Utility Functions
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
};

const formatDate = (date) => {
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    }).format(new Date(date));
};

// Show Toast Notification
const showToast = (message, type = 'success') => {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        background: ${type === 'success' ? '#10b981' : '#ef4444'};
        color: white;
        border-radius: 0.75rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        z-index: 9999;
        animation: slideIn 0.3s ease-out;
        font-weight: 600;
    `;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
};

// Confirm Dialog
const confirmDialog = (message, callback) => {
    if (confirm(message)) {
        callback();
    }
};

// Toggle Sidebar (Mobile)
const toggleSidebar = () => {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('show');
};

// Toggle Dropdown Menu
const setupDropdownToggles = () => {
    console.log('🔧 Setting up dropdown toggles...');
    const dropdownToggles = document.querySelectorAll('.nav-link.dropdown-toggle');
    console.log('Found dropdown toggles:', dropdownToggles.length);
    
    dropdownToggles.forEach((toggle, index) => {
        console.log('Attaching click handler to dropdown #' + index);
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Dropdown clicked!');
            const navItem = this.closest('.nav-item.has-submenu');
            
            if (navItem) {
                console.log('Found nav-item.has-submenu');
                // Close other open dropdowns at same level
                const siblings = navItem.parentElement.querySelectorAll('.nav-item.has-submenu.open');
                siblings.forEach(sibling => {
                    if (sibling !== navItem) {
                        console.log('Closing sibling dropdown');
                        sibling.classList.remove('open');
                    }
                });
                
                // Toggle current dropdown
                console.log('Toggling current dropdown');
                navItem.classList.toggle('open');
            } else {
                console.log('ERROR: nav-item.has-submenu not found');
            }
        });
    });
    console.log('✅ Dropdown toggles setup complete');
};


// Initialize Date Pickers
const initDatePickers = () => {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        if (!input.value) {
            input.value = new Date().toISOString().split('T')[0];
        }
    });
};

// Auto-calculate in forms
const setupCalculations = () => {
    const amountInputs = document.querySelectorAll('.amount-input');
    amountInputs.forEach(input => {
        input.addEventListener('input', (e) => {
            // Format as user types
            let value = e.target.value.replace(/[^0-9]/g, '');
            e.target.value = value ? parseInt(value).toLocaleString('id-ID') : '';
        });
    });
};

// Real-time Search
const setupSearch = () => {
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('input', debounce((e) => {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        }, 300));
    }
};

// Debounce function
const debounce = (func, wait) => {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

// Filter Table by Date Range
const filterByDateRange = (startDate, endDate) => {
    const rows = document.querySelectorAll('tbody tr');
    const start = new Date(startDate);
    const end = new Date(endDate);
    
    rows.forEach(row => {
        const dateCell = row.querySelector('.date-cell');
        if (dateCell) {
            const rowDate = new Date(dateCell.dataset.date);
            const inRange = rowDate >= start && rowDate <= end;
            row.style.display = inRange ? '' : 'none';
        }
    });
};

// Export Table to CSV
const exportToCSV = (tableId, filename) => {
    const table = document.querySelector(`#${tableId}`);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const csvRow = [];
        cols.forEach(col => csvRow.push(col.textContent));
        csv.push(csvRow.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${filename}_${new Date().toISOString().split('T')[0]}.csv`;
    a.click();
    window.URL.revokeObjectURL(url);
};

// Print Table
const printTable = () => {
    window.print();
};

// Form Validation
const validateForm = (formId) => {
    const form = document.querySelector(`#${formId}`);
    if (!form) return false;
    
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.style.borderColor = '#ef4444';
            isValid = false;
        } else {
            field.style.borderColor = '';
        }
    });
    
    if (!isValid) {
        showToast('Mohon lengkapi semua field yang wajib diisi', 'error');
    }
    
    return isValid;
};

// AJAX Request Helper
const ajaxRequest = async (url, method = 'GET', data = null) => {
    try {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            }
        };
        
        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }
        
        const response = await fetch(url, options);
        return await response.json();
    } catch (error) {
        console.error('AJAX Error:', error);
        showToast('Terjadi kesalahan pada server', 'error');
        return null;
    }
};

// Update Dashboard Stats (Real-time)
const updateDashboardStats = async () => {
    const stats = await ajaxRequest('api/get-stats.php');
    if (stats) {
        document.querySelector('#total-income').textContent = formatCurrency(stats.income);
        document.querySelector('#total-expense').textContent = formatCurrency(stats.expense);
        document.querySelector('#balance').textContent = formatCurrency(stats.balance);
    }
};

// Chart Helper (for future Chart.js integration)
const initCharts = () => {
    // Chart initialization will go here
    console.log('Charts initialized');
};

// Initialize on DOM Load
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 Narayana Hotel Management System Initialized');
    
    // Initialize components
    initDatePickers();
    setupCalculations();
    setupSearch();
    setupDropdownToggles(); // Add dropdown toggle handler
    
    // Add smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
    
    // Auto-refresh dashboard every 5 minutes
    if (document.querySelector('.dashboard-grid')) {
        setInterval(updateDashboardStats, 300000);
    }
});

// Add CSS for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
