/**
 * MForm FlexRepeater
 *
 * Vanilla-JS Repeater ohne Alpine.js-Abhängigkeit.
 * TinyMCE-kompatibel: destroy-before-move, reinit-after-move.
 * Drag-and-Drop via SortableJS (lokal bereitgestellt).
 *
 * Datenstruktur ist rückwärtskompatibel zur bisherigen Alpine-Implementierung.
 *
 * @author MForm Contributors
 * @license MIT
 */
/* global Sortable, tinymce, $, initMFormElements, saveTinyEditorContent, cke5_get_editors */

(function () {
    'use strict';

    const MFR_LOG_PREFIX = '[MFR]';
    const MFR_ITEM_DISABLED_KEY = '__disabled';

    function isGlobalDebugEnabled() {
        return window.MFR_DEBUG === true;
    }

    function isContainerDebugEnabled(container) {
        if (isGlobalDebugEnabled()) return true;
        if (!container) return false;
        return container.dataset && container.dataset.mfrDebug === '1';
    }

    function mfrLog(debugEnabled, label) {
        if (!debugEnabled) return;
        const args = Array.prototype.slice.call(arguments, 2);
        console.log.apply(console, [MFR_LOG_PREFIX + ' ' + label].concat(args));
    }

    // -------------------------------------------------------------------------
    // Hilfsfunktionen
    // -------------------------------------------------------------------------

    let _idSeq = 0;

    function uid(prefix) {
        return (prefix || 'mfr') + '_' + (++_idSeq) + '_' + Math.random().toString(36).slice(2, 7);
    }

    function getScrollOffset() {
        const nav = document.querySelector('#rex-js-nav-top');
        const navHeight = nav ? nav.getBoundingClientRect().height : 0;
        return Math.max(0, Math.round(navHeight + 14));
    }

    function scrollElementIntoViewWithOffset(el, center) {
        if (!el) return;
        const rect = el.getBoundingClientRect();
        const currentY = window.pageYOffset || document.documentElement.scrollTop || 0;
        const offset = getScrollOffset();
        const targetY = center
            ? (rect.top + currentY - Math.max(offset, Math.round(window.innerHeight * 0.22)))
            : (rect.top + currentY - offset);

        window.scrollTo({
            top: Math.max(0, targetY),
            behavior: 'smooth'
        });
    }

    function focusFirstEditableField(itemEl) {
        if (!itemEl) return;
        const field = itemEl.querySelector('input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]), textarea, select');
        if (!field) return;
        window.setTimeout(function () {
            try {
                field.focus({ preventScroll: true });
            } catch (e) {
                field.focus();
            }
        }, 220);
    }

    function setCollapseIconState(iconEl, isCollapsed) {
        if (!iconEl) return;
        iconEl.classList.toggle('fa-square-o', !!isCollapsed);
        iconEl.classList.toggle('fa-minus', !isCollapsed);
    }

    function flashAndRevealItem(itemEl, center, openBody) {
        if (!itemEl) return;

        if (openBody) {
            const body = itemEl.querySelector('.mfr-item-body, .mfr-nested-body');
            if (body) {
                body.style.display = '';
            }
            const icon = itemEl.querySelector('.mfr-btn-collapse i, .mfr-btn-nested-toggle i');
            setCollapseIconState(icon, false);
        }

        itemEl.classList.remove('mfr-item-flash');
        // reflow for restarting animation if needed
        void itemEl.offsetWidth;
        itemEl.classList.add('mfr-item-flash');
        window.setTimeout(function () {
            itemEl.classList.remove('mfr-item-flash');
        }, 950);

        scrollElementIntoViewWithOffset(itemEl, !!center);
        focusFirstEditableField(itemEl);
    }

    function resolveSortableUrl() {
        const script = document.currentScript || Array.from(document.getElementsByTagName('script')).find(function (s) {
            return typeof s.src === 'string' && s.src.indexOf('/addons/mform/assets/js/flex-repeater.js') !== -1;
        });
        if (!script || !script.src) return '';
        return script.src.replace(/flex-repeater\.js(?:\?.*)?$/, 'sortable.min.js');
    }

    function ensureSortableLoaded(callback) {
        if (typeof Sortable !== 'undefined') {
            callback();
            return;
        }

        const existing = document.querySelector('script[data-mfr-sortable-loader="1"]');
        if (existing) {
            document.addEventListener('mfr:sortable-ready', function handler() {
                document.removeEventListener('mfr:sortable-ready', handler);
                callback();
            });
            return;
        }

        const src = resolveSortableUrl();
        if (!src) {
            callback();
            return;
        }

        const script = document.createElement('script');
        script.src = src;
        script.async = false;
        script.dataset.mfrSortableLoader = '1';
        script.onload = function () {
            document.dispatchEvent(new CustomEvent('mfr:sortable-ready'));
            callback();
        };
        script.onerror = function () {
            console.warn('[MFR] Sortable could not be loaded:', src);
            callback();
        };
        document.head.appendChild(script);
    }

    /**
     * Speichert alle aktiven TinyMCE-Inhalte zurück in die Textareas.
     * Delegiert an die tinymce-Addon-Funktion, falls vorhanden.
     */
    function saveTinyMCE() {
        mfrLog(isGlobalDebugEnabled(), 'saveTinyMCE start', {
            hasTinySaveFn: typeof saveTinyEditorContent === 'function',
            hasTinyMCE: typeof tinymce !== 'undefined'
        });
        if (typeof saveTinyEditorContent === 'function') {
            saveTinyEditorContent();
            mfrLog(isGlobalDebugEnabled(), 'saveTinyMCE via saveTinyEditorContent() done');
            return;
        }
        if (typeof tinymce === 'undefined') return;
        try {
            tinymce.editors.forEach(function (editor) {
                if (!editor || !editor.targetElm) return;
                const content = editor.getContent();
                if (editor.targetElm.tagName === 'TEXTAREA') {
                    editor.targetElm.value = content;
                } else {
                    editor.targetElm.innerHTML = content;
                }
            });
            mfrLog(isGlobalDebugEnabled(), 'saveTinyMCE direct tiny loop done', { count: tinymce.editors.length });
        } catch (e) { /* silent */ }
    }

    /**
     * Entfernt TinyMCE-Instanzen innerhalb eines Elements.
     */
    function destroyTinyMCE(el) {
        if (typeof tinymce === 'undefined') return;
        el.querySelectorAll('.tiny-editor[id]').forEach(function (textarea) {
            const editor = tinymce.get(textarea.id);
            if (editor) {
                try { editor.remove(); } catch (e) { /* silent */ }
            }
            textarea.classList.remove('mce-initialized');
        });
        // TinyMCE hinterlässt manchmal .tox-Wrapper außerhalb – entfernen
        el.querySelectorAll('.tox.tox-tinymce').forEach(function (wrap) {
            wrap.remove();
        });
    }

    /**
     * Gibt TinyMCE-Instanzen innerhalb eines Elements eindeutige IDs
     * und triggert anschließend rex:ready zur Initialisierung aller Widgets.
     */
    function initWidgets(el) {
        mfrLog(isContainerDebugEnabled(el.closest ? el.closest('.mfr-container') : null), 'initWidgets', el);
        el.querySelectorAll('.tiny-editor').forEach(function (textarea) {
            if (!textarea.id) {
                textarea.id = uid('tiny');
            }
            textarea.classList.remove('mce-initialized');
        });
        el.querySelectorAll('.cke5-editor').forEach(function (textarea) {
            if (!textarea.id) {
                textarea.id = uid('cke5');
            }
        });
        if (typeof $ !== 'undefined') {
            const $el = $(el);
            $el.trigger('rex:ready', [$el]);
            if (typeof initMFormElements === 'function') {
                initMFormElements($el);
            }
        }
    }

    /**
     * Liest den aktuellen Wert eines Formularelements.
     * Gibt `null` zurück für nicht-selektierte Radios (zum Überspringen).
     */
    function getFieldValue(field) {
        const tag = field.tagName.toLowerCase();
        if (tag === 'input') {
            if (field.type === 'checkbox') return field.checked ? (field.value || '1') : '';
            if (field.type === 'radio') return field.checked ? field.value : null;
        }
        if (tag === 'select' && field.multiple) {
            return Array.from(field.selectedOptions).map(function (o) { return o.value; });
        }
        if (tag === 'textarea' && field.id && typeof tinymce !== 'undefined') {
            const editor = tinymce.get(field.id);
            if (editor) return editor.getContent();
        }
        if (tag === 'textarea' && field.id && field.classList.contains('cke5-editor') && typeof cke5_get_editors === 'function') {
            const editors = cke5_get_editors();
            if (editors && editors[field.id] && typeof editors[field.id].getData === 'function') {
                return editors[field.id].getData();
            }
        }
        return field.value !== undefined ? field.value : '';
    }

    /**
     * Setzt den Wert eines Formularelements.
     */
    function setFieldValue(field, value) {
        if (value === undefined || value === null) return;
        const tag = field.tagName.toLowerCase();
        if (tag === 'input') {
            if (field.type === 'checkbox') {
                const normalized = typeof value === 'string' ? value.trim().toLowerCase() : value;
                field.checked = !(
                    normalized === '' ||
                    normalized === false ||
                    normalized === 0 ||
                    normalized === '0' ||
                    normalized === 'false' ||
                    normalized === 'off' ||
                    normalized === 'no'
                );
                return;
            }
            if (field.type === 'radio') {
                field.checked = (field.value === String(value));
                return;
            }
        }
        if (tag === 'select' && field.multiple) {
            const vals = Array.isArray(value) ? value : [String(value)];
            Array.from(field.options).forEach(function (o) {
                o.selected = vals.includes(o.value);
            });
            return;
        }
        if (tag === 'textarea' && field.id && typeof tinymce !== 'undefined') {
            const editor = tinymce.get(field.id);
            if (editor) {
                editor.setContent(String(value));
                return;
            }
        }
        field.value = String(value);
    }

    /**
     * Liest alle [data-mfr-field]-Werte aus einem Item-Element
     * (direkte Felder, keine nested Repeater).
     */
    function collectItemData(itemEl) {
        const data = {};

        itemEl.querySelectorAll('[data-mfr-field]').forEach(function (field) {
            // Felder in nested Repeatern überspringen
            if (field.closest('.mfr-nested-repeater')) return;

            const key = field.dataset.mfrField;
            const val = getFieldValue(field);

            if (val === null) {
                // radio nicht selektiert → Schlüssel sichern (leer), wenn noch nicht gesetzt
                if (data[key] === undefined) data[key] = '';
                return;
            }
            data[key] = val;
        });

        // Nested Repeater
        itemEl.querySelectorAll(':scope .mfr-nested-repeater').forEach(function (nested) {
            if (nested.closest('.mfr-item') !== itemEl) return; // nur direkte Kind-Nested
            const key = nested.dataset.mfrField;
            data[key] = collectNestedItems(nested);
        });

        if (itemEl.dataset.mfrDisabled === '1') {
            data[MFR_ITEM_DISABLED_KEY] = 1;
        }

        return data;
    }

    function collectNestedItems(nestedEl) {
        const items = [];
        const list = nestedEl.querySelector('.mfr-nested-items');
        if (!list) return items;
        list.querySelectorAll(':scope > .mfr-nested-item').forEach(function (nestedItem) {
            const itemData = {};
            nestedItem.querySelectorAll('[data-mfr-field]').forEach(function (field) {
                const key = field.dataset.mfrField;
                const val = getFieldValue(field);
                if (val === null) {
                    if (itemData[key] === undefined) itemData[key] = '';
                    return;
                }
                itemData[key] = val;
            });
            items.push(itemData);
        });
        return items;
    }

    /**
     * Extrahiert einen lesbaren Titel-String aus einem Datenobjekt.
     */
    function extractTitle(data, index) {
        for (const key of Object.keys(data)) {
            const val = data[key];
            if (typeof val === 'string' && val.trim()) {
                // HTML-Tags entfernen
                const tmp = document.createElement('div');
                tmp.innerHTML = val;
                const text = (tmp.textContent || tmp.innerText || '').trim();
                if (text) return text.length > 55 ? text.slice(0, 55) + '…' : text;
            }
        }
        return '#' + (index + 1);
    }

    // -------------------------------------------------------------------------
    // MFormNestedRepeater – Level 2 (verschachtelt)
    // -------------------------------------------------------------------------

    class MFormNestedRepeater {
        constructor(container, data, onChange) {
            this.container = container;
            this.data = Array.isArray(data) ? data : [];
            this.onChange = onChange;

            this.itemsList = container.querySelector('.mfr-nested-items');
            this.template = container.querySelector('.mfr-nested-template');
            this.addBtn = container.querySelector('.mfr-btn-add-nested');

            this._init();
        }

        _init() {
            this.data.forEach((d, i) => this._renderItem(d, i));

            if (this.addBtn) {
                this.addBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.addItem();
                });
            }

            this._sortableInitialized = false;
            this._initSortable();
        }

        _initSortable() {
            if (this._sortableInitialized) return;
            if (typeof Sortable === 'undefined' || !this.itemsList) return;
            Sortable.create(this.itemsList, {
                handle: '.mfr-nested-drag',
                animation: 150,
                ghostClass: 'mfr-item-ghost',
                onEnd: (evt) => {
                    if (evt.oldIndex === evt.newIndex) return;
                    const [moved] = this.data.splice(evt.oldIndex, 1);
                    this.data.splice(evt.newIndex, 0, moved);
                    this.onChange && this.onChange();
                }
            });
            this._sortableInitialized = true;
        }

        addItem(data) {
            const d = data || {};
            this.data.push(d);
            const itemEl = this._renderItem(d, this.data.length - 1);
            this._focusNewItem(itemEl, false);
            this.onChange && this.onChange();
        }

        insertItemAfter(index, data) {
            if (index < 0) return;
            const d = data || {};
            const insertIndex = index + 1;
            this.data.splice(insertIndex, 0, d);

            const insertedEl = this._renderItem(d, insertIndex);
            if (insertedEl) {
                const children = Array.from(this.itemsList.children);
                const ref = children[insertIndex + 1];
                if (ref) ref.before(insertedEl);
                this._focusNewItem(insertedEl, true);
            }

            this._reindex();
            this.onChange && this.onChange();
        }

        _renderItem(data, index) {
            if (!this.template) return;

            const clone = this.template.content.cloneNode(true);
            const itemEl = clone.querySelector('.mfr-nested-item');
            if (!itemEl) return;

            itemEl.dataset.mfrIndex = String(index);

            // Felder befüllen
            itemEl.querySelectorAll('[data-mfr-field]').forEach(function (field) {
                const key = field.dataset.mfrField;
                if (data[key] !== undefined) setFieldValue(field, data[key]);
            });

            // Titel setzen
            this._updateTitle(itemEl, data, index);

            // Erstes Item offen, weitere standardmäßig zugeklappt
            const body = itemEl.querySelector('.mfr-nested-body');
            const shouldCollapse = index > 0;
            if (body) body.style.display = shouldCollapse ? 'none' : '';
            const icon = itemEl.querySelector('.mfr-btn-nested-toggle i');
            setCollapseIconState(icon, shouldCollapse);

            this._bindItemEvents(itemEl, body);
            this.itemsList.appendChild(clone);

            // Widgets initialisieren
            initWidgets(itemEl);

            return itemEl;
        }

        _updateTitle(itemEl, data, index) {
            const titleEl = itemEl.querySelector('.mfr-nested-title');
            if (titleEl) titleEl.textContent = extractTitle(data, index);
        }

        _bindItemEvents(itemEl, body) {
            // Toggle
            const toggleBtn = itemEl.querySelector('.mfr-btn-nested-toggle');
            if (toggleBtn && body) {
                toggleBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const open = body.style.display !== 'none';
                    body.style.display = open ? 'none' : '';
                    const icon = toggleBtn.querySelector('i');
                    setCollapseIconState(icon, body.style.display === 'none');
                });
            }

            // Add after
            const addAfterBtn = itemEl.querySelector('.mfr-btn-nested-add-after');
            if (addAfterBtn) {
                addAfterBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const idx = this._indexOf(itemEl);
                    if (idx === -1) return;
                    this.insertItemAfter(idx, {});
                });
            }

            // Remove
            const removeBtn = itemEl.querySelector('.mfr-btn-nested-remove');
            if (removeBtn) {
                removeBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const idx = this._indexOf(itemEl);
                    itemEl.classList.add('mfr-item-removing');
                    window.setTimeout(() => {
                        if (idx !== -1) this.data.splice(idx, 1);
                        itemEl.remove();
                        this.onChange && this.onChange();
                    }, 180);
                });
            }

            // Up
            const upBtn = itemEl.querySelector('.mfr-btn-nested-up');
            if (upBtn) {
                upBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const idx = this._indexOf(itemEl);
                    if (idx <= 0) return;
                    this._swapItems(idx, idx - 1);
                });
            }

            // Down
            const downBtn = itemEl.querySelector('.mfr-btn-nested-down');
            if (downBtn) {
                downBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const idx = this._indexOf(itemEl);
                    if (idx < 0 || idx >= this.itemsList.children.length - 1) return;
                    this._swapItems(idx, idx + 1);
                });
            }

            // Feldänderungen → Daten aktualisieren
            itemEl.querySelectorAll('[data-mfr-field]').forEach((field) => {
                const evt = (field.tagName.toLowerCase() === 'select' || field.type === 'checkbox' || field.type === 'radio') ? 'change' : 'input';
                field.addEventListener(evt, () => {
                    const idx = this._indexOf(itemEl);
                    if (idx !== -1) {
                        const val = getFieldValue(field);
                        if (val !== null) this.data[idx][field.dataset.mfrField] = val;
                        this._updateTitle(itemEl, this.data[idx], idx);
                    }
                    this.onChange && this.onChange();
                });
            });
        }

        _swapItems(fromIdx, toIdx) {
            const items = Array.from(this.itemsList.children);
            if (fromIdx < toIdx) {
                items[toIdx].after(items[fromIdx]);
            } else {
                items[toIdx].before(items[fromIdx]);
            }
            const [moved] = this.data.splice(fromIdx, 1);
            this.data.splice(toIdx, 0, moved);
            this.onChange && this.onChange();
        }

        _indexOf(itemEl) {
            return Array.from(this.itemsList.children).indexOf(itemEl);
        }

        _reindex() {
            Array.from(this.itemsList.children).forEach((item, idx) => {
                item.dataset.mfrIndex = String(idx);
                if (this.data[idx]) {
                    this._updateTitle(item, this.data[idx], idx);
                }
            });
        }

        _focusNewItem(itemEl, insertedInMiddle) {
            flashAndRevealItem(itemEl, !!insertedInMiddle, true);
        }
    }

    // -------------------------------------------------------------------------
    // MFormFlexRepeater – Haupt-Klasse (Level 1)
    // -------------------------------------------------------------------------

    class MFormFlexRepeater {
        constructor(container) {
            this.container = container;
            this.fieldName = container.dataset.mfrFieldName;
            this.min = parseInt(container.dataset.mfrMin, 10) || 0;
            this.max = parseInt(container.dataset.mfrMax, 10) || 0;
            this.collapsed = container.dataset.mfrCollapsed === 'true';
            this.firstOpen = container.dataset.mfrFirstOpen !== 'false';
            this.showToggleAll = container.dataset.mfrShowToggleAll !== 'false';
            this.defaultCount = parseInt(container.dataset.mfrDefaultCount, 10) || 0;
            this.confirmDelete = container.dataset.mfrConfirmDelete === '1';
            this.confirmDeleteMsg = container.dataset.mfrConfirmDeleteMsg || 'Wirklich löschen?';
            this.copyPaste = container.dataset.mfrCopyPaste === '1';

            this.itemsList = container.querySelector('.mfr-items-list');
            this.template = container.querySelector('template.mfr-item-template');
            this.valueInput = container.querySelector('.mfr-value');
            this.addBtns = this._getToolbarButtons('.mfr-btn-add');
            this.toggleAllBtns = this._getToolbarButtons('.mfr-btn-toggle-all');
            this.debug = isContainerDebugEnabled(container);
            this.debugId = this.fieldName || container.id || 'unknown';

            this.data = [];
            this._suppressSync = false;
            this._isBootstrapping = false;

            this._log('constructor', {
                fieldName: this.fieldName,
                valueInputName: this.valueInput ? this.valueInput.name : null,
                min: this.min,
                max: this.max,
                defaultCount: this.defaultCount,
                collapsed: this.collapsed,
                firstOpen: this.firstOpen,
                showToggleAll: this.showToggleAll,
                addButtons: this.addBtns.length,
                toggleAllButtons: this.toggleAllBtns.length,
                debug: this.debug
            });

            this._init();
        }

        _log(label) {
            if (!this.debug) return;
            const args = Array.prototype.slice.call(arguments, 1);
            console.log.apply(console, [MFR_LOG_PREFIX + ' [' + this.debugId + '] ' + label].concat(args));
        }

        _getToolbarButtons(selector) {
            const direct = Array.from(this.container.querySelectorAll(':scope > .mfr-toolbar ' + selector));
            if (direct.length > 0) return direct;

            // Fallback: robust against markup differences where :scope selectors fail
            return Array.from(this.container.querySelectorAll('.mfr-toolbar ' + selector)).filter((btn) => {
                return btn.closest('.mfr-container') === this.container;
            });
        }

        _init() {
            // Vorhandene Daten aus Hidden-Input laden
            try {
                const raw = this.valueInput ? this.valueInput.value : '';
                const parsed = raw ? JSON.parse(raw) : [];
                this.data = Array.isArray(parsed) ? parsed : [];
                this._log('_init parsed initial data', {
                    rawLength: raw ? raw.length : 0,
                    items: this.data.length,
                    rawPreview: raw ? raw.slice(0, 300) : ''
                });
            } catch (e) {
                this.data = [];
                this._log('_init parse failed, fallback []', e);
            }

            // Bestehende Items rendern
            this._suppressSync = true;
            this.data.forEach((d, i) => this._renderItem(d, i));
            this._suppressSync = false;

            // Default-Count/Min ohne Auto-Scroll aufbauen (z. B. beim Slice-Öffnen)
            this._isBootstrapping = true;
            while (this.data.length < this.defaultCount) {
                this.addItem();
            }
            while (this.min > 0 && this.data.length < this.min) {
                this.addItem();
            }
            this._isBootstrapping = false;

            // Toolbar-Events via Delegation, damit Buttons immer greifen
            this.container.addEventListener('click', (e) => {
                const addBtn = e.target.closest('.mfr-btn-add');
                if (addBtn && addBtn.closest('.mfr-container') === this.container) {
                    e.preventDefault();
                    this._log('add button clicked (delegated)');
                    this.addItem();
                    return;
                }

                const toggleAllBtn = e.target.closest('.mfr-btn-toggle-all');
                if (toggleAllBtn && toggleAllBtn.closest('.mfr-container') === this.container) {
                    e.preventDefault();
                    this._log('toggle all clicked (delegated)');
                    this._toggleAll();
                }

                // Paste button in toolbar → append at end
                const pasteBtn = e.target.closest('.mfr-btn-paste');
                if (pasteBtn && pasteBtn.closest('.mfr-container') === this.container) {
                    e.preventDefault();
                    this._pasteItem();
                }

                // Paste button on item → insert after that item
                const pasteAfterBtn = e.target.closest('.mfr-btn-paste-after');
                if (pasteAfterBtn && pasteAfterBtn.closest('.mfr-container') === this.container) {
                    e.preventDefault();
                    const itemEl = pasteAfterBtn.closest('.mfr-item');
                    const idx = itemEl ? Array.from(this.itemsList.children).indexOf(itemEl) : -1;
                    this._pasteItemAfter(idx);
                }
            });

            this._updateAddBtn();
            this._updateToggleAllButton();

            this._sortableInitialized = false;
            this._initSortable();

            // Show paste buttons if clipboard is already filled
            if (this.copyPaste) {
                try {
                    if (sessionStorage.getItem('mfr_clipboard')) {
                        this.container.querySelectorAll('.mfr-btn-paste, .mfr-btn-paste-after, .mfr-btn-paste-after').forEach(function (btn) {
                            btn.style.display = '';
                        });
                    }
                } catch (e) { /* ignore */ }
            }

            // Formular-Submit wird über den globalen Handler in initRepeaters abgewickelt
        }

        // ---------------------------------------------------------------------
        // Öffentliche API
        // ---------------------------------------------------------------------

        addItem(data) {
            if (this.max > 0 && this.data.length >= this.max) return;
            const d = data || this._emptyData();
            this.data.push(d);
            this._log('addItem', { newLength: this.data.length, data: d });
            const itemEl = this._renderItem(d, this.data.length - 1);
            if (!this._isBootstrapping) {
                this._focusNewItem(itemEl, false);
            }
            this._updateAddBtn();
            this._updateToggleAllButton();
            if (!this._suppressSync) this.syncValue();
        }

        insertItemAfter(index, data) {
            if (this.max > 0 && this.data.length >= this.max) return;
            if (index < 0) return;

            const d = data || this._emptyData();
            const insertIndex = index + 1;
            this.data.splice(insertIndex, 0, d);
            this._log('insertItemAfter', { index: index, insertIndex: insertIndex, data: d });

            const insertedEl = this._renderItem(d, insertIndex);
            if (insertedEl) {
                const children = Array.from(this.itemsList.children);
                const ref = children[insertIndex + 1];
                if (ref) ref.before(insertedEl);
                this._focusNewItem(insertedEl, true);
            }

            this._reindex();
            this._updateAddBtn();
            this._updateToggleAllButton();
            if (!this._suppressSync) this.syncValue();
        }

        syncFromDOM() {
            if (this._suppressSync) return;
            saveTinyMCE();
            const items = Array.from(this.itemsList.children);
            this.data = items.map((item) => collectItemData(item));
            this._log('syncFromDOM collected', {
                domItems: items.length,
                dataItems: this.data.length,
                data: this.data
            });
            this.syncValue();
        }

        syncValue() {
            if (!this.valueInput) return;
            const oldVal = this.valueInput.value;
            this.valueInput.value = JSON.stringify(this.data);
            this._log('syncValue wrote hidden input', {
                inputName: this.valueInput.name,
                oldLength: oldVal ? oldVal.length : 0,
                newLength: this.valueInput.value.length,
                newValue: this.valueInput.value
            });
        }

        // ---------------------------------------------------------------------
        // Interne Methoden
        // ---------------------------------------------------------------------

        _emptyData() {
            const data = {};
            if (!this.template) return data;
            const temp = this.template.content.cloneNode(true);
            temp.querySelectorAll('[data-mfr-field]').forEach(function (field) {
                if (!field.closest('.mfr-nested-repeater')) {
                    data[field.dataset.mfrField] = '';
                }
            });
            temp.querySelectorAll('.mfr-nested-repeater').forEach(function (nested) {
                data[nested.dataset.mfrField] = [];
            });
            return data;
        }

        _renderItem(data, index) {
            if (!this.template) return;
            this._log('_renderItem', { index: index, data: data });

            const clone = this.template.content.cloneNode(true);
            const itemEl = clone.querySelector('.mfr-item');
            if (!itemEl) return;

            itemEl.dataset.mfrIndex = String(index);

            // Felder befüllen (Textareas vor TinyMCE-Init)
            this._fillFields(itemEl, data);

            // Zugeklappt
            const shouldCollapse = this.collapsed && (!this.firstOpen || index > 0);
            if (shouldCollapse) {
                const body = itemEl.querySelector('.mfr-item-body');
                if (body) body.style.display = 'none';
                const icon = itemEl.querySelector('.mfr-btn-collapse i');
                setCollapseIconState(icon, true);
            } else {
                const icon = itemEl.querySelector('.mfr-btn-collapse i');
                setCollapseIconState(icon, false);
            }

            this._applyItemDisabledState(itemEl, this._isDisabledValue(data ? data[MFR_ITEM_DISABLED_KEY] : null));

            // Titel inkl. Status-Punkt setzen (nach Disabled-State, damit Initialfarbe korrekt ist)
            this._setTitle(itemEl, data, index);

            // Item in DOM einfügen BEVOR Nested Repeater und Widgets initialisiert werden
            this.itemsList.appendChild(clone);
            // Nach appendChild ist itemEl im DOM-Tree verankert

            // Nested Repeater initialisieren und Instanz auf Element speichern (für globalen TinyMCE-Listener)
            itemEl.querySelectorAll('.mfr-nested-repeater').forEach((nestedEl) => {
                const nestedKey = nestedEl.dataset.mfrField;
                const nestedData = (data && Array.isArray(data[nestedKey])) ? data[nestedKey] : [];
                nestedEl._mfrNestedInstance = new MFormNestedRepeater(nestedEl, nestedData, () => this.syncFromDOM());
            });

            // Aktionen binden
            this._bindActions(itemEl);

            // Feld-Listener binden
            this._bindFieldListeners(itemEl);

            // Widgets nach kurzem Delay initialisieren (TinyMCE braucht DOM-Stabilität)
            itemEl.querySelectorAll('.tiny-editor').forEach(function (ta) {
                if (!ta.id) ta.id = uid('tiny');
                ta.classList.remove('mce-initialized');
            });
            setTimeout(() => initWidgets(itemEl), 20);

            return itemEl;
        }

        _fillFields(itemEl, data) {
            if (!data) return;
            itemEl.querySelectorAll('[data-mfr-field]').forEach(function (field) {
                if (field.closest('.mfr-nested-repeater')) return;
                const key = field.dataset.mfrField;
                if (data[key] !== undefined && data[key] !== null) {
                    // TinyMCE-Textareas: Wert in textarea setzen, init läuft separat
                    if (field.tagName.toLowerCase() === 'textarea' && field.classList.contains('tiny-editor')) {
                        field.value = String(data[key]);
                        return;
                    }
                    setFieldValue(field, data[key]);
                }
            });
        }

        _setTitle(itemEl, data, index) {
            const titleEl = itemEl.querySelector('.mfr-item-title');
            if (!titleEl) return;

            const baseTitle = extractTitle(data, index);
            const isDisabled = itemEl && itemEl.dataset && itemEl.dataset.mfrDisabled === '1';

            // Titel-Text setzen (ohne "(deaktiviert)" – das übernimmt das Badge)
            titleEl.textContent = baseTitle;

            // Status-Punkt aktualisieren (immer sichtbar)
            let dot = titleEl.parentElement ? titleEl.parentElement.querySelector('.mfr-status-dot') : null;
            if (!dot) {
                dot = document.createElement('span');
                dot.className = 'mfr-status-dot';
                titleEl.insertAdjacentElement('beforebegin', dot);
            }
            dot.classList.toggle('mfr-status-dot--offline', isDisabled);
            dot.classList.toggle('mfr-status-dot--online', !isDisabled);
        }

        _bindFieldListeners(itemEl) {
            itemEl.querySelectorAll('[data-mfr-field]').forEach((field) => {
                if (field.closest('.mfr-nested-repeater')) return;
                const tag = field.tagName.toLowerCase();
                // Rich-Text-Textareas werden über globale Editor-Listener behandelt
                if (tag === 'textarea' && (field.classList.contains('tiny-editor') || field.classList.contains('cke5-editor'))) return;
                const evtType = (tag === 'select' || field.type === 'checkbox' || field.type === 'radio') ? 'change' : 'input';
                field.addEventListener(evtType, () => {
                    // Titel aktuell halten
                    const idx = this._indexOf(itemEl);
                    const val = getFieldValue(field);
                    if (val !== null && idx !== -1) {
                        if (!this.data[idx]) this.data[idx] = {};
                        this.data[idx][field.dataset.mfrField] = val;
                        this._setTitle(itemEl, this.data[idx], idx);
                        this._log('field change', {
                            eventType: evtType,
                            field: field.dataset.mfrField,
                            value: val,
                            index: idx
                        });
                    }
                    this.syncValue();
                });
            });
        }

        _bindActions(itemEl) {
            // Sichtbarkeit/Ausgabe aktiv|deaktiviert
            const visibilityBtn = itemEl.querySelector('.mfr-btn-visibility');
            if (visibilityBtn) {
                visibilityBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this._toggleItemVisibilityState(itemEl);
                    this.syncValue();
                });
            }

            // Toggle collapse
            const collapseBtn = itemEl.querySelector('.mfr-btn-collapse');
            if (collapseBtn) {
                collapseBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const body = itemEl.querySelector('.mfr-item-body');
                    if (!body) return;
                    const isHidden = body.style.display === 'none';
                    body.style.display = isHidden ? '' : 'none';
                    const icon = collapseBtn.querySelector('i');
                    setCollapseIconState(icon, body.style.display === 'none');
                });
            }

            // Nach diesem Element ein neues anlegen
            const addAfterBtn = itemEl.querySelector('.mfr-btn-add-after');
            if (addAfterBtn) {
                addAfterBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const idx = this._indexOf(itemEl);
                    if (idx < 0) return;
                    this.insertItemAfter(idx);
                });
            }

            // Item kopieren (in sessionStorage ablegen)
            const copyBtn = itemEl.querySelector('.mfr-btn-copy');
            if (copyBtn) {
                copyBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    saveTinyMCE();
                    const idx = this._indexOf(itemEl);
                    if (idx === -1) return;
                    const snapshot = JSON.parse(JSON.stringify(this.data[idx] || {}));
                    // Remove disabled marker so paste produces an enabled item
                    delete snapshot[MFR_ITEM_DISABLED_KEY];
                    try {
                        sessionStorage.setItem('mfr_clipboard', JSON.stringify(snapshot));
                    } catch (e2) {
                        window._mfrClipboard = snapshot;
                    }
                    // Show all paste buttons in this container
                    this.container.querySelectorAll('.mfr-btn-paste, .mfr-btn-paste-after').forEach(function (btn) {
                        btn.style.display = '';
                    });
                    // Visual feedback
                    copyBtn.classList.add('mfr-btn-copy-active');
                    window.setTimeout(function () { copyBtn.classList.remove('mfr-btn-copy-active'); }, 800);
                    this._log('copyItem', { idx: idx, snapshot: snapshot });
                });
            }

            // Item entfernen
            const removeBtn = itemEl.querySelector('.mfr-btn-remove');
            if (removeBtn) {
                removeBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (this.min > 0 && this.data.length <= this.min) return;
                    if (this.confirmDelete && !confirm(this.confirmDeleteMsg)) return;

                    saveTinyMCE();
                    destroyTinyMCE(itemEl);

                    itemEl.classList.add('mfr-item-removing');
                    window.setTimeout(() => {
                        const idx = this._indexOf(itemEl);
                        if (idx !== -1) this.data.splice(idx, 1);
                        this._log('remove item', { index: idx, newLength: this.data.length });
                        itemEl.remove();
                        this._reindex();
                        this._updateAddBtn();
                        this._updateToggleAllButton();
                        this.syncValue();
                    }, 180);
                });
            }

            // Nach oben
            const upBtn = itemEl.querySelector('.mfr-btn-up');
            if (upBtn) {
                upBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const idx = this._indexOf(itemEl);
                    if (idx <= 0) return;
                    this._moveItem(idx, idx - 1);
                });
            }

            // Nach unten
            const downBtn = itemEl.querySelector('.mfr-btn-down');
            if (downBtn) {
                downBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const idx = this._indexOf(itemEl);
                    if (idx < 0 || idx >= this.itemsList.children.length - 1) return;
                    this._moveItem(idx, idx + 1);
                });
            }
        }

        _pasteItem() {
            let snapshot = null;
            try {
                const raw = sessionStorage.getItem('mfr_clipboard');
                if (raw) snapshot = JSON.parse(raw);
            } catch (e) { /* ignore */ }
            if (!snapshot && window._mfrClipboard) {
                snapshot = window._mfrClipboard;
            }
            if (!snapshot) return;
            const data = JSON.parse(JSON.stringify(snapshot));
            this._log('pasteItem', { data: data });
            this.addItem(data);
        }

        _pasteItemAfter(idx) {
            let snapshot = null;
            try {
                const raw = sessionStorage.getItem('mfr_clipboard');
                if (raw) snapshot = JSON.parse(raw);
            } catch (e) { /* ignore */ }
            if (!snapshot && window._mfrClipboard) {
                snapshot = window._mfrClipboard;
            }
            if (!snapshot) return;
            const data = JSON.parse(JSON.stringify(snapshot));
            this._log('pasteItemAfter', { idx: idx, data: data });
            if (idx >= 0) {
                this.insertItemAfter(idx, data);
            } else {
                this.addItem(data);
            }
        }

        _moveItem(fromIdx, toIdx) {
            this._log('_moveItem', { from: fromIdx, to: toIdx });
            saveTinyMCE();
            const items = Array.from(this.itemsList.children);
            const fromEl = items[fromIdx];
            const toEl = items[toIdx];
            if (!fromEl || !toEl) return;

            destroyTinyMCE(fromEl);

            // DOM verschieben
            if (fromIdx < toIdx) {
                toEl.after(fromEl);
            } else {
                toEl.before(fromEl);
            }

            // Daten verschieben
            const [moved] = this.data.splice(fromIdx, 1);
            this.data.splice(toIdx, 0, moved);

            this._reindex();
            this.syncValue();
            this._updateToggleAllButton();

            setTimeout(() => initWidgets(fromEl), 50);
        }

        _initSortable() {
            if (this._sortableInitialized) return;
            if (typeof Sortable === 'undefined' || !this.itemsList) return;

            Sortable.create(this.itemsList, {
                handle: '.mfr-item-drag',
                animation: 150,
                ghostClass: 'mfr-item-ghost',
                chosenClass: 'mfr-item-chosen',
                dragClass: 'mfr-item-dragging',
                scroll: true,
                // Vor dem Ziehen: TinyMCE-Inhalt sichern und Instanz entfernen
                onStart: (evt) => {
                    this._log('sortable onStart', { oldIndex: evt.oldIndex, newIndex: evt.newIndex });
                    saveTinyMCE();
                    destroyTinyMCE(evt.item);
                },
                // Nach dem Loslassen: Daten-Array anpassen, TinyMCE neu initialisieren
                onEnd: (evt) => {
                    this._log('sortable onEnd', { oldIndex: evt.oldIndex, newIndex: evt.newIndex });
                    if (evt.oldIndex !== evt.newIndex) {
                        const [moved] = this.data.splice(evt.oldIndex, 1);
                        this.data.splice(evt.newIndex, 0, moved);
                        this._reindex();
                        this.syncValue();
                    }
                    setTimeout(() => initWidgets(evt.item), 50);
                }
            });

            this.itemsList.querySelectorAll('.mfr-nested-repeater').forEach(function (nestedEl) {
                if (nestedEl._mfrNestedInstance && typeof nestedEl._mfrNestedInstance._initSortable === 'function') {
                    nestedEl._mfrNestedInstance._initSortable();
                }
            });

            this._sortableInitialized = true;
        }

        _setCollapsedState(itemEl, collapsed) {
            const body = itemEl.querySelector('.mfr-item-body');
            if (!body) return;
            body.style.display = collapsed ? 'none' : '';
            const icon = itemEl.querySelector('.mfr-btn-collapse i');
            setCollapseIconState(icon, collapsed);
        }

        _toggleAll() {
            const items = Array.from(this.itemsList.children);
            const hasCollapsed = items.some(function (item) {
                const body = item.querySelector('.mfr-item-body');
                return body && body.style.display === 'none';
            });
            // Wenn mindestens ein Item zu ist -> alle auf; sonst alle zu.
            const collapseAll = !hasCollapsed;
            items.forEach((item) => this._setCollapsedState(item, collapseAll));
            this._updateToggleAllButton();
        }

        _updateToggleAllButton() {
            if (!this.toggleAllBtns || this.toggleAllBtns.length === 0) return;
            const items = Array.from(this.itemsList.children);
            const hasCollapsed = items.some(function (item) {
                const body = item.querySelector('.mfr-item-body');
                return body && body.style.display === 'none';
            });
            this.toggleAllBtns.forEach(function (btn) {
                const icon = btn.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-square-o', hasCollapsed);
                    icon.classList.toggle('fa-minus', !hasCollapsed);
                }
            });
        }

        _isDisabledValue(value) {
            if (value === true || value === 1 || value === '1') return true;
            if (typeof value === 'string') {
                const normalized = value.trim().toLowerCase();
                return normalized === 'true' || normalized === 'yes' || normalized === 'on';
            }
            return false;
        }

        _applyItemDisabledState(itemEl, isDisabled) {
            if (!itemEl) return;

            itemEl.dataset.mfrDisabled = isDisabled ? '1' : '0';
            itemEl.classList.toggle('mfr-item-disabled', isDisabled);

            const icon = itemEl.querySelector('.mfr-btn-visibility i');
            if (icon) {
                icon.classList.toggle('fa-eye', !isDisabled);
                icon.classList.toggle('fa-eye-slash', isDisabled);
            }

            const idx = this._indexOf(itemEl);
            if (idx !== -1) {
                if (!this.data[idx]) this.data[idx] = {};
                if (isDisabled) {
                    this.data[idx][MFR_ITEM_DISABLED_KEY] = 1;
                } else {
                    delete this.data[idx][MFR_ITEM_DISABLED_KEY];
                }
                this._setTitle(itemEl, this.data[idx], idx);
            }
        }

        _toggleItemVisibilityState(itemEl) {
            const currentlyDisabled = itemEl && itemEl.dataset && itemEl.dataset.mfrDisabled === '1';
            this._applyItemDisabledState(itemEl, !currentlyDisabled);
        }

        _focusNewItem(itemEl, insertedInMiddle) {
            flashAndRevealItem(itemEl, !!insertedInMiddle, true);
        }

        _reindex() {
            Array.from(this.itemsList.children).forEach((item, idx) => {
                item.dataset.mfrIndex = String(idx);
                const titleEl = item.querySelector('.mfr-item-title');
                if (titleEl && this.data[idx]) {
                    this._setTitle(item, this.data[idx], idx);
                }
            });
        }

        _updateAddBtn() {
            if (!this.addBtns || this.addBtns.length === 0) return;
            const hide = this.max > 0 && this.data.length >= this.max;
            this.addBtns.forEach(function (btn) {
                btn.style.display = hide ? 'none' : '';
            });
        }

        _indexOf(itemEl) {
            return Array.from(this.itemsList.children).indexOf(itemEl);
        }
    }

    // -------------------------------------------------------------------------
    // Initialisierung: alle .mfr-container auf der Seite / im Container
    // -------------------------------------------------------------------------

    function initRepeaters(root) {
        const scope = root instanceof Element ? root : document;
        scope.querySelectorAll('.mfr-container').forEach(function (el) {
            if (el._mfrInstance) return; // bereits initialisiert
            el._mfrInstance = new MFormFlexRepeater(el);
            mfrLog(isContainerDebugEnabled(el), 'initRepeaters created instance', {
                fieldName: el.dataset.mfrFieldName,
                valueInputName: el.querySelector('.mfr-value') ? el.querySelector('.mfr-value').name : null
            });
        });
    }

    // DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            ensureSortableLoaded(function () {
                initRepeaters(document);
                document.querySelectorAll('.mfr-container').forEach(function (container) {
                    if (container._mfrInstance) container._mfrInstance._initSortable();
                });
            });
        });
    } else {
        ensureSortableLoaded(function () {
            initRepeaters(document);
            document.querySelectorAll('.mfr-container').forEach(function (container) {
                if (container._mfrInstance) container._mfrInstance._initSortable();
            });
        });
    }

    document.addEventListener('mfr:sortable-ready', function () {
        document.querySelectorAll('.mfr-container').forEach(function (container) {
            if (container._mfrInstance) container._mfrInstance._initSortable();
        });
    });

    // REDAXO rex:ready (PJAX, MBlock, dynamische Inhalte)
    if (typeof $ !== 'undefined') {
        $(document).on('rex:ready', function (e, container) {
            const el = container ? (container[0] || container) : document;
            initRepeaters(el);
            // TinyMCE-Listener beim ersten rex:ready registrieren (tinymce ist dann geladen)
            setupGlobalTinyMCEListener();
        });
    }

    // -------------------------------------------------------------------------
    // Globaler TinyMCE AddEditor-Listener (einmalig, kein Leak)
    // -------------------------------------------------------------------------

    let _tinyListenerRegistered = false;

    function bindMfrEditor(editor) {
        if (!editor || !editor.targetElm) return;
        const textarea = editor.targetElm;
        const containerElForDebug = textarea.closest('.mfr-container');
        mfrLog(isContainerDebugEnabled(containerElForDebug), 'bindMfrEditor', {
            editorId: editor.id,
            field: textarea.dataset ? textarea.dataset.mfrField : null,
            textareaId: textarea.id,
            inNested: !!textarea.closest('.mfr-nested-item')
        });

        // Level-1 Repeater: textarea direkt in .mfr-item (nicht in nested)
        const itemEl = textarea.closest('.mfr-item');
        const nestedItemEl = textarea.closest('.mfr-nested-item');

        if (nestedItemEl) {
            // Level-2: nested Repeater
            const nestedEl = textarea.closest('.mfr-nested-repeater');
            if (!nestedEl || !nestedEl._mfrNestedInstance) return;
            const instance = nestedEl._mfrNestedInstance;
            editor.on('change input keyup', function () {
                const idx = instance._indexOf(nestedItemEl);
                if (idx === -1) return;
                if (!instance.data[idx]) instance.data[idx] = {};
                instance.data[idx][textarea.dataset.mfrField] = editor.getContent();
                instance._updateTitle(nestedItemEl, instance.data[idx], idx);
                mfrLog(isContainerDebugEnabled(containerElForDebug), 'tiny change (nested)', {
                    editorId: editor.id,
                    field: textarea.dataset.mfrField,
                    index: idx,
                    contentLength: editor.getContent().length
                });
                instance.onChange && instance.onChange();
            });
        } else if (itemEl) {
            // Level-1: Haupt-Repeater
            const containerEl = textarea.closest('.mfr-container');
            if (!containerEl || !containerEl._mfrInstance) return;
            const instance = containerEl._mfrInstance;
            editor.on('change input keyup', function () {
                const idx = instance._indexOf(itemEl);
                if (idx === -1) return;
                if (!instance.data[idx]) instance.data[idx] = {};
                instance.data[idx][textarea.dataset.mfrField] = editor.getContent();
                instance._setTitle(itemEl, instance.data[idx], idx);
                instance._log('tiny change', {
                    editorId: editor.id,
                    field: textarea.dataset.mfrField,
                    index: idx,
                    contentLength: editor.getContent().length
                });
                instance.syncValue();
            });
        }
    }

    function onMfrAddEditor(evt) {
        bindMfrEditor(evt.editor);
    }

    function setupGlobalTinyMCEListener() {
        if (_tinyListenerRegistered) return;
        if (typeof tinymce === 'undefined') return;
        tinymce.on('AddEditor', onMfrAddEditor);
        // Falls TinyMCE bereits initialisierte Editoren hat, diese nachtraeglich anbinden.
        if (Array.isArray(tinymce.editors)) {
            tinymce.editors.forEach(function (editor) {
                bindMfrEditor(editor);
            });
        }
        _tinyListenerRegistered = true;
    }

    // Sofort versuchen (falls tinymce schon geladen)
    setupGlobalTinyMCEListener();

    // -------------------------------------------------------------------------
    // Globaler CKE5-Listener (einmalig, kein Leak)
    // -------------------------------------------------------------------------

    let _cke5ListenerRegistered = false;

    function bindMfrCke5Editor(editor, editorId) {
        if (!editor || !editorId) return;

        const textarea = document.getElementById(editorId);
        if (!textarea) return;

        const itemEl = textarea.closest('.mfr-item');
        const nestedItemEl = textarea.closest('.mfr-nested-item');

        if (nestedItemEl) {
            const nestedEl = textarea.closest('.mfr-nested-repeater');
            if (!nestedEl || !nestedEl._mfrNestedInstance) return;
            const instance = nestedEl._mfrNestedInstance;
            editor.model.document.on('change:data', function () {
                const idx = instance._indexOf(nestedItemEl);
                if (idx === -1) return;
                if (!instance.data[idx]) instance.data[idx] = {};
                instance.data[idx][textarea.dataset.mfrField] = editor.getData();
                instance._updateTitle(nestedItemEl, instance.data[idx], idx);
                instance.onChange && instance.onChange();
            });
            return;
        }

        if (itemEl) {
            const containerEl = textarea.closest('.mfr-container');
            if (!containerEl || !containerEl._mfrInstance) return;
            const instance = containerEl._mfrInstance;
            editor.model.document.on('change:data', function () {
                const idx = instance._indexOf(itemEl);
                if (idx === -1) return;
                if (!instance.data[idx]) instance.data[idx] = {};
                instance.data[idx][textarea.dataset.mfrField] = editor.getData();
                instance._setTitle(itemEl, instance.data[idx], idx);
                instance.syncValue();
            });
        }
    }

    function setupGlobalCke5Listener() {
        if (_cke5ListenerRegistered) return;
        if (typeof $ === 'undefined') return;

        $(window).on('rex:cke5IsInit.mfr', function (_evt, editor, editorId) {
            bindMfrCke5Editor(editor, editorId);
        });

        if (typeof cke5_get_editors === 'function') {
            const editors = cke5_get_editors();
            if (editors && typeof editors === 'object') {
                Object.keys(editors).forEach(function (editorId) {
                    bindMfrCke5Editor(editors[editorId], editorId);
                });
            }
        }

        _cke5ListenerRegistered = true;
    }

    setupGlobalCke5Listener();

    // -------------------------------------------------------------------------
    // Globaler Form-Submit-Handler (einmalig für alle Repeater)
    // -------------------------------------------------------------------------

    function syncRepeatersInForm(form) {
        if (!form || !form.querySelector('.mfr-container')) return;
        mfrLog(isGlobalDebugEnabled(), 'syncRepeatersInForm start', {
            formAction: form.getAttribute('action'),
            formMethod: form.getAttribute('method'),
            containerCount: form.querySelectorAll('.mfr-container').length
        });
        saveTinyMCE();
        form.querySelectorAll('.mfr-container').forEach(function (container) {
            if (container._mfrInstance) {
                container._mfrInstance.syncFromDOM();
                mfrLog(isContainerDebugEnabled(container), 'syncRepeatersInForm container synced', {
                    fieldName: container.dataset.mfrFieldName,
                    valueInputName: container.querySelector('.mfr-value') ? container.querySelector('.mfr-value').name : null,
                    hiddenValue: container.querySelector('.mfr-value') ? container.querySelector('.mfr-value').value : null
                });
            }
        });
        mfrLog(isGlobalDebugEnabled(), 'syncRepeatersInForm end');
    }

    document.addEventListener('submit', function (e) {
        const form = e.target;
        mfrLog(isGlobalDebugEnabled(), 'document submit capture', {
            formAction: form && form.getAttribute ? form.getAttribute('action') : null,
            formMethod: form && form.getAttribute ? form.getAttribute('method') : null
        });
        syncRepeatersInForm(form);
    }, true);

    // PJAX serialisiert Formulardaten sehr frueh; daher schon beim Klick auf Submit syncen.
    document.addEventListener('click', function (e) {
        const submitControl = e.target.closest('button[type="submit"], input[type="submit"], button:not([type])');
        if (!submitControl) return;
        const form = submitControl.form || submitControl.closest('form');
        mfrLog(isGlobalDebugEnabled(), 'submit control click capture', {
            controlType: submitControl.tagName,
            formAction: form && form.getAttribute ? form.getAttribute('action') : null,
            formMethod: form && form.getAttribute ? form.getAttribute('method') : null
        });
        syncRepeatersInForm(form);
    }, true);

    window.mfrDebugDump = function () {
        const containers = Array.from(document.querySelectorAll('.mfr-container'));
        const dump = containers.map(function (container) {
            const input = container.querySelector('.mfr-value');
            return {
                id: container.id,
                fieldName: container.dataset.mfrFieldName,
                valueInputName: input ? input.name : null,
                value: input ? input.value : null,
                data: container._mfrInstance ? container._mfrInstance.data : null,
                itemCount: container._mfrInstance ? container._mfrInstance.data.length : null
            };
        });
        console.log(MFR_LOG_PREFIX + ' debug dump', dump);
        return dump;
    };

    // Globale Referenz
    window.MFormFlexRepeater = MFormFlexRepeater;
    window.mfrInit = initRepeaters;

}());
