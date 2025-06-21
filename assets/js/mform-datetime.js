/**
 * MForm DateTime Picker Integration
 * Integrates flatpickr datetime picker with MForm
 * @author MForm Team
 */

function initMFormDateTimePickers() {
    // Check if flatpickr is available
    if (typeof flatpickr === 'undefined') {
        console.warn('MForm DateTime: flatpickr library not loaded. Please include flatpickr.js and flatpickr.css');
        return;
    }

    // Initialize all datetime pickers
    document.querySelectorAll('.mform-datetime-picker').forEach(function(element) {
        const config = {
            enableTime: true,
            dateFormat: element.dataset.dateFormat || 'Y-m-d H:i',
            time_24hr: element.dataset.time24hr !== 'false',
            allowInput: true,
            clickOpens: true
        };

        // Parse additional config from data attributes
        if (element.dataset.enableTime === 'false') {
            config.enableTime = false;
            config.dateFormat = element.dataset.dateFormat || 'Y-m-d';
        }

        if (element.dataset.noCalendar === 'true') {
            config.noCalendar = true;
            config.enableTime = true;
            config.dateFormat = element.dataset.dateFormat || 'H:i';
        }

        if (element.dataset.minDate) {
            config.minDate = element.dataset.minDate;
        }

        if (element.dataset.maxDate) {
            config.maxDate = element.dataset.maxDate;
        }

        if (element.dataset.defaultDate) {
            config.defaultDate = element.dataset.defaultDate;
        }

        if (element.dataset.locale) {
            config.locale = element.dataset.locale;
        }

        // Initialize flatpickr
        const picker = flatpickr(element, config);

        // Store reference for potential future access
        element._flatpickr = picker;
    });
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    initMFormDateTimePickers();
});

// Re-initialize when content is dynamically added (for repeater elements)
document.addEventListener('mform:repeater:added', function() {
    initMFormDateTimePickers();
});

// Export for manual initialization
window.initMFormDateTimePickers = initMFormDateTimePickers;