/**
 * MForm Visual Form Builder.
 * Vanilla JS, depends only on the globally available Sortable from sortable.min.js.
 *
 * Architektur:
 *  - Modell (state[]) ist single-source-of-truth.
 *  - Sortable-Events mutieren das Modell direkt anhand evt.from / evt.to /
 *    evt.oldIndex / evt.newIndex; wir scannen NICHT das DOM.
 *  - Palette unterstützt sowohl Drag&Drop (Sortable mit pull:'clone') als
 *    auch Click-to-Add. Klick und Drag stören sich nicht, weil Sortable
 *    Klicks ohne tatsächlichen Drag durchlässt.
 *  - Repeater dürfen maximal eine weitere Repeater-Ebene tief verschachtelt
 *    werden (zwei Repeater-Ebenen insgesamt). Tiefer-Drops werden geblockt.
 */
(function () {
    'use strict';

    var initialised = false;

    function init() {
        if (initialised) return;
        var canvas = document.querySelector('[data-fb-canvas]');
        if (!canvas) return;
        initialised = true;
        run();
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    if (typeof jQuery !== 'undefined') {
        jQuery(document).on('rex:ready', function () {
            initialised = false;
            init();
        });
    }

    function run() {

        if (typeof Sortable === 'undefined') {
            console.error('[mform-fb] Sortable is not available.');
            return;
        }

        // Wie oft darf ein Repeater verschachtelt werden? 1 = zwei Ebenen.
        var MAX_REPEATER_DEPTH = 1;

        var TYPES = {
            text:        { label: 'Text', method: 'addTextField',
                props: ['label', 'defaultValue', 'placeholder', 'required', 'full'] },
            textarea:    { label: 'Textarea', method: 'addTextAreaField',
                props: ['label', 'defaultValue', 'placeholder', 'tinymce', 'required', 'full'] },
            select:      { label: 'Select', method: 'addSelectField',
                props: ['label', 'defaultValue', 'options', 'required', 'full'] },
            radio:       { label: 'Radio', method: 'addRadioField',
                props: ['label', 'defaultValue', 'options', 'required'] },
            checkbox:    { label: 'Checkbox', method: 'addCheckboxField',
                props: ['label', 'defaultValue', 'options'] },
            hidden:      { label: 'Hidden', method: 'addHiddenField',
                props: ['defaultValue'] },
            headline:    { label: 'Headline', method: 'addHeadline',
                props: ['label'] },
            description: { label: 'Description', method: 'addDescription',
                props: ['label'] },
            // REDAXO core widgets
            media:       { label: 'Media', method: 'addMediaField',
                props: ['label', 'category'] },
            medialist:   { label: 'Medialist', method: 'addMedialistField',
                props: ['label', 'category'] },
            imagelist:   { label: 'Imagelist', method: 'addImagelistField',
                props: ['label', 'category'] },
            link:        { label: 'Link', method: 'addLinkField',
                props: ['label', 'category'] },
            linklist:    { label: 'Linklist', method: 'addLinklistField',
                props: ['label', 'category'] },
            customlink:  { label: 'Custom Link', method: 'addCustomLinkField',
                props: ['label'] },
            customlinkmultiple: { label: 'Custom Link Multiple', method: 'addCustomLinkMultipleField',
                props: ['label'] },
            repeater:    { label: 'Flex Repeater', method: 'addFlexRepeaterElement',
                props: ['label', 'repeaterMin', 'repeaterMax'] }
        };

        var state = [];
        var nextId = 1;
        var activeItem = null;

        var $canvas = document.querySelector('[data-fb-canvas]');
        var $palette = document.querySelector('[data-fb-palette]');
        var $paletteWrap = document.querySelector('[data-fb-palette-wrap]');
        var $code = document.querySelector('[data-fb-code]');
        var $propsForm = document.querySelector('[data-fb-props-form]');
        var $propsEmpty = document.querySelector('[data-fb-props-empty]');

        $canvas.dataset.fbDepth = '0';

        // ---- Model helpers --------------------------------------------------

        function makeItem(type) {
            var def = TYPES[type];
            return {
                uid: 'fb-' + (Math.random().toString(36).slice(2, 8)),
                id: nextId++,
                type: type,
                label: def.label,
                defaultValue: '',
                placeholder: '',
                category: '',
                options: (type === 'select' || type === 'radio' || type === 'checkbox') ? "1=Option 1\n2=Option 2" : '',
                required: false,
                full: false,
                tinymce: false,
                repeaterMin: '',
                repeaterMax: '',
                children: type === 'repeater' ? [] : null
            };
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

        // Tree-aware: if a repeater contains a repeater, that's depth 1.
        function maxRepeaterSubtreeDepth(item) {
            if (item.type !== 'repeater') return -1;
            var max = 0;
            (item.children || []).forEach(function (c) {
                if (c.type === 'repeater') {
                    var d = 1 + (maxRepeaterSubtreeDepth(c) === -1 ? 0 : 1 + maxRepeaterSubtreeDepth(c));
                    // Simpler: recurse
                }
            });
            // simpler: iterative via children
            return 0;
        }

        // ---- Rendering ------------------------------------------------------

        function renderItem(item, depth) {
            var el = document.createElement('div');
            el.className = 'mform-fb__item mform-fb__item--' + item.type;
            el.dataset.uid = item.uid;

            var head = document.createElement('div');
            head.className = 'mform-fb__item-head';
            head.innerHTML =
                '<span class="mform-fb__item-handle">::</span>' +
                '<span class="mform-fb__item-type">' + item.type + '</span>' +
                '<span class="mform-fb__item-label"></span>' +
                '<span class="mform-fb__item-id">id ' + item.id + '</span>' +
                '<button type="button" class="mform-fb__item-remove" data-fb-remove>x</button>';
            head.querySelector('.mform-fb__item-label').textContent = item.label || item.type;
            head.addEventListener('click', function (e) {
                if (e.target.closest('[data-fb-remove]')) return;
                selectItem(item);
            });
            el.appendChild(head);

            if (item.type === 'repeater') {
                var nested = document.createElement('div');
                nested.className = 'mform-fb__nested';
                nested.dataset.fbNested = item.uid;
                nested.dataset.fbDepth = String(depth + 1);
                if (item.children.length === 0) {
                    nested.innerHTML = '<p class="mform-fb__nested-hint">Felder hierher ziehen oder Repeater oben anklicken und dann links ein Feld waehlen</p>';
                } else {
                    item.children.forEach(function (c) { nested.appendChild(renderItem(c, depth + 1)); });
                }
                el.appendChild(nested);

                Sortable.create(nested, {
                    group: { name: 'fb-fields', pull: true, put: ['fb-palette', 'fb-fields'] },
                    animation: 150,
                    handle: '.mform-fb__item-handle',
                    onAdd: handleSortAdd,
                    onUpdate: handleSortUpdate,
                    onRemove: handleSortRemove
                });
            }

            head.querySelector('[data-fb-remove]').addEventListener('click', function (e) {
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
                $canvas.innerHTML = '<p class="mform-fb__hint">Felder hierher ziehen oder links anklicken</p>';
                highlightActive();
                return;
            }
            state.forEach(function (item) {
                $canvas.appendChild(renderItem(item, 0));
            });
            highlightActive();
        }

        function highlightActive() {
            $canvas.querySelectorAll('.mform-fb__item.is-active').forEach(function (el) { el.classList.remove('is-active'); });
            if (!activeItem) return;
            var els = $canvas.querySelectorAll('[data-uid="' + activeItem.uid + '"]');
            els.forEach(function (el) { el.classList.add('is-active'); });
        }

        // ---- Props panel ----------------------------------------------------

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
            if (key === 'label') {
                $canvas.querySelectorAll('[data-uid="' + activeItem.uid + '"] > .mform-fb__item-head .mform-fb__item-label').forEach(function (lbl) {
                    lbl.textContent = activeItem.label || activeItem.type;
                });
            }
            emitCode();
        });

        // ---- Sortable wiring ------------------------------------------------

        function listFor(el) {
            if (el === $canvas) return state;
            if (el && el.dataset && el.dataset.fbNested) {
                var owner = findItem(el.dataset.fbNested);
                return owner && owner.children ? owner.children : null;
            }
            return null;
        }

        function depthOf(el) {
            return parseInt((el && el.dataset && el.dataset.fbDepth) || '0', 10);
        }

        function handleSortAdd(evt) {
            var fromPalette = evt.from === $palette || evt.from === $paletteWrap;
            var toList = listFor(evt.to);
            if (!toList) {
                if (evt.item.parentNode) evt.item.parentNode.removeChild(evt.item);
                renderCanvas();
                return;
            }

            if (fromPalette) {
                var type = evt.item.dataset.type;
                if (evt.item.parentNode) evt.item.parentNode.removeChild(evt.item);
                if (type === 'repeater' && depthOf(evt.to) > MAX_REPEATER_DEPTH) {
                    alert('Mehr als ' + (MAX_REPEATER_DEPTH + 1) + ' Repeater-Ebenen werden nicht unterstuetzt.');
                    renderCanvas();
                    return;
                }
                var newItem = makeItem(type);
                toList.splice(evt.newIndex, 0, newItem);
                renderCanvas();
                emitCode();
                selectItem(newItem);
                return;
            }

            // Cross-list move within builder
            var uid = evt.item.dataset && evt.item.dataset.uid;
            if (!uid) return;
            var item = findItem(uid);
            if (!item) return;

            // Block dropping a repeater that would exceed max depth
            if (item.type === 'repeater' && depthOf(evt.to) > MAX_REPEATER_DEPTH) {
                alert('Mehr als ' + (MAX_REPEATER_DEPTH + 1) + ' Repeater-Ebenen werden nicht unterstuetzt.');
                renderCanvas();
                return;
            }

            removeItem(uid);
            toList.splice(evt.newIndex, 0, item);
            renderCanvas();
            emitCode();
        }

        function handleSortRemove() { /* no-op: handled in onAdd */ }

        function handleSortUpdate(evt) {
            var list = listFor(evt.from);
            if (!list) return;
            if (evt.oldIndex === evt.newIndex) return;
            var moved = list.splice(evt.oldIndex, 1)[0];
            list.splice(evt.newIndex, 0, moved);
            renderCanvas();
            emitCode();
        }

        // Palette: clone-on-drag + click-to-add. Both work side by side.
        Sortable.create($palette, {
            group: { name: 'fb-palette', pull: 'clone', put: false },
            sort: false,
            animation: 0
        });
        Sortable.create($paletteWrap, {
            group: { name: 'fb-palette', pull: 'clone', put: false },
            sort: false,
            animation: 0
        });

        function paletteClick(e) {
            // Ignore if it was the end of a drag
            if (e.detail === 0) return;
            var li = e.target.closest('.mform-fb__pal-item');
            if (!li) return;
            // Only react if the li is inside one of the palette containers
            if (!$palette.contains(li) && !$paletteWrap.contains(li)) return;
            var type = li.dataset.type;
            if (!type) return;
            var newItem = makeItem(type);
            if (type === 'repeater') {
                if (activeItem && activeItem.type === 'repeater') {
                    // Active repeater is depth 0 (top level only) -> insert nested
                    activeItem.children.push(newItem);
                } else {
                    state.push(newItem);
                }
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
            group: { name: 'fb-fields', pull: true, put: ['fb-palette', 'fb-fields'] },
            animation: 150,
            handle: '.mform-fb__item-handle',
            onUpdate: handleSortUpdate,
            onAdd: handleSortAdd,
            onRemove: handleSortRemove
        });

        // ---- Toolbar --------------------------------------------------------

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
            navigator.clipboard.writeText($code.textContent).then(function () {
                var msg = document.querySelector('[data-fb-copy-msg]');
                msg.textContent = 'kopiert';
                setTimeout(function () { msg.textContent = ''; }, 1500);
            });
        });

        // ---- Code generation ------------------------------------------------

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

        function optionsArray(items) {
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

        function attrsForItem(item) {
            var a = {};
            if (item.label && item.type !== 'hidden') a.label = item.label;
            if (item.placeholder) a.placeholder = item.placeholder;
            if (item.required) a.required = 'required';
            if (item.tinymce && item.type === 'textarea') a['class'] = 'form-control tinymce-editor';
            return a;
        }

        function attrsToPhp(attrs) {
            var keys = Object.keys(attrs);
            if (!keys.length) return null;
            return '[' + keys.map(function (k) { return phpStr(k) + ' => ' + phpStr(attrs[k]); }).join(', ') + ']';
        }

        // Builds the call expression WITHOUT a leading `$mform` / `->`, so the
        // caller can choose: top-level uses `$mform->...`, inner items use `->...`.
        function renderCall(item, idArg) {
            var def = TYPES[item.type];
            var attrs = attrsForItem(item);
            var attrPhp = attrsToPhp(attrs);
            // Headline / Description: no id, single string argument
            if (item.type === 'headline' || item.type === 'description') {
                return def.method + '(' + phpStr(item.label || '') + ')';
            }
            var idLit = typeof idArg === 'number' ? String(idArg) : phpStr(idArg);
            var line = def.method + '(' + idLit;

            switch (item.type) {
                case 'select':
                case 'radio':
                case 'checkbox':
                    line += ', ' + optionsArray(parseOptions(item.options));
                    if (attrPhp) line += ', ' + attrPhp;
                    if (item.defaultValue) line += ', ' + phpStr(item.defaultValue);
                    break;
                case 'text':
                case 'textarea':
                    if (attrPhp) line += ', ' + attrPhp;
                    if (item.defaultValue) line += ', null, ' + phpStr(item.defaultValue);
                    break;
                case 'hidden':
                    line += ', ' + (item.defaultValue ? phpStr(item.defaultValue) : 'null');
                    break;
                case 'media':
                case 'medialist':
                case 'imagelist':
                case 'link':
                case 'linklist':
                    var catLit = item.category ? (parseInt(item.category, 10) || phpStr(item.category)) : 'null';
                    line += ', null, ' + catLit + (attrPhp ? ', ' + attrPhp : '');
                    break;
                case 'customlink':
                    if (attrPhp) line += ', ' + attrPhp;
                    if (item.defaultValue) line += ', ' + phpStr(item.defaultValue);
                    break;
                case 'customlinkmultiple':
                    if (attrPhp) line += ', ' + attrPhp;
                    break;
            }
            line += ')';
            return line;
        }

        // Render top-level field as `$mform->call(...);` (with optional setFull chain)
        function renderField(item, idArg, indent) {
            var line = indent + '$mform->' + renderCall(item, idArg);
            if (item.full && (item.type === 'text' || item.type === 'textarea' || item.type === 'select')) {
                line += '\n' + indent + '    ->setFull()';
            }
            return line;
        }

        // Render inner field as `->call(...)` chained to MForm::factory()
        function renderInnerChainLink(item, idArg, indent) {
            var line = indent + '->' + renderCall(item, idArg);
            if (item.full && (item.type === 'text' || item.type === 'textarea' || item.type === 'select')) {
                line += '\n' + indent + '->setFull()';
            }
            return line;
        }

        function renderRepeaterStmt(item, idArg, indent) {
            var idLit = typeof idArg === 'number' ? String(idArg) : phpStr(idArg);
            var inner = renderRepeaterInner(item, indent);
            var cfg = repeaterCfg(item);
            return indent + '$mform->addFlexRepeaterElement(' + idLit + ', MForm::factory()\n'
                + inner
                + (cfg ? ',\n' + indent + '    ' + cfg : '')
                + ');';
        }

        function renderInnerRepeaterChainLink(item, idArg, indent) {
            // Inside an outer factory(), a nested repeater is added with
            // ->addFlexRepeaterElement($id, MForm::factory()->...->..., $options)
            var idLit = typeof idArg === 'number' ? String(idArg) : phpStr(idArg);
            var inner = renderRepeaterInner(item, indent);
            var cfg = repeaterCfg(item);
            return indent + '->addFlexRepeaterElement(' + idLit + ', MForm::factory()\n'
                + inner
                + (cfg ? ',\n' + indent + '    ' + cfg : '')
                + ')';
        }

        function repeaterCfg(item) {
            var minVal = item.repeaterMin !== '' ? parseInt(item.repeaterMin, 10) : null;
            var maxVal = item.repeaterMax !== '' ? parseInt(item.repeaterMax, 10) : null;
            var parts = [];
            if (minVal !== null && !isNaN(minVal)) parts.push("'min' => " + minVal);
            if (maxVal !== null && !isNaN(maxVal)) parts.push("'max' => " + maxVal);
            if (item.label) parts.push("'label' => " + phpStr(item.label));
            return parts.length ? '[' + parts.join(', ') + ']' : '';
        }

        function renderRepeaterInner(item, indent) {
            var children = item.children || [];
            if (children.length === 0) {
                return indent + '        // Felder hier ablegen\n';
            }
            var keyPool = {};
            var lines = children.map(function (c) {
                var key = slugify(c.label, 'field_' + c.id);
                var base = key, n = 2;
                while (keyPool[key]) { key = base + '_' + n++; }
                keyPool[key] = true;
                if (c.type === 'repeater') {
                    return renderInnerRepeaterChainLink(c, key, indent + '        ');
                }
                return renderInnerChainLink(c, key, indent + '        ');
            });
            return lines.join('\n') + '\n';
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
                    lines.push(renderRepeaterStmt(item, item.id, ''));
                } else {
                    lines.push(renderField(item, item.id, '') + ';');
                }
            });
            lines.push('');
            lines.push('echo $mform->show();');
            $code.textContent = lines.join('\n');
        }

        // ---- Init -----------------------------------------------------------

        renderCanvas();
        emitCode();
    } // end run()
})();
