/* Travel Agency Custom JavaScript */

// Format currency
function formatCurrency(amount) {
    return '$' + parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Format date
function formatDate(dateStr) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateStr).toLocaleDateString('en-US', options);
}

// Confirm action
function confirmAction(message) {
    return confirm(message || 'Are you sure?');
}

// Show toast notification
function showToast(message, type = 'info') {
    const alertClass = `alert alert-${type === 'error' ? 'danger' : type}`;
    const toast = document.createElement('div');
    toast.className = `${alertClass} alert-dismissible fade show`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    // Insert at top of main content
    const main = document.querySelector('main');
    if (main) {
        main.insertBefore(toast, main.firstChild);
    }

    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

// Validate email
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Validate phone
function validatePhone(phone) {
    const re = /^[\d\s\-\+\(\)]+$/;
    return re.test(phone) && phone.replace(/\D/g, '').length >= 10;
}

// Format input as currency
function formatCurrencyInput(event) {
    let value = event.target.value.replace(/\D/g, '');
    if (value) {
        value = (value / 100).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        event.target.value = value;
    }
}

// Auto-calculate total price
function calculateTotalPrice() {
    const pricePerPerson = parseFloat(document.getElementById('pricePerPerson')?.textContent || 0);
    const numPeople = parseInt(document.getElementById('number_of_people')?.value || 0);
    const total = pricePerPerson * numPeople;

    const totalEl = document.getElementById('totalPrice');
    if (totalEl) {
        totalEl.textContent = formatCurrency(total);
    }
}

// Format card expiry input (MM/YY)
function formatExpiryInput(event) {
    let value = event.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    event.target.value = value;
}

// Format card number with spaces
function formatCardNumber(event) {
    let value = event.target.value.replace(/\s/g, '');
    let formattedValue = value.replace(/(\d{4})/g, '$1 ').trim();
    event.target.value = formattedValue;
}

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Live search functionality
const liveSearch = debounce(function () {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        // Implement live search logic here
        console.log('Searching for:', searchInput.value);
    }
}, 300);

// Initialize tooltips and popovers
document.addEventListener('DOMContentLoaded', function () {
    // Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Add event listeners for input formatting
    const cardNumberInputs = document.querySelectorAll('input[name="card_number"]');
    cardNumberInputs.forEach(input => {
        input.addEventListener('input', formatCardNumber);
    });

    const expiryInputs = document.querySelectorAll('input[name="card_expiry"]');
    expiryInputs.forEach(input => {
        input.addEventListener('input', formatExpiryInput);
    });

    const currencyInputs = document.querySelectorAll('[data-format="currency"]');
    currencyInputs.forEach(input => {
        input.addEventListener('input', formatCurrencyInput);
    });

    // Calculate total price on input change
    const numberInput = document.getElementById('number_of_people');
    if (numberInput) {
        numberInput.addEventListener('input', calculateTotalPrice);
    }
});

// AJAX form submission
function submitFormAjax(formId, onSuccess) {
    const form = document.getElementById(formId);
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(form);
        const action = form.getAttribute('action') || form.getAttribute('data-action');

        fetch(action, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Success!', 'success');
                    if (typeof onSuccess === 'function') {
                        onSuccess(data);
                    }
                } else {
                    showToast(data.error || 'An error occurred', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred', 'error');
            });
    });
}

// Clear form
function clearForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.reset();
    }
}

// Scroll to element
function scrollToElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
}
