/**
 * MForm Visual Form Builder.
 * Vanilla JS, depends only on the globally available Sortable from sortable.min.js.
 */
(function () {
    'use strict';

    var initialised = false;

    function init() {
        if (initialised) return;
        var canvas = document.querySelector('[data-fb-canvas]');
        if (!canvas) return; // not on this page
        initialised = true;
        run();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    // REDAXO PJAX – container reload
    if (typeof jQuery !== 'undefined') {
        jQuery(document).on('rex:ready', function () {
            // After a PJAX reload, init flag must reset because DOM is replaced
            initialised = false;
            init();
        });
    }

    function run() {

    if (typeof Sortable === 'undefined') {
        console.error('[mform-fb] Sortable is not available.');
        return;
    }

    var TYPES = {
        text:        { label: 'Text', method: 'addTextField',        props: ['label', 'defaultValue', 'placeholder', 'required', 'full'] },
        textarea:    { label: 'Textarea', method: 'addTextAreaField', props: ['label', 'defaultValue', 'placeholder', 'tinymce', 'required', 'full'] },
        select:      { label: 'Select', method: 'addSelectField',     props: ['label', 'defaultValue', 'options', 'required', 'full'] },
        radio:       { label: 'Radio',  method: 'addRadioField',      props: ['label', 'defaultValue', 'options', 'required'] },
        checkbox:    { label: 'Checkbox', method: 'addCheckboxField', props: ['label', 'defaultValue', 'options'] },
        hidden:      { label: 'Hidden', method: 'addHiddenField',     props: ['defaultValue'] },
        headline:    { label: 'Headline', method: 'addHeadlineElement', props: ['label'] },
        description: { label: 'Description', method: 'addDescriptionElement', props: ['label'] },
        repeater:    { label: 'Flex Repeater', method: 'addFlexRepeaterElement', props: ['label', 'repeaterMin', 'repeaterMax'] }
    };

    /** @type {Array<Object>} top-level model */
    var state = [];
    var nextId = 1;
    var activeItem = null;

    var $canvas = document.querySelector('[data-fb-canvas]');
    var $palette = document.querySelector('[data-fb-palette]');
    var $paletteWrap = document.querySelector('[data-fb-palette-wrap]');
    var $code = document.querySelector('[data-fb-code]');
    var $propsForm = document.querySelector('[data-fb-props-form]');
    var $propsEmpty = document.querySelector('[data-fb-props-empty]');

    if (!$canvas) return;

    // ---- Model helpers ---------------------------------------------------

    function makeItem(type) {
        var def = TYPES[type];
        var item = {
            uid: 'fb-' + (Math.random().toString(36).slice(2, 8)),
            id: nextId++,
            type: type,
            label: def.label,
            defaultValue: '',
            placeholder: '',
            options: type === 'select' || type === 'radio' || type === 'checkbox' ? "1=Option 1\n2=Option 2" : '',
            required: false,
            full: false,
            tinymce: false,
            repeaterMin: '',
            repeaterMax: '',
            children: type === 'repeater' ? [] : null
        };
        return item;
    }

    function findItem(uid, list) {
        list = list || state;
        for (var i = 0; i < list.length; i++) {
            if (list[i].uid === uid) return list[i];
            if (list[i].children) {
                var hit = findItem(uid, list[i].children);
                if (hit) return hit;
            }
        }
        return null;
    }

    function removeItem(uid, list) {
        list = list || state;
        for (var i = 0; i < list.length; i++) {
            if (list[i].uid === uid) { list.splice(i, 1); return true; }
            if (list[i].children && removeItem(uid, list[i].children)) return true;
        }
        return false;
    }

    // ---- Rendering -------------------------------------------------------

    function renderItem(item) {
        var el = document.createElement('div');
        el.className = 'mform-fb__item mform-fb__item--' + item.type;
        el.dataset.uid = item.uid;

        if (item.type === 'repeater') {
            var head = document.createElement('div');
            head.className = 'mform-fb__item-head';
            head.innerHTML =
                '<span class="mform-fb__item-handle">::</span>' +
                '<span class="mform-fb__item-type">repeater</span>' +
                '<span class="mform-fb__item-label"></span>' +
                '<span class="mform-fb__item-id">id ' + item.id + '</span>' +
                '<button type="button" class="mform-fb__item-remove" data-fb-remove>x</button>';
            head.querySelector('.mform-fb__item-label').textContent = item.label || 'Repeater';
            head.addEventListener('click', function (e) {
                if (e.target.closest('[data-fb-remove]')) return;
                selectItem(item);
            });
            el.appendChild(head);

            var nested = document.createElement('div');
            nested.className = 'mform-fb__nested';
            nested.dataset.fbNested = item.uid;
            if (item.children.length === 0) {
                nested.innerHTML = '<p class="mform-fb__nested-hint">Felder fuer den Repeater hier einfuegen (Repeater oben anklicken, dann links ein Feld waehlen)</p>';
            } else {
                item.children.forEach(function (c) { nested.appendChild(renderItem(c)); });
            }
            el.appendChild(nested);

            // sortable for nested zone
            Sortable.create(nested, {
                group: { name: 'fb-fields', pull: true, put: ['fb-fields', 'fb-palette'] },
                animation: 150,
                handle: '.mform-fb__item-handle',
                onAdd: handleSortAdd,
                onUpdate: handleSortUpdate,
                onRemove: rebuildAllAndEmit
            });
        } else {
            el.innerHTML =
                '<span class="mform-fb__item-handle">::</span>' +
                '<span class="mform-fb__item-type">' + item.type + '</span>' +
                '<span class="mform-fb__item-label"></span>' +
                '<span class="mform-fb__item-id">id ' + item.id + '</span>' +
                '<button type="button" class="mform-fb__item-remove" data-fb-remove>x</button>';
            el.querySelector('.mform-fb__item-label').textContent = item.label || item.type;
            el.addEventListener('click', function (e) {
                if (e.target.closest('[data-fb-remove]')) return;
                selectItem(item);
            });
        }

        el.querySelector('[data-fb-remove]').addEventListener('click', function (e) {
            e.stopPropagation();
            removeItem(item.uid);
            if (activeItem === item) { activeItem = null; renderProps(); }
            renderCanvas();
            emitCode();
        });

        return el;
    }

    function renderCanvas() {
        $canvas.innerHTML = '';
        if (state.length === 0) {
            $canvas.innerHTML = '<p class="mform-fb__hint">Klick links auf ein Feld, um es hier einzufuegen</p>';
            highlightActive();
            return;
        }
        state.forEach(function (item) {
            $canvas.appendChild(renderItem(item));
        });
        highlightActive();
    }

    function highlightActive() {
        document.querySelectorAll('.mform-fb__item.is-active').forEach(function (el) { el.classList.remove('is-active'); });
        if (!activeItem) return;
        var el = $canvas.querySelector('[data-uid="' + activeItem.uid + '"]');
        if (el) el.classList.add('is-active');
    }

    // ---- Props panel -----------------------------------------------------

    function selectItem(item) {
        activeItem = item;
        renderProps();
        highlightActive();
    }

    function renderProps() {
        if (!activeItem) {
            $propsEmpty.style.display = '';
            $propsForm.style.display = 'none';
            return;
        }
        $propsEmpty.style.display = 'none';
        $propsForm.style.display = '';

        var def = TYPES[activeItem.type];
        var available = def.props || [];

        $propsForm.querySelectorAll('[data-fb-prop-group]').forEach(function (g) {
            g.style.display = available.indexOf(g.dataset.fbPropGroup) !== -1 ? '' : 'none';
        });

        // Label group: also hidden field has no label - but our def excludes it.
        var labelGrp = $propsForm.querySelector('.form-group:first-child');
        labelGrp.style.display = available.indexOf('label') !== -1 ? '' : 'none';

        $propsForm.querySelectorAll('[data-fb-prop]').forEach(function (input) {
            var key = input.dataset.fbProp;
            var val = activeItem[key];
            if (input.type === 'checkbox') input.checked = !!val;
            else input.value = val == null ? '' : val;
        });
    }

    $propsForm.addEventListener('input', function (e) {
        if (!activeItem) return;
        var input = e.target.closest('[data-fb-prop]');
        if (!input) return;
        var key = input.dataset.fbProp;
        activeItem[key] = input.type === 'checkbox' ? input.checked : input.value;
        // Live update the rendered label
        var el = $canvas.querySelector('[data-uid="' + activeItem.uid + '"]');
        if (el && key === 'label') {
            var lbl = el.querySelector('.mform-fb__item-label');
            if (lbl) lbl.textContent = activeItem.label || activeItem.type;
        }
        emitCode();
    });

    // ---- Sortable wiring -------------------------------------------------

    function handleSortAdd(evt) {
        var fromPalette = evt.from.dataset.fbPalette !== undefined || evt.from.dataset.fbPaletteWrap !== undefined;
        if (fromPalette) {
            var type = evt.item.dataset.type;
            evt.item.parentNode.removeChild(evt.item);  // drop palette ghost
            var newItem = makeItem(type);
            insertIntoTarget(evt.to, evt.newIndex, newItem);
            renderCanvas();
            emitCode();
            selectItem(newItem);
            return;
        }
        rebuildAllAndEmit();
    }

    function handleSortUpdate() {
        rebuildAllAndEmit();
    }

    function insertIntoTarget(targetEl, idx, item) {
        if (targetEl === $canvas) {
            state.splice(idx, 0, item);
        } else if (targetEl.dataset.fbNested) {
            var owner = findItem(targetEl.dataset.fbNested);
            if (owner && owner.children) {
                if (item.type === 'repeater') {
                    // disallow nested repeaters in MVP
                    alert('Repeater im Repeater wird im MVP nicht unterstuetzt.');
                    return;
                }
                owner.children.splice(idx, 0, item);
            }
        }
    }

    /**
     * After a Sortable reorder, rebuild model from DOM.
     */
    function rebuildAllAndEmit() {
        var newTop = [];
        Array.prototype.forEach.call($canvas.children, function (el) {
            if (!el.dataset || !el.dataset.uid) return;
            var item = findItem(el.dataset.uid);
            if (!item) return;
            // Rebuild children if repeater
            if (item.type === 'repeater') {
                var nested = el.querySelector('[data-fb-nested]');
                var children = [];
                if (nested) {
                    Array.prototype.forEach.call(nested.children, function (cEl) {
                        if (!cEl.dataset || !cEl.dataset.uid) return;
                        var ci = findItem(cEl.dataset.uid);
                        if (ci) children.push(ci);
                    });
                }
                item.children = children;
            }
            newTop.push(item);
        });
        state = newTop;
        renderCanvas();
        emitCode();
    }

    // Palette items: click-to-add only.
    // Sortable on the palette swallows clicks, so we deliberately do not
    // attach Sortable to the palette containers.
    function paletteClick(e) {
        var li = e.target.closest('.mform-fb__pal-item');
        if (!li || !$palette.contains(li) && !$paletteWrap.contains(li)) return;
        var type = li.dataset.type;
        if (!type) return;
        var newItem = makeItem(type);
        if (type === 'repeater') {
            state.push(newItem);
        } else if (activeItem && activeItem.type === 'repeater') {
            activeItem.children.push(newItem);
        } else {
            state.push(newItem);
        }
        renderCanvas();
        emitCode();
        selectItem(newItem);
    }
    $palette.addEventListener('click', paletteClick);
    $paletteWrap.addEventListener('click', paletteClick);

    Sortable.create($canvas, {
        group: { name: 'fb-fields', pull: true, put: 'fb-fields' },
        animation: 150,
        handle: '.mform-fb__item-handle',
        onUpdate: handleSortUpdate,
        onAdd: handleSortAdd,
        onRemove: rebuildAllAndEmit
    });

    // ---- Toolbar ---------------------------------------------------------

    document.querySelector('[data-fb-action="clear"]').addEventListener('click', function () {
        if (!confirm('Alles loeschen?')) return;
        state = [];
        nextId = 1;
        activeItem = null;
        renderProps();
        renderCanvas();
        emitCode();
    });

    document.querySelector('[data-fb-action="copy"]').addEventListener('click', function () {
        var txt = $code.textContent;
        navigator.clipboard.writeText(txt).then(function () {
            var msg = document.querySelector('[data-fb-copy-msg]');
            msg.textContent = 'kopiert';
            setTimeout(function () { msg.textContent = ''; }, 1500);
        });
    });

    // ---- Code generation -------------------------------------------------

    function phpStr(s) {
        return "'" + String(s).replace(/\\/g, '\\\\').replace(/'/g, "\\'") + "'";
    }

    function parseOptions(raw) {
        if (!raw) return [];
        return raw.split('\n').map(function (line) { return line.trim(); }).filter(Boolean).map(function (line, idx) {
            var eq = line.indexOf('=');
            if (eq === -1) return { key: idx + 1, label: line };
            return { key: line.slice(0, eq).trim(), label: line.slice(eq + 1).trim() };
        });
    }

    function optionsArray(items, indent) {
        var pairs = items.map(function (o) {
            var k = /^\d+$/.test(String(o.key)) ? String(o.key) : phpStr(o.key);
            return k + ' => ' + phpStr(o.label);
        });
        return '[' + pairs.join(', ') + ']';
    }

    function slugify(s, fallback) {
        if (!s) return fallback;
        var slug = String(s).toLowerCase()
            .replace(/[\u00e4]/g, 'ae').replace(/[\u00f6]/g, 'oe').replace(/[\u00fc]/g, 'ue').replace(/[\u00df]/g, 'ss')
            .replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
        return slug || fallback;
    }

    /**
     * Render a single field as fluent chain (no leading $mform).
     */
    function renderField(item, idArg, indent) {
        var def = TYPES[item.type];
        var idLit = typeof idArg === 'number' ? String(idArg) : phpStr(idArg);
        var attrs = {};
        if (item.label && item.type !== 'hidden') attrs.label = item.label;
        if (item.placeholder) attrs.placeholder = item.placeholder;
        if (item.required) attrs.required = 'required';
        if (item.tinymce && item.type === 'textarea') attrs['class'] = 'form-control tinymce-editor';

        var attrParts = Object.keys(attrs).map(function (k) {
            return phpStr(k) + ' => ' + phpStr(attrs[k]);
        });
        var attrPhp = attrParts.length ? '[' + attrParts.join(', ') + ']' : null;

        var line = '$mform->' + def.method + '(' + idLit;

        if (item.type === 'select' || item.type === 'radio' || item.type === 'checkbox') {
            var opts = parseOptions(item.options);
            line += ', ' + optionsArray(opts);
            if (attrPhp) line += ', ' + attrPhp;
            if (item.defaultValue) line += ', ' + phpStr(item.defaultValue);
        } else if (item.type === 'text' || item.type === 'textarea') {
            if (attrPhp) line += ', ' + attrPhp;
            if (item.defaultValue) line += ', null, ' + phpStr(item.defaultValue);
        } else if (item.type === 'hidden') {
            line += ', ' + (item.defaultValue ? phpStr(item.defaultValue) : 'null');
        } else if (item.type === 'headline' || item.type === 'description') {
            line += ', ' + phpStr(item.label || '');
        }

        line += ')';
        if (item.full) line += '\n' + indent + '    ->setFull()';
        return indent + line;
    }

    function renderRepeater(item, slotId, indent) {
        var minMax = '';
        var childKeys = {};
        // Collect & assign string keys for inner fields
        var innerLines = item.children.map(function (c) {
            var key = slugify(c.label, 'field_' + c.id);
            // ensure unique
            var base = key, n = 2;
            while (childKeys[key]) { key = base + '_' + n++; }
            childKeys[key] = true;
            return renderField(c, key, indent + '        ');
        }).join("\n");

        var minVal = item.repeaterMin !== '' ? parseInt(item.repeaterMin, 10) : null;
        var maxVal = item.repeaterMax !== '' ? parseInt(item.repeaterMax, 10) : null;

        var configParts = [];
        if (minVal !== null && !isNaN(minVal)) configParts.push("'min' => " + minVal);
        if (maxVal !== null && !isNaN(maxVal)) configParts.push("'max' => " + maxVal);
        if (item.label) configParts.push("'label' => " + phpStr(item.label));

        var cfg = configParts.length ? ', [' + configParts.join(', ') + ']' : '';

        var inner = innerLines || (indent + '        // Felder hier ablegen');

        return indent + '$mform->addFlexRepeaterElement(' + slotId + ', MForm::factory()\n'
            + inner + (innerLines ? '\n' : '\n')
            + indent + '    , null' + cfg + ');';
    }

    function emitCode() {
        if (state.length === 0) {
            $code.textContent = '// Noch keine Felder hinzugefuegt.';
            return;
        }
        var lines = [];
        lines.push('use FriendsOfRedaxo\\MForm;');
        lines.push('');
        lines.push('$mform = MForm::factory();');
        lines.push('');
        state.forEach(function (item) {
            if (item.type === 'repeater') {
                lines.push(renderRepeater(item, item.id, ''));
            } else {
                lines.push(renderField(item, item.id, '') + ';');
            }
        });
        lines.push('');
        lines.push('echo $mform->show();');
        $code.textContent = lines.join('\n');
    }

    // initial
    renderCanvas();
    emitCode();

    } // end run()
})();
