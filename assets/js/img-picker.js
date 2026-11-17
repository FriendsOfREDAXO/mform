/**
 * MForm Bild-Picker (MForm::addImgPickerField)
 *
 * Macht aus einem <select class="mform-img-picker"> ein kompaktes Feld mit Vorschaubild.
 * Ein Klick öffnet ein Fenster mit allen Optionen als Bildkacheln. Das <select> bleibt im Formular
 * und trägt den Wert: Speichern, visible_if/Conditional Fieldsets und der Flex-Repeater lesen es wie gewohnt.
 *
 * Tastatur: Pfeiltasten (im Raster), Pos1/Ende, Enter/Leertaste wählt, Esc schließt.
 */
(function () {
    'use strict';

    var counter = 0;
    var enhanced = typeof WeakSet === 'function' ? new WeakSet() : null;

    function uid(prefix) {
        counter += 1;
        return prefix + '-' + Date.now().toString(36) + '-' + counter;
    }

    function escapeAttr(value) {
        return String(value).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
    }

    function visual(meta) {
        if (meta.img) {
            return '<span class="mform-img-picker__img"><img src="' + escapeAttr(meta.img) + '" alt=""></span>';
        }
        if (meta.icon) {
            return '<span class="mform-img-picker__img mform-img-picker__img--icon"><i class="' + escapeAttr(meta.icon) + '" aria-hidden="true"></i></span>';
        }
        return '';
    }

    function findLabel(select) {
        var label = null;
        if (select.id && window.CSS && CSS.escape) {
            label = document.querySelector('label[for="' + CSS.escape(select.id) + '"]');
        }
        if (!label) {
            var group = select.closest('.form-group');
            label = group ? group.querySelector('label.control-label, .control-label') : null;
        }
        return label;
    }

    function enhance(select) {
        if (enhanced && enhanced.has(select)) {
            return;
        }
        if (select.closest('template')) {
            return;
        }
        if (enhanced) {
            enhanced.add(select);
        }

        var meta = {};
        try {
            meta = JSON.parse(select.getAttribute('data-mform-img-picker') || '{}') || {};
        } catch (e) {
            meta = {};
        }

        // Kopierte Items (cloneNode) bringen Wrapper, Button und Fenster schon mit – neu aufbauen
        var wrap = select.parentNode && select.parentNode.classList && select.parentNode.classList.contains('mform-img-picker')
            ? select.parentNode
            : null;
        if (wrap) {
            wrap.querySelectorAll('.mform-img-picker__toggle, .mform-img-picker__panel').forEach(function (node) {
                node.remove();
            });
        } else {
            wrap = document.createElement('div');
            wrap.className = 'mform-img-picker';
            select.parentNode.insertBefore(wrap, select);
            wrap.appendChild(select);
        }
        select.classList.add('mform-img-picker__native');
        select.tabIndex = -1;
        select.setAttribute('aria-hidden', 'true');

        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'mform-img-picker__toggle';
        button.id = uid('mform-img-picker-button');
        button.setAttribute('aria-haspopup', 'listbox');
        button.setAttribute('aria-expanded', 'false');

        var panel = document.createElement('div');
        panel.className = 'mform-img-picker__panel';
        panel.id = uid('mform-img-picker-list');
        panel.setAttribute('role', 'listbox');
        panel.tabIndex = -1;
        panel.hidden = true;
        button.setAttribute('aria-controls', panel.id);

        var label = findLabel(select);
        if (label) {
            if (!label.id) {
                label.id = uid('mform-img-picker-label');
            }
            button.setAttribute('aria-labelledby', label.id + ' ' + button.id);
            panel.setAttribute('aria-labelledby', label.id);
            if (label.tagName === 'LABEL') {
                label.setAttribute('for', button.id);
            }
        }

        wrap.appendChild(button);
        wrap.appendChild(panel);

        var options = Array.prototype.slice.call(select.options);
        var items = options.map(function (option) {
            var data = meta[option.value] || {};
            var item = document.createElement('div');
            item.className = 'mform-img-picker__option';
            item.id = uid('mform-img-picker-option');
            item.setAttribute('role', 'option');
            item.innerHTML = visual(data)
                + '<span class="mform-img-picker__text"><span class="mform-img-picker__label"></span>'
                + (data.description ? '<span class="mform-img-picker__desc"></span>' : '')
                + '</span>';
            item.querySelector('.mform-img-picker__label').textContent = option.text;
            if (data.description) {
                item.querySelector('.mform-img-picker__desc').textContent = data.description;
            }
            if (option.disabled) {
                item.setAttribute('aria-disabled', 'true');
            }
            panel.appendChild(item);
            return item;
        });

        var active = -1;

        function render() {
            var index = select.selectedIndex;
            var option = options[index];
            var data = option ? (meta[option.value] || {}) : {};
            button.innerHTML = visual(data)
                + '<span class="mform-img-picker__text"><span class="mform-img-picker__label"></span></span>'
                + '<span class="mform-img-picker__caret" aria-hidden="true"></span>';
            button.querySelector('.mform-img-picker__label').textContent = option ? option.text : '';
            items.forEach(function (item, k) {
                item.setAttribute('aria-selected', k === index ? 'true' : 'false');
            });
            button.disabled = select.disabled;
        }

        function setActive(index) {
            if (index < 0 || index >= items.length) {
                return;
            }
            active = index;
            items.forEach(function (item, k) {
                item.classList.toggle('is-active', k === index);
            });
            panel.setAttribute('aria-activedescendant', items[index].id);
            if (items[index].scrollIntoView) {
                items[index].scrollIntoView({ block: 'nearest' });
            }
        }

        function columns() {
            if (!items.length) {
                return 1;
            }
            var top = items[0].offsetTop;
            var count = 0;
            for (var k = 0; k < items.length && items[k].offsetTop === top; k++) {
                count++;
            }
            return Math.max(1, count);
        }

        function onOutside(event) {
            if (!wrap.contains(event.target)) {
                close(false);
            }
        }

        function open() {
            if (button.disabled) {
                return;
            }
            render();
            panel.hidden = false;
            wrap.classList.add('is-open');
            button.setAttribute('aria-expanded', 'true');
            setActive(Math.max(0, select.selectedIndex));
            panel.focus({ preventScroll: true });
            document.addEventListener('mousedown', onOutside, true);
        }

        function close(focusButton) {
            if (panel.hidden) {
                return;
            }
            panel.hidden = true;
            wrap.classList.remove('is-open');
            button.setAttribute('aria-expanded', 'false');
            panel.removeAttribute('aria-activedescendant');
            document.removeEventListener('mousedown', onOutside, true);
            if (focusButton) {
                button.focus();
            }
        }

        function choose(index) {
            var item = items[index];
            if (!item || item.getAttribute('aria-disabled') === 'true') {
                return;
            }
            if (select.selectedIndex !== index) {
                select.selectedIndex = index;
                // native Events: mform.js (Bedingungen, jQuery-Handler) und der Flex-Repeater hören darauf
                select.dispatchEvent(new Event('input', { bubbles: true }));
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }
            render();
            close(true);
        }

        button.addEventListener('click', function () {
            if (panel.hidden) {
                open();
            } else {
                close(true);
            }
        });
        button.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                open();
            }
        });
        panel.addEventListener('keydown', function (event) {
            var next = active;
            switch (event.key) {
                case 'ArrowRight':
                    next = active + 1;
                    break;
                case 'ArrowLeft':
                    next = active - 1;
                    break;
                case 'ArrowDown':
                    next = active + columns();
                    break;
                case 'ArrowUp':
                    next = active - columns();
                    break;
                case 'Home':
                    next = 0;
                    break;
                case 'End':
                    next = items.length - 1;
                    break;
                case 'Enter':
                case ' ':
                    event.preventDefault();
                    choose(active);
                    return;
                case 'Escape':
                    event.preventDefault();
                    event.stopPropagation();
                    close(true);
                    return;
                case 'Tab':
                    close(false);
                    return;
                default:
                    return;
            }
            event.preventDefault();
            setActive(Math.min(items.length - 1, Math.max(0, next)));
        });
        panel.addEventListener('click', function (event) {
            var item = event.target.closest('.mform-img-picker__option');
            if (item) {
                choose(items.indexOf(item));
            }
        });
        panel.addEventListener('mousemove', function (event) {
            var item = event.target.closest('.mform-img-picker__option');
            if (item && items.indexOf(item) !== active) {
                setActive(items.indexOf(item));
            }
        });
        // Wert von außen geändert (Repeater: Kopieren/Einfügen, Zurücksetzen)
        select.addEventListener('change', render);

        render();
    }

    function init(root) {
        (root || document).querySelectorAll('select.mform-img-picker').forEach(enhance);
    }

    if (window.jQuery) {
        window.jQuery(document).on('rex:ready', function (event, container) {
            init(container && container[0] ? container[0] : document);
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            init(document);
        });
    } else {
        init(document);
    }
})();
