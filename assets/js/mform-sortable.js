/**
 * MForm Sortable MultiSelect Integration
 * Adds sortable functionality to multiselect fields
 * @author MForm Team
 */

function initMFormSortableMultiSelect() {
    // Check if Sortable library is available (like SortableJS)
    // If not available, provide basic drag and drop functionality
    
    document.querySelectorAll('.mform-sortable-multiselect').forEach(function(selectElement) {
        if (selectElement.hasAttribute('data-sortable-initialized')) {
            return; // Already initialized
        }
        
        selectElement.setAttribute('data-sortable-initialized', 'true');
        
        // Create wrapper container for the sortable list
        const wrapper = document.createElement('div');
        wrapper.className = 'mform-sortable-wrapper';
        selectElement.parentNode.insertBefore(wrapper, selectElement);
        
        // Create sortable list container
        const sortableList = document.createElement('ul');
        sortableList.className = 'mform-sortable-list';
        
        // Create add/remove interface
        const controls = document.createElement('div');
        controls.className = 'mform-sortable-controls';
        
        const availableSelect = document.createElement('select');
        availableSelect.className = 'form-control mform-available-options';
        availableSelect.size = Math.min(selectElement.options.length, 8);
        
        const addButton = document.createElement('button');
        addButton.type = 'button';
        addButton.className = 'btn btn-primary btn-sm mform-add-option';
        addButton.innerHTML = '→ Add';
        
        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'btn btn-secondary btn-sm mform-remove-option';
        removeButton.innerHTML = '← Remove';
        
        // Populate available options
        for (let i = 0; i < selectElement.options.length; i++) {
            const option = selectElement.options[i];
            const newOption = document.createElement('option');
            newOption.value = option.value;
            newOption.textContent = option.textContent;
            availableSelect.appendChild(newOption);
        }
        
        // Hide original select
        selectElement.style.display = 'none';
        
        // Build interface
        const leftPanel = document.createElement('div');
        leftPanel.className = 'mform-sortable-panel mform-available-panel';
        leftPanel.innerHTML = '<label>Available Options:</label>';
        leftPanel.appendChild(availableSelect);
        
        const middlePanel = document.createElement('div');
        middlePanel.className = 'mform-sortable-panel mform-controls-panel';
        middlePanel.appendChild(addButton);
        middlePanel.appendChild(removeButton);
        
        const rightPanel = document.createElement('div');
        rightPanel.className = 'mform-sortable-panel mform-selected-panel';
        rightPanel.innerHTML = '<label>Selected Options (drag to reorder):</label>';
        rightPanel.appendChild(sortableList);
        
        controls.appendChild(leftPanel);
        controls.appendChild(middlePanel);
        controls.appendChild(rightPanel);
        
        wrapper.appendChild(controls);
        wrapper.appendChild(selectElement);
        
        // Initialize with selected values
        updateSortableList();
        
        // Event handlers
        addButton.addEventListener('click', function() {
            const selectedOption = availableSelect.options[availableSelect.selectedIndex];
            if (selectedOption) {
                addToSortableList(selectedOption.value, selectedOption.textContent);
                updateOriginalSelect();
            }
        });
        
        removeButton.addEventListener('click', function() {
            const selectedItem = sortableList.querySelector('.selected');
            if (selectedItem) {
                selectedItem.remove();
                updateOriginalSelect();
            }
        });
        
        // Make list sortable with basic drag and drop
        makeSortable(sortableList);
        
        function addToSortableList(value, text) {
            // Check if already exists
            const existing = sortableList.querySelector(`[data-value="${value}"]`);
            if (existing) return;
            
            const li = document.createElement('li');
            li.className = 'mform-sortable-item';
            li.setAttribute('data-value', value);
            li.setAttribute('draggable', 'true');
            li.innerHTML = `
                <span class="mform-item-handle">⋮⋮</span>
                <span class="mform-item-text">${text}</span>
            `;
            
            li.addEventListener('click', function() {
                // Remove previous selection
                sortableList.querySelectorAll('.selected').forEach(item => item.classList.remove('selected'));
                // Select this item
                this.classList.add('selected');
            });
            
            sortableList.appendChild(li);
        }
        
        function updateSortableList() {
            sortableList.innerHTML = '';
            for (let i = 0; i < selectElement.options.length; i++) {
                const option = selectElement.options[i];
                if (option.selected) {
                    addToSortableList(option.value, option.textContent);
                }
            }
        }
        
        function updateOriginalSelect() {
            // Clear all selections
            for (let i = 0; i < selectElement.options.length; i++) {
                selectElement.options[i].selected = false;
            }
            
            // Set selections based on sortable list order
            const items = sortableList.querySelectorAll('.mform-sortable-item');
            items.forEach(function(item) {
                const value = item.getAttribute('data-value');
                const option = selectElement.querySelector(`option[value="${value}"]`);
                if (option) {
                    option.selected = true;
                }
            });
            
            // Trigger change event
            selectElement.dispatchEvent(new Event('change'));
        }
        
        function makeSortable(list) {
            let draggedElement = null;
            
            list.addEventListener('dragstart', function(e) {
                draggedElement = e.target;
                e.target.style.opacity = '0.5';
            });
            
            list.addEventListener('dragend', function(e) {
                e.target.style.opacity = '';
                draggedElement = null;
                updateOriginalSelect();
            });
            
            list.addEventListener('dragover', function(e) {
                e.preventDefault();
            });
            
            list.addEventListener('drop', function(e) {
                e.preventDefault();
                if (draggedElement && e.target.classList.contains('mform-sortable-item')) {
                    const items = Array.from(list.children);
                    const draggedIndex = items.indexOf(draggedElement);
                    const targetIndex = items.indexOf(e.target);
                    
                    if (draggedIndex > targetIndex) {
                        list.insertBefore(draggedElement, e.target);
                    } else {
                        list.insertBefore(draggedElement, e.target.nextSibling);
                    }
                }
            });
        }
    });
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    initMFormSortableMultiSelect();
});

// Re-initialize when content is dynamically added (for repeater elements)
document.addEventListener('mform:repeater:added', function() {
    initMFormSortableMultiSelect();
});

// Export for manual initialization
window.initMFormSortableMultiSelect = initMFormSortableMultiSelect;