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
    var initialisedCanvas = null;

    function init() {
        var canvas = document.querySelector('[data-fb-canvas]');
        if (!canvas) return;
        // Wenn bereits auf demselben Canvas initialisiert: nichts tun.
        // (rex:ready feuert bei manchen REDAXO-Setups sonst eine doppelte
        //  Initialisierung mit doppelten Event-Listenern und parallelen
        //  state-Arrays, was zu "Geister"-Eintraegen nach Loeschen fuehrt.)
        if (initialised && initialisedCanvas === canvas) return;
        initialised = true;
        initialisedCanvas = canvas;
        run();
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    if (typeof jQuery !== 'undefined') {
        jQuery(document).on('rex:ready', function () {
            // Reset nur wenn der Canvas-Knoten ausgetauscht wurde (z.B. echter
            // Page-Wechsel via PJAX). Bei reinem rex:ready ohne DOM-Wechsel
            // ueberspringen wir die Re-Initialisierung.
            var canvas = document.querySelector('[data-fb-canvas]');
            if (canvas && canvas !== initialisedCanvas) {
                initialised = false;
                initialisedCanvas = null;
                init();
            }
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
                props: ['label', 'defaultValue', 'placeholder', 'notice', 'cssClass', 'required', 'full'] },
            textarea:    { label: 'Textarea', method: 'addTextAreaField',
                props: ['label', 'defaultValue', 'placeholder', 'notice', 'rows', 'cssClass', 'tinymce', 'tinymceProfile', 'required', 'full'] },
            select:      { label: 'Select', method: 'addSelectField',
                props: ['label', 'defaultValue', 'options', 'notice', 'cssClass', 'required', 'full'] },
            radio:       { label: 'Radio', method: 'addRadioField',
                props: ['label', 'defaultValue', 'options', 'notice', 'cssClass', 'required'] },
            checkbox:    { label: 'Checkbox', method: 'addCheckboxField',
                props: ['label', 'defaultValue', 'options', 'notice', 'cssClass'] },
            hidden:      { label: 'Hidden', method: 'addHiddenField',
                props: ['defaultValue'] },
            headline:    { label: 'Headline', method: 'addHeadline',
                props: ['label'] },
            description: { label: 'Description', method: 'addDescription',
                props: ['label'] },
            // REDAXO core widgets
            media:       { label: 'Media', method: 'addMediaField',
                props: ['label', 'category', 'mediaType', 'notice', 'cssClass'] },
            medialist:   { label: 'Medialist', method: 'addMedialistField',
                props: ['label', 'category', 'mediaType', 'notice', 'cssClass'] },
            imagelist:   { label: 'Imagelist', method: 'addImagelistField',
                props: ['label', 'category', 'notice', 'cssClass'] },
            link:        { label: 'Link', method: 'addLinkField',
                props: ['label', 'category', 'notice', 'cssClass'] },
            linklist:    { label: 'Linklist', method: 'addLinklistField',
                props: ['label', 'category', 'notice', 'cssClass'] },
            customlink:  { label: 'Custom Link', method: 'addCustomLinkField',
                props: ['label', 'notice', 'cssClass',
                        'clTypeIntern', 'clTypeExtern', 'clTypeMedia', 'clTypeMailto', 'clTypeTel',
                        'linkCategory', 'mediaCategory', 'externPrefix', 'mediaType'] },
            customlinkmultiple: { label: 'Custom Link Multiple', method: 'addCustomLinkMultipleField',
                props: ['label', 'notice', 'cssClass', 'btnAdd',
                        'clTypeIntern', 'clTypeExtern', 'clTypeMedia', 'clTypeMailto', 'clTypeTel',
                        'linkCategory', 'mediaCategory', 'externPrefix', 'mediaType'] },
            repeater:    { label: 'Flex Repeater', method: 'addFlexRepeaterElement',
                props: ['label', 'repeaterMin', 'repeaterMax', 'repeaterDefaultCount',
                        'repeaterCollapsed', 'repeaterFirstOpen', 'repeaterShowToggleAll',
                        'repeaterOpen', 'repeaterCopyPaste', 'repeaterConfirmDelete',
                        'repeaterConfirmDeleteMsg', 'repeaterBtnText', 'repeaterBtnClass'] },
            tab:         { label: 'Tab', method: 'addTabElement',
                props: ['label', 'tabPullRight'] }
        };

        var state = [];
        var nextId = 1;
        var activeItem = null;
        var clipboard = null; // shallow item clone in memory for paste

        var $canvas = document.querySelector('[data-fb-canvas]');
        var $palette = document.querySelector('[data-fb-palette]');
        var $paletteWrap = document.querySelector('[data-fb-palette-wrap]');
        var $code = document.querySelector('[data-fb-code]');
        var $output = document.querySelector('[data-fb-output]');
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
                notice: '',
                cssClass: '',
                rows: '',
                category: '',
                options: (type === 'select' || type === 'radio' || type === 'checkbox') ? "1=Option 1\n2=Option 2" : '',
                required: false,
                full: false,
                tinymce: false,
                tinymceProfile: '',
                // CustomLink
                clTypeIntern: type === 'customlink' || type === 'customlinkmultiple',
                clTypeExtern: type === 'customlink' || type === 'customlinkmultiple',
                clTypeMedia: false,
                clTypeMailto: false,
                clTypeTel: false,
                linkCategory: '',
                mediaCategory: '',
                externPrefix: '',
                mediaType: '',
                btnAdd: '',
                // Repeater
                repeaterMin: '',
                repeaterMax: '',
                repeaterDefaultCount: '',
                repeaterCollapsed: false,
                repeaterFirstOpen: false,
                repeaterShowToggleAll: type === 'repeater' ? true : false,
                repeaterCopyPaste: type === 'repeater' ? true : false,
                repeaterConfirmDelete: type === 'repeater' ? true : false,
                repeaterOpen: type === 'repeater' ? true : false,
                repeaterConfirmDeleteMsg: '',
                repeaterBtnText: '',
                repeaterBtnClass: '',
                // Tab
                tabPullRight: false,
                children: (type === 'repeater' || type === 'tab') ? [] : null
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

        function deepCloneWithNewIds(item) {
            var copy = JSON.parse(JSON.stringify(item));
            function reId(node) {
                node.uid = 'fb-' + (Math.random().toString(36).slice(2, 8));
                node.id = nextId++;
                if (node.children) node.children.forEach(reId);
            }
            reId(copy);
            return copy;
        }

        function findParentList(uid, list) {
            list = list || state;
            for (var i = 0; i < list.length; i++) {
                if (list[i].uid === uid) return { list: list, index: i };
                if (list[i].children) {
                    var hit = findParentList(uid, list[i].children);
                    if (hit) return hit;
                }
            }
            return null;
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

        // Track collapsed state per UID inside the BUILDER (not the same as the
        // repeater's runtime `collapsed` option which only affects the rendered form).
        var builderCollapsed = {};

        function renderItem(item, depth) {
            var el = document.createElement('div');
            el.className = 'mform-fb__item mform-fb__item--' + item.type;
            el.dataset.uid = item.uid;

            var head = document.createElement('div');
            head.className = 'mform-fb__item-head';
            var hasChildren = item.type === 'repeater' || item.type === 'tab';
            var collapseBtn = hasChildren
                ? '<button type="button" class="mform-fb__item-btn" data-fb-collapse title="Ein-/Ausklappen"><i class="rex-icon fa-chevron-' + (builderCollapsed[item.uid] ? 'right' : 'down') + '"></i></button>'
                : '';
            head.innerHTML =
                '<span class="mform-fb__item-handle"><i class="rex-icon fa-arrows"></i></span>' +
                collapseBtn +
                '<span class="mform-fb__item-type">' + item.type + '</span>' +
                '<span class="mform-fb__item-label"></span>' +
                '<span class="mform-fb__item-id">id ' + item.id + '</span>' +
                '<span class="mform-fb__item-actions">' +
                    '<button type="button" class="mform-fb__item-btn" data-fb-duplicate title="Duplizieren"><i class="rex-icon fa-clone"></i></button>' +
                    '<button type="button" class="mform-fb__item-btn" data-fb-copy-item title="Kopieren"><i class="rex-icon fa-copy"></i></button>' +
                    '<button type="button" class="mform-fb__item-btn" data-fb-paste-after title="Aus Zwischenablage einfuegen"><i class="rex-icon fa-paste"></i></button>' +
                '</span>' +
                '<button type="button" class="mform-fb__item-remove" data-fb-remove title="Loeschen"><i class="rex-icon fa-trash"></i></button>';
            head.querySelector('.mform-fb__item-label').textContent = item.label || item.type;
            head.addEventListener('click', function (e) {
                if (e.target.closest('button')) return;
                selectItem(item);
            });
            el.appendChild(head);

            if (item.type === 'repeater' || item.type === 'tab') {
                var nested = document.createElement('div');
                nested.className = 'mform-fb__nested';
                nested.dataset.fbNested = item.uid;
                nested.dataset.fbDepth = String(depth + 1);
                if (builderCollapsed[item.uid]) {
                    nested.style.display = 'none';
                }
                if (item.children.length === 0) {
                    nested.innerHTML = '<p class="mform-fb__nested-hint">Felder hierher ziehen oder ' + (item.type === 'tab' ? 'Tab' : 'Repeater') + ' oben anklicken und dann links ein Feld waehlen</p>';
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

            return el;
        }

        // Single delegated click handler for all item action buttons.
        $canvas.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-fb-remove], [data-fb-duplicate], [data-fb-copy-item], [data-fb-paste-after], [data-fb-collapse]');
            if (!btn) return;
            var itemEl = btn.closest('[data-uid]');
            if (!itemEl) return;
            e.stopPropagation();
            e.preventDefault();
            var uid = itemEl.dataset.uid;
            var item = findItem(uid);
            if (!item) return;

            if (btn.hasAttribute('data-fb-remove')) {
                removeItem(uid);
                if (activeItem && activeItem.uid === uid) { activeItem = null; renderProps(); }
                renderCanvas();
                emitCode();
                return;
            }
            if (btn.hasAttribute('data-fb-collapse')) {
                builderCollapsed[uid] = !builderCollapsed[uid];
                renderCanvas();
                return;
            }
            if (btn.hasAttribute('data-fb-duplicate')) {
                var posD = findParentList(uid);
                if (!posD) return;
                var cloneD = deepCloneWithNewIds(item);
                posD.list.splice(posD.index + 1, 0, cloneD);
                renderCanvas();
                emitCode();
                selectItem(cloneD);
                return;
            }
            if (btn.hasAttribute('data-fb-copy-item')) {
                clipboard = JSON.parse(JSON.stringify(item));
                var msg = document.querySelector('[data-fb-copy-msg]');
                if (msg) { msg.textContent = 'Element in Zwischenablage'; setTimeout(function () { msg.textContent = ''; }, 1500); }
                return;
            }
            if (btn.hasAttribute('data-fb-paste-after')) {
                if (!clipboard) {
                    alert('Zwischenablage ist leer. Erst auf Kopieren druecken.');
                    return;
                }
                var posP = findParentList(uid);
                if (!posP) return;
                var cloneP = deepCloneWithNewIds(clipboard);
                renderCanvas(); // ensure DOM in sync before depth check
                posP.list.splice(posP.index + 1, 0, cloneP);
                renderCanvas();
                emitCode();
                selectItem(cloneP);
                return;
            }
        });

        function renderCanvas() {
            // Destroy any existing nested Sortable instances before wiping the DOM.
            // Otherwise their internal state can re-insert stale clones on subsequent drags.
            $canvas.querySelectorAll('[data-fb-nested]').forEach(function (el) {
                var s = (typeof Sortable !== 'undefined' && Sortable.get) ? Sortable.get(el) : null;
                if (s) { try { s.destroy(); } catch (e) {} }
            });
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

            // tinymceProfile-Group nur sichtbar wenn TinyMCE aktiviert ist
            var profileGroup = $propsForm.querySelector('[data-fb-prop-group="tinymceProfile"]');
            if (profileGroup && !activeItem.tinymce) {
                profileGroup.style.display = 'none';
            }

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
            if (key === 'tinymce') {
                renderProps();
            }
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
            // Always defer: let Sortable finish its DOM mutations, then re-render from state.
            var doRender = function () { renderCanvas(); emitCode(); };

            if (!toList) {
                if (evt.item.parentNode) evt.item.parentNode.removeChild(evt.item);
                setTimeout(doRender, 0);
                return;
            }

            if (fromPalette) {
                var type = evt.item.dataset.type;
                if (evt.item.parentNode) evt.item.parentNode.removeChild(evt.item);
                if (!type) { setTimeout(doRender, 0); return; }
                if (type === 'repeater' && depthOf(evt.to) > MAX_REPEATER_DEPTH) {
                    alert('Mehr als ' + (MAX_REPEATER_DEPTH + 1) + ' Repeater-Ebenen werden nicht unterstuetzt.');
                    setTimeout(doRender, 0);
                    return;
                }
                if (type === 'tab' && depthOf(evt.to) > 0) {
                    alert('Tabs koennen nur auf der obersten Ebene platziert werden.');
                    setTimeout(doRender, 0);
                    return;
                }
                var newItem = makeItem(type);
                toList.splice(evt.newIndex, 0, newItem);
                setTimeout(function () { doRender(); selectItem(newItem); }, 0);
                return;
            }

            // Cross-list move within builder
            var uid = evt.item.dataset && evt.item.dataset.uid;
            if (!uid) { setTimeout(doRender, 0); return; }
            var item = findItem(uid);
            if (!item) { setTimeout(doRender, 0); return; }

            if (item.type === 'repeater' && depthOf(evt.to) > MAX_REPEATER_DEPTH) {
                alert('Mehr als ' + (MAX_REPEATER_DEPTH + 1) + ' Repeater-Ebenen werden nicht unterstuetzt.');
                setTimeout(doRender, 0);
                return;
            }
            if (item.type === 'tab' && depthOf(evt.to) > 0) {
                alert('Tabs koennen nur auf der obersten Ebene platziert werden.');
                setTimeout(doRender, 0);
                return;
            }

            removeItem(uid);
            toList.splice(evt.newIndex, 0, item);
            setTimeout(doRender, 0);
        }

        // Safety net: if Sortable removes an item from a list and onAdd never fires
        // on a known destination (e.g. dropped outside any drop zone), the state is
        // still intact but the DOM lost the node. A deferred renderCanvas heals this.
        function handleSortRemove() {
            setTimeout(function () { renderCanvas(); }, 0);
        }

        function handleSortUpdate(evt) {
            var list = listFor(evt.from);
            if (!list) return;
            if (evt.oldIndex === evt.newIndex) return;
            var moved = list.splice(evt.oldIndex, 1)[0];
            list.splice(evt.newIndex, 0, moved);
            setTimeout(function () { renderCanvas(); emitCode(); }, 0);
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

        document.querySelector('[data-fb-action="copy-output"]').addEventListener('click', function () {
            navigator.clipboard.writeText($output.textContent).then(function () {
                var msg = document.querySelector('[data-fb-copy-output-msg]');
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
            if (item.notice) a.notice = item.notice;
            if (item.rows && item.type === 'textarea') a.rows = item.rows;
            if (item.btnAdd && item.type === 'customlinkmultiple') a.btn_add = item.btnAdd;

            // CSS class merge: tiny-editor + user classes
            var classes = [];
            if (item.tinymce && item.type === 'textarea') {
                classes.push('form-control', 'tiny-editor');
            }
            if (item.cssClass) classes.push(item.cssClass);
            if (classes.length) a['class'] = classes.join(' ');

            // TinyMCE Profil
            if (item.tinymce && item.tinymceProfile && item.type === 'textarea') {
                a['data-profile'] = item.tinymceProfile;
            }

            // CustomLink data-* toggles
            if (item.type === 'customlink' || item.type === 'customlinkmultiple') {
                a['data-intern'] = item.clTypeIntern ? 'enable' : 'disable';
                a['data-extern'] = item.clTypeExtern ? 'enable' : 'disable';
                a['data-media']  = item.clTypeMedia  ? 'enable' : 'disable';
                a['data-mailto'] = item.clTypeMailto ? 'enable' : 'disable';
                a['data-tel']    = item.clTypeTel    ? 'enable' : 'disable';
                if (item.linkCategory)  a['data-link-category']  = item.linkCategory;
                if (item.mediaCategory) a['data-media-category'] = item.mediaCategory;
                if (item.externPrefix)  a['data-extern-link-prefix'] = item.externPrefix;
                if (item.mediaType)     a['data-media-type'] = item.mediaType;
            } else if ((item.type === 'media' || item.type === 'medialist') && item.mediaType) {
                a['data-media-type'] = item.mediaType;
            }
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

        function renderTabStmt(item, indent, isFirst) {
            var inner = renderRepeaterInner(item, indent);
            var label = item.label || '';
            var openTab = isFirst ? 'true' : 'false';
            var args = phpStr(label) + ', MForm::factory()\n' + inner;
            // Drop trailing args we can default; only emit pullRight if true
            if (item.tabPullRight) {
                return indent + '$mform->addTabElement(' + args + ', ' + openTab + ', true);';
            }
            if (isFirst) {
                return indent + '$mform->addTabElement(' + args + ', true);';
            }
            return indent + '$mform->addTabElement(' + args + ');';
        }

        function repeaterCfg(item) {
            var minVal = item.repeaterMin !== '' ? parseInt(item.repeaterMin, 10) : null;
            var maxVal = item.repeaterMax !== '' ? parseInt(item.repeaterMax, 10) : null;
            var defCnt = item.repeaterDefaultCount !== '' ? parseInt(item.repeaterDefaultCount, 10) : null;
            var parts = [];
            if (minVal !== null && !isNaN(minVal)) parts.push("'min' => " + minVal);
            if (maxVal !== null && !isNaN(maxVal)) parts.push("'max' => " + maxVal);
            if (defCnt !== null && !isNaN(defCnt)) parts.push("'default_count' => " + defCnt);
            if (item.label) parts.push("'label' => " + phpStr(item.label));
            // Flags only emit when DIFFERENT from MForm core default.
            // Core defaults: collapsed=false, first_open=false, show_toggle_all=true,
            //                open=true, copy_paste=true, confirm_delete=true.
            if (item.repeaterCollapsed === true) parts.push("'collapsed' => true");
            if (item.repeaterFirstOpen === true) parts.push("'first_open' => true");
            if (item.repeaterShowToggleAll === false) parts.push("'show_toggle_all' => false");
            if (item.repeaterOpen === false) parts.push("'open' => false");
            if (item.repeaterCopyPaste === false) parts.push("'copy_paste' => false");
            if (item.repeaterConfirmDelete === false) parts.push("'confirm_delete' => false");
            if (item.repeaterConfirmDeleteMsg) parts.push("'confirm_delete_msg' => " + phpStr(item.repeaterConfirmDeleteMsg));
            if (item.repeaterBtnText) parts.push("'btn_text' => " + phpStr(item.repeaterBtnText));
            if (item.repeaterBtnClass) parts.push("'btn_class' => " + phpStr(item.repeaterBtnClass));
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
            state.forEach(function (item, idx) {
                if (item.type === 'repeater') {
                    lines.push(renderRepeaterStmt(item, item.id, ''));
                } else if (item.type === 'tab') {
                    // First tab in a contiguous run is opened by default.
                    var isFirstTab = idx === 0 || state[idx - 1].type !== 'tab';
                    lines.push(renderTabStmt(item, '', isFirstTab));
                } else {
                    lines.push(renderField(item, item.id, '') + ';');
                }
            });
            lines.push('');
            lines.push('echo $mform->show();');
            $code.textContent = lines.join('\n');
            emitOutputCode();
        }

        // ---- Output-Code (Modul-Output) -------------------------------------
        //
        // Erzeugt rein PHP-Variablen-Vorbereitung: kein HTML, kein rex_escape.
        // Der Entwickler bekommt fertig gefuellte Variablen / Arrays und kann
        // dann seine Ausgabe selbst stricken.

        function rexVarFor(type, id) {
            if (type === 'media') return 'REX_MEDIA[' + id + ']';
            if (type === 'medialist') return 'REX_MEDIALIST[' + id + ']';
            if (type === 'link') return 'REX_LINK[' + id + ']';
            if (type === 'linklist') return 'REX_LINK_LIST[' + id + ']';
            return 'REX_VALUE[' + id + ']';
        }

        function childKeyFor(child, usedKeys) {
            var key = slugify(child.label, 'field_' + child.id);
            var base = key, n = 2;
            while (usedKeys[key]) { key = base + '_' + n++; }
            usedKeys[key] = true;
            return key;
        }

        function varNameFor(item) {
            var slug = slugify(item.label, item.type + '_' + item.id);
            return slug.replace(/[^a-z0-9_]/gi, '_');
        }

        // Liefert die rechte Seite einer Variablen-Zuweisung fuer Top-Level-Felder.
        function topLevelExpr(item) {
            var rv = '"' + rexVarFor(item.type, item.id) + '"';
            switch (item.type) {
                case 'medialist':
                case 'imagelist':
                case 'linklist':
                case 'customlinkmultiple':
                case 'checkbox':
                    // kommagetrennte Listen -> Array
                    return 'array_filter(explode(",", ' + rv + '))';
                case 'link':
                    return '(int) ' + rv;
                default:
                    return rv;
            }
        }

        // Inner-Repeater: rechte Seite, Daten kommen aus $row[key].
        function childExpr(child, key) {
            var access = '$row[' + phpStr(key) + '] ?? ""';
            switch (child.type) {
                case 'medialist':
                case 'imagelist':
                case 'linklist':
                case 'customlinkmultiple':
                case 'checkbox':
                    return 'array_filter(explode(",", (string) (' + access + ')))';
                case 'link':
                    return '(int) (' + access + ')';
                case 'repeater':
                    return '(array) (' + access + ')';
                default:
                    return access;
            }
        }

        function renderTopLevelVar(item) {
            return '$' + varNameFor(item) + ' = ' + topLevelExpr(item) + ';';
        }

        function renderRepeaterBlock(item, indent, rowVar) {
            indent = indent || '';
            rowVar = rowVar || 'row';
            var lines = [];
            var src;
            if (rowVar === 'row') {
                // Top-Level Repeater: aus REX_VALUE dekodieren
                src = '\\FriendsOfRedaxo\\MForm\\Repeater\\MFormRepeaterHelper::decode("REX_VALUE[' + item.id + ']")';
                lines.push(indent + '$' + varNameFor(item) + ' = ' + src + ';');
                lines.push(indent + 'foreach ($' + varNameFor(item) + ' as $' + rowVar + ') {');
            } else {
                // Verschachtelter Repeater: bereits Array
                lines.push(indent + 'foreach ((array) ($row[' + phpStr(item.__childKey) + '] ?? []) as $' + rowVar + ') {');
            }

            var inner = indent + '    ';
            var usedKeys = {};
            (item.children || []).forEach(function (c) {
                var key = childKeyFor(c, usedKeys);
                if (c.type === 'repeater') {
                    c.__childKey = key;
                    var sub = renderRepeaterBlock(c, inner, rowVar === 'row' ? 'row2' : 'row3');
                    // Ersetzen $row[ -> $rowVar[ in dem Sub-Block (greift nur die gefuellten ?? Zugriffe)
                    if (rowVar !== 'row') {
                        sub = sub.replace(/\$row\[/g, '$' + rowVar + '[');
                    }
                    lines.push(sub);
                    delete c.__childKey;
                } else {
                    var line = inner + '$' + key + ' = ' + childExpr(c, key) + ';';
                    if (rowVar !== 'row') {
                        line = line.replace(/\$row\[/g, '$' + rowVar + '[');
                    }
                    lines.push(line);
                }
            });
            lines.push(indent + '}');
            return lines.join('\n');
        }

        function emitOutputCode() {
            if (state.length === 0) {
                $output.textContent = '// Noch keine Felder hinzugefuegt.';
                return;
            }
            var lines = [];
            lines.push('// Modul-Output: Variablen aus den Eingabefeldern befuellen.');
            lines.push('// Ausgabe / HTML / Escaping nach Bedarf selbst ergaenzen.');
            lines.push('');
            state.forEach(function (item) {
                if (item.type === 'tab') {
                    if (item.label) lines.push('// ----- Tab: ' + item.label + ' -----');
                    (item.children || []).forEach(function (c) {
                        if (c.type === 'repeater') {
                            lines.push(renderRepeaterBlock(c, ''));
                        } else if (c.type === 'headline' || c.type === 'description') {
                            // sind reine Form-Strukturhinweise, ueberspringen
                        } else {
                            lines.push(renderTopLevelVar(c));
                        }
                    });
                } else if (item.type === 'repeater') {
                    lines.push(renderRepeaterBlock(item, ''));
                } else if (item.type === 'headline' || item.type === 'description') {
                    // ueberspringen
                } else {
                    lines.push(renderTopLevelVar(item));
                }
            });
            $output.textContent = lines.join('\n');
        }

        // ---- Init -----------------------------------------------------------

        renderCanvas();
        emitCode();
    } // end run()
})();
