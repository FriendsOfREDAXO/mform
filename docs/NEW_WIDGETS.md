# New MForm Widgets - Usage Guide

This document explains how to use the new widgets added to MForm in response to issue #253.

## DateTime Picker (using flatpickr.js)

The DateTime picker provides three methods for different date/time input needs:

### Requirements
To use the DateTime picker widgets, you need to include the flatpickr.js library in your project:

```html
<!-- Include in your template head -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
```

### Usage Examples

```php
// Full DateTime picker
$mform->addDateTimeField(1, ['label' => 'Event Date & Time']);

// Date only
$mform->addDateField(2, ['label' => 'Birth Date']);

// Time only  
$mform->addTimeField(3, ['label' => 'Meeting Time']);

// Custom format
$mform->addDateTimeField(4, [
    'label' => 'Custom Format',
    'data-date-format' => 'd.m.Y H:i'
], 'd.m.Y H:i');

// With min/max dates
$mform->addDateField(5, [
    'label' => 'Select Date',
    'data-min-date' => '2024-01-01',
    'data-max-date' => '2024-12-31'
]);
```

### Configuration Options

Available data attributes:
- `data-date-format`: Date format (default: 'Y-m-d H:i')
- `data-enable-time`: Enable time picker (default: 'true')
- `data-no-calendar`: Time only mode (default: 'false')
- `data-min-date`: Minimum selectable date
- `data-max-date`: Maximum selectable date
- `data-default-date`: Default date value
- `data-locale`: Locale for date picker

## Checkbox with Images

Create checkboxes with image previews, similar to the existing radio image field.

### Usage

```php
$options = [
    'layout1' => ['img' => '/path/to/image1.jpg', 'label' => 'Layout 1'],
    'layout2' => ['img' => '/path/to/image2.jpg', 'label' => 'Layout 2'],
    'layout3' => ['img' => '/path/to/image3.jpg', 'label' => 'Layout 3']
];

$mform->addCheckboxImgField(1, $options, ['label' => 'Select Layouts']);
```

### Supported Image Formats

The widget supports the same formats as the radio image field:
- Direct image URLs: `['img' => 'url', 'label' => 'text']`
- Layout configs: `['config' => [...], 'label' => 'text']`
- SVG icon sets: `['svgIconSet' => '...', 'label' => 'text']`

## Sortable MultiSelect

A multiselect field with drag-and-drop reordering capabilities.

### Usage

```php
$options = [
    'opt1' => 'First Option',
    'opt2' => 'Second Option',
    'opt3' => 'Third Option',
    'opt4' => 'Fourth Option'
];

$mform->addSortableMultiSelectField(1, $options, [
    'label' => 'Sortable Options',
    'size' => 6  // Display height
]);
```

### Features

- Dual-panel interface (available options ↔ selected options)
- Drag-and-drop reordering of selected items
- Add/remove buttons for easy selection
- Maintains selection order in form submission
- Responsive design for mobile devices

## Multiple Checkbox Groups

Organize checkboxes into labeled groups for better UX.

### Usage

```php
$groups = [
    'features' => [
        'label' => 'Website Features',
        'options' => [
            'blog' => 'Blog System',
            'gallery' => 'Photo Gallery',
            'contact' => 'Contact Forms'
        ]
    ],
    'settings' => [
        'label' => 'Technical Settings', 
        'options' => [
            'ssl' => 'SSL Certificate',
            'cdn' => 'CDN Integration',
            'cache' => 'Advanced Caching'
        ]
    ]
];

$mform->addMultipleCheckboxField(1, $groups, ['label' => 'Configuration']);
```

### Data Structure

The form data will be structured as:
```php
REX_VALUE[1][features][blog] = 1      // if checked
REX_VALUE[1][settings][ssl] = 1       // if checked
```

### Features

- Organized into collapsible groups
- Grid layout for optimal space usage
- Individual group labels and styling
- Dark theme support
- Responsive design

## Styling and Theming

All new widgets include:
- Full CSS styling matching MForm's design
- Dark theme support for REDAXO backend
- Responsive design for mobile devices
- Hover and focus states
- Accessibility considerations

## Browser Compatibility

The widgets are designed to work with modern browsers that support:
- CSS Grid (for multiple checkbox groups)
- HTML5 Drag and Drop API (for sortable multiselect)
- ES6 JavaScript features

For older browser support, consider including appropriate polyfills.

## Migration Notes

These new widgets are additive and don't affect existing MForm functionality. All existing widgets continue to work as before.

The checkbox image widget follows the same pattern as the existing radio image widget, making it easy to convert between the two when needed.