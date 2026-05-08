let mform_custom_link = '.rex-js-widget-customlink';

$(document).on('rex:ready', function (e, container) {
    if (container.find(mform_custom_link).length) {
        container.find(mform_custom_link).each(function () {
            customlink_init_widget($(this).find('.input-group.custom-link'));
        });
    }
    // Init multi custom-link widgets
    $(container).find('.rex-js-cl-multi').each(function () {
        customLinkMultiInit($(this));
    });
});

function customlink_init_widget(element) {
    let id = customlinkResolveWidgetId(element),
        clang = element.data('clang'),
        media_types = element.data('types'),
        media_Category = element.data('media_category'),
        extern_link_prefix = (element.data('extern-link-prefix') === undefined) ? 'https://' : element.data('extern-link-prefix'),
        link_category = element.data('category'),
        hidden_input = element.find('input[type=hidden]'),
        showed_input = element.find('input[type=text]'),
        value, text, args, timer, repeaterLink = (showed_input.attr('repeater_link') === '1');

    function normalizeMediaTypes(rawTypes) {
        if (Array.isArray(rawTypes)) {
            return rawTypes.map(function (entry) {
                return String(entry || '').trim().toLowerCase();
            }).filter(Boolean);
        }

        return String(rawTypes || '')
            .split(',')
            .map(function (entry) {
                return String(entry || '').trim().toLowerCase();
            })
            .filter(Boolean);
    }

    function isPreviewableMediaValue(mediaValue) {
        const value = String(mediaValue || '').trim().toLowerCase();
        if (!value || value.indexOf('://') !== -1) {
            return false;
        }

        const cleanValue = value.split('?')[0].split('#')[0];
        const ext = cleanValue.indexOf('.') >= 0 ? cleanValue.split('.').pop() : '';
        return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg', 'bmp', 'tif', 'tiff', 'mp4', 'webm', 'ogg', 'ogv', 'mov', 'm4v'].indexOf(ext) !== -1;
    }

    function syncMediaPreviewButton(mediaValue) {
        const previewBtn = element.find('a.media_preview_link');
        if (!previewBtn.length) {
            return;
        }

        const hasMediaButton = element.find('a.media_link').length && !element.find('a.media_link').hasClass('hidden');
        const canPreview = hasMediaButton && isPreviewableMediaValue(mediaValue);
        previewBtn.toggleClass('hidden', !canPreview);
        previewBtn.toggleClass('disabled', !canPreview);
        previewBtn.attr('aria-disabled', canPreview ? 'false' : 'true');
    }

    function updateActiveButton(value) {
        let v = String(value || '');
        element.find('.btn-popup').removeClass('active');
        if (!v || v.trim() === '') return;

        if (v.startsWith('mailto:')) {
            element.find('a.email_link').addClass('active');
        } else if (v.startsWith('tel:')) {
            element.find('a.phone_link').addClass('active');
        } else if (v.startsWith('#')) {
            element.find('a.anchor_link').addClass('active');
        } else if (v.startsWith('redaxo://')) {
            element.find('a.intern_link').addClass('active');
        } else if (/^\d+$/.test(v)) {
            // plain numeric article ID (stored without redaxo:// prefix)
            element.find('a.intern_link').addClass('active');
        } else {
            // Check ylinks first
            let matched = false;
            element.find('.ylink').each(function () {
                let table = $(this).data('table');
                if (table && v.startsWith(table.split('_').join('-') + '://')) {
                    element.find('.input-group-btn [data-toggle="dropdown"]').addClass('active');
                    matched = true;
                    return false;
                }
            });
            if (!matched) {
                if (/^https?:\/\//.test(v) || /^[a-z][a-z0-9+\-.]*:\/\//.test(v)) {
                    element.find('a.external_link').addClass('active');
                } else {
                    element.find('a.media_link').addClass('active');
                }
            }
        }
    }

    function setLinkValue(linkUrl, linkText) {
        hidden_input.val(linkUrl || '');
        showed_input.val(linkText || '');
        element.toggleClass('is-empty', !(linkUrl && String(linkUrl).trim() !== ''));
        updateActiveButton(linkUrl);
        syncMediaPreviewButton(linkUrl);
        dispatchCustomLinkEvent(hidden_input, hidden_input.val(), showed_input.val());
    }

    function promptValue(promptLabel, currentValue, fallbackPrefix) {
        let inputValue = currentValue;
        if (!inputValue || (fallbackPrefix && String(inputValue).indexOf(fallbackPrefix) < 0)) {
            inputValue = fallbackPrefix || '';
        }
        return window.prompt(promptLabel, inputValue);
    }

    // Re-resolve ID on every init so cloned/reindexed Gridblock elements
    // are bound to their current REX_* ids.
    id = customlinkResolveWidgetId(element);
    element.data('id', id);
    element.find('ul.dropdown-menu').attr('id', 'mform_ylink_' + id);

    // In dynamischen Kontexten (z. B. Flex-Repeater) wird oft nur der Hidden-Wert gesetzt.
    // Wenn das sichtbare Feld leer ist, aber ein Linkwert vorhanden ist, den Namen per AJAX auflösen.
    if ((!showed_input.val() || String(showed_input.val()).trim() === '') && hidden_input.val() && String(hidden_input.val()).trim() !== '') {
        var rawValue = hidden_input.val();
        $.getJSON(
            'index.php',
            { 'rex-api-call': 'mform_resolve_link', value: rawValue },
            function (data) {
                if (data && data.text && showed_input.val() === '') {
                    showed_input.val(data.text);
                }
            }
        ).fail(function () {
            // Fallback: Rohwert anzeigen
            if (showed_input.val() === '') {
                showed_input.val(rawValue);
            }
        });
    }

    element.toggleClass('is-empty', !(hidden_input.val() && String(hidden_input.val()).trim() !== ''));
    updateActiveButton(hidden_input.val());
    syncMediaPreviewButton(hidden_input.val());
    element.addClass('init_custom_link_widget');

    if (repeaterLink) {
        let parent = element.parents('.repeater-group'),
            index = parent.attr('iteration') || randId(),
            groupsAttr = showed_input.attr('groups') || '',
            groups = groupsAttr ? groupsAttr.split('.') : [];
        if (groups.length > 1) {
            let parentParent = parent.parents('.repeater-group'),
                parentIndex = (parentParent) ? parentParent.attr('iteration') : undefined;
            if (parentIndex !== undefined) {
                index = index + '_' + parentIndex;
            }
        }
        id = index;
    }

    element.data('id', id);

    // ylink
    element
        .find('.input-group-btn a.ylink')
        .off('click.mformCustomlink')
        .on('click.mformCustomlink', function () {
                let id = element.data('id'),
                    table = $(this).data('table'),
                    column = $(this).data('column'),
                    pool = newPoolWindow(
                        'index.php?page=yform/manager/data_edit&table_name=' +
                        table +
                        '&rex_yform_manager_opener[id]=' +
                        id +
                        '&rex_yform_manager_opener[field]=' +
                        column +
                        '&rex_yform_manager_opener[multiple]=0'
                    )

                clearInterval(timer)
                closeDropDown(id)

                window.addEventListener('rex:YForm_selectData_' + id, (event) => {
                    event.preventDefault()
                    const id = event.detail.id
                    const label = event.detail.value
                    YForm_selectData(id, label, pool, hidden_input, showed_input, table)
                }, { once: true })

                $(pool).on('rex:YForm_selectData', function (event, id, label) {
                    event.preventDefault()
                    YForm_selectData(id, label, pool, hidden_input, showed_input, table)
                })

            return false
        })

    // media element
    element.find('a.media_link').off('click.mformCustomlink').on('click.mformCustomlink', function () {
            let id = element.data('id'),
                value = hidden_input.val(),
                args = '';

            clearInterval(timer);
            closeDropDown(id);

            const mediaTypesList = normalizeMediaTypes(media_types);
            if (mediaTypesList.length) {
                args = '&args[types]=' + encodeURIComponent(mediaTypesList.join(','));
            }
            if (media_Category !== undefined) {
                args = args + '&rex_file_category=' + media_Category;
            }

            hidden_input.attr('id', 'REX_MEDIA_' + id);

            timer = setInterval(function () {
                if (!$('#REX_MEDIA_' + id).length) {
                    clearInterval(timer);
                } else {
                    if (value != hidden_input.val()) {
                        clearInterval(timer);
                        setLinkValue(hidden_input.val(), hidden_input.val());
                    }
                }
            }, 10);

            let mediaMap = openREXMedia(id, args); // &args[preview]=1&args[types]=jpg%2Cpng
            $(mediaMap).on('rex:selectMedia', (event, mediaName) => {
                setLinkValue(mediaName, mediaName);
            });
        return false;
    });

    // media preview element
    element.find('a.media_preview_link').off('click.mformCustomlink').on('click.mformCustomlink', function () {
            let id = element.data('id'),
                mediaValue = String(hidden_input.val() || '').trim();

            clearInterval(timer);
            closeDropDown(id);

            if (!isPreviewableMediaValue(mediaValue)) {
                syncMediaPreviewButton(mediaValue);
                return false;
            }

            hidden_input.attr('id', 'REX_MEDIA_' + id);
            viewREXMedia(id);
        return false;
    });

    // link element
    element.find('a.intern_link').off('click.mformCustomlink').on('click.mformCustomlink', function () {
            let id = element.data('id'),
                link_id = randInt(),
                args = '&clang=' + clang;

            clearInterval(timer);
            closeDropDown(id);

            if (link_category !== undefined) {
                args = args + '&category_id=' + link_category;
            }

            showed_input.attr('id', 'REX_LINK_' + link_id + '_NAME');
            hidden_input.attr('id', 'REX_LINK_' + link_id);

            let linkMap = openLinkMap('REX_LINK_' + link_id, args);
            $(linkMap).on('rex:selectLink', (event, linkurl, linktext) => {
                setLinkValue(linkurl, linktext);
            });
        return false;
    });

    // extern link
    element.find('a.external_link').off('click.mformCustomlink').on('click.mformCustomlink', function () {
            let id = element.data('id'),
                value = hidden_input.val(),
                text = showed_input.val();

            clearInterval(timer);
            closeDropDown(id);

            let extern_link = promptValue('Link', value, extern_link_prefix);

            hidden_input.attr('id', 'REX_LINK_' + id).addClass('form-control').attr('readonly', true);

            if (extern_link !== 'https://' && extern_link !== "" && extern_link !== undefined && extern_link != null) {
                setLinkValue(extern_link, extern_link);
            }
            if (extern_link == null) {
                setLinkValue(value, text);
            }
        return false;
    });

    // mail to link
    element.find('a.email_link').off('click.mformCustomlink').on('click.mformCustomlink', function () {
            let id = element.data('id'),
                value = hidden_input.val(),
                text = showed_input.val();

            clearInterval(timer);
            closeDropDown(id);

            hidden_input.attr('id', 'REX_LINK_' + id).addClass('form-control').attr('readonly', true);

            let mailto_link = promptValue('E-Mail-Adresse', value, 'mailto:');

            if (mailto_link !== 'mailto:' && mailto_link !== '' && mailto_link !== undefined && mailto_link != null) {
                if (!String(mailto_link).startsWith('mailto:')) {
                    mailto_link = 'mailto:' + mailto_link;
                }
                // E-Mail-Validierung
                let emailPart = String(mailto_link).replace(/^mailto:/i, '').split('?')[0];
                let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailPart)) {
                    window.alert('Bitte eine gültige E-Mail-Adresse eingeben.');
                    setLinkValue(value, text);
                } else {
                    setLinkValue(mailto_link, mailto_link);
                }
            }
            if (mailto_link == null) {
                setLinkValue(value, text);
            }
        return false;
    });

    // phone link
    element.find('a.phone_link').off('click.mformCustomlink').on('click.mformCustomlink', function () {
            let id = element.data('id'),
                value = hidden_input.val(),
                text = showed_input.val();

            clearInterval(timer);
            closeDropDown(id);

            hidden_input.attr('id', 'REX_LINK_' + id).addClass('form-control').attr('readonly', true);

            let tel_link = promptValue('Telefonnummer (z. B. +49 30 123456)', value, 'tel:');

            if (tel_link !== 'tel:' && tel_link !== '' && tel_link !== undefined && tel_link != null) {
                if (!String(tel_link).startsWith('tel:')) {
                    tel_link = 'tel:' + tel_link;
                }
                // Telefon-Validierung: nur Ziffern, +, -, Leerzeichen, Klammern
                let telPart = String(tel_link).replace(/^tel:/i, '');
                let telRegex = /^[\d\s+\-().]{3,}$/;
                if (!telRegex.test(telPart)) {
                    window.alert('Bitte eine gültige Telefonnummer eingeben (Ziffern, +, -, Leerzeichen, Klammern).');
                    setLinkValue(value, text);
                } else {
                    setLinkValue(tel_link, tel_link);
                }
            }
            if (tel_link == null) {
                setLinkValue(value, text);
            }
        return false;
    });

    // delete link
    element.find('a.delete_link').off('click.mformCustomlink').on('click.mformCustomlink', function () {
            let id = element.data('id');
            clearInterval(timer);
            closeDropDown(id);
            setLinkValue('', '');
        return false;
    });

    // anchor link element
    element.find('a.anchor_link').off('click.mformCustomlink').on('click.mformCustomlink', function () {
            let id = element.data('id'),
                value = hidden_input.val(),
                text = showed_input.val();

            clearInterval(timer);
            closeDropDown(id);

            let anchor_link = promptValue('Anker-ID (mit #)', value, '#');

            hidden_input.attr('id', 'REX_LINK_' + id).addClass('form-control').attr('readonly', true);

            if (anchor_link !== '#' && anchor_link !== "" && anchor_link !== undefined && anchor_link != null) {
                if (!anchor_link.startsWith('#')) {
                    anchor_link = '#' + anchor_link;
                }
                setLinkValue(anchor_link, 'Anker: ' + anchor_link);
            }
            if (anchor_link == null) {
                setLinkValue(value, text);
            }
        return false;
    });
}

function customlinkResolveWidgetId(element) {
    const hiddenInput = element.find('input[type=hidden]').first();
    const shownInput = element.find('input[type=text]').first();
    const hiddenId = String(hiddenInput.attr('id') || '');
    const shownId = String(shownInput.attr('id') || '');
    const dataId = String(element.data('id') || '');
    const widgetId = String(element.closest('.rex-js-widget').attr('data-widget-id') || '');

    let id = '';

    if (hiddenId.indexOf('REX_LINK_') === 0) {
        id = hiddenId.replace(/^REX_LINK_/, '').replace(/_NAME$/, '');
    }
    if (!id && shownId.indexOf('REX_LINK_') === 0) {
        id = shownId.replace(/^REX_LINK_/, '').replace(/_NAME$/, '');
    }
    if (!id && dataId) {
        id = dataId;
    }
    if (!id && widgetId) {
        id = widgetId;
    }
    if (!id) {
        id = 'cl' + randId();
    }

    return id;
}

function dispatchCustomLinkEvent(element, linkurl, linktext) {
    let event = jQuery.Event("rex:selectCustomLink");
    jQuery(window).trigger(event, [linkurl, linktext, element]);
}

const YForm_selectData = (
    id,
    label,
    pool,
    hidden_input,
    showed_input,
    table
) => {
    pool.close()

    let linkUrl = table.split('_').join('-') + '://' + id

    hidden_input.val(linkUrl)
    showed_input.val(label)

    const widget = hidden_input.closest('.custom-link');
    if (widget && widget.length) {
        widget.toggleClass('is-empty', false);
    }
    dispatchCustomLinkEvent(hidden_input, linkUrl, label);
}

function randId() {
    return Math.random().toString(16).slice(2);
}

function randInt() {
    return parseInt((Math.random() * 1000000000000) + (Math.random() * 1000000000000 / Math.random()));
}

function closeDropDown(id) {
    let dropdown = $('ul#mform_ylink_' + id);
    if (dropdown.is(':visible')) {
        dropdown.dropdown('toggle');
    }
}

/* -------------------------------------------------------
 * Custom Link Multi Widget
 * ------------------------------------------------------- */

function customLinkMultiSerialize(multiWidget) {
    let values = [];
    multiWidget.find('.mform-cl-multi-item').each(function () {
        let val = $(this).find('.rex-js-widget-customlink input[type=hidden]').val();
        // Accept any value including empty strings, but skip undefined
        if (typeof val !== 'undefined') {
            values.push(val || '');
        }
    });
    multiWidget.find('> input.mform-cl-multi-value').val(JSON.stringify(values));
}

function customLinkMultiBindItem($item, multiWidget) {
    // Re-serialize whenever a custom-link value changes inside this item
    $item.find('.rex-js-widget-customlink input[type=hidden]').on('change.clmulti', function () {
        customLinkMultiSerialize(multiWidget);
    });
    // Remove button
    $item.find('.mform-cl-multi-remove').off('click.clmulti').on('click.clmulti', function (e) {
        e.preventDefault();
        $item.remove();
        customLinkMultiSerialize(multiWidget);
    });
}

function customLinkMultiInit(multiWidget) {
    if (multiWidget.data('clmulti-init')) return;
    multiWidget.data('clmulti-init', true);

    // Hydrate existing JSON values (e.g. in Flex-Repeater rows) when no items are pre-rendered.
    if (multiWidget.find('.mform-cl-multi-item').length === 0) {
        let template = multiWidget.data('template');
        let raw = multiWidget.find('> input.mform-cl-multi-value').val();
        let parsed = [];

        if (typeof raw === 'string' && raw.trim() !== '') {
            try {
                let decoded = JSON.parse(raw);
                if (Array.isArray(decoded)) {
                    parsed = decoded;
                }
            } catch (e) {
                parsed = [];
            }
        }

        if (template && parsed.length > 0) {
            parsed.forEach(function (val) {
                let idx = 'clm' + randId();
                let itemHtml = template.split('CMLIDX').join(idx);
                let $item = $(
                    '<div class="mform-cl-multi-item">' +
                    '<span class="mform-cl-multi-handle" title="Verschieben"><i class="rex-icon fa-bars"></i></span>' +
                    itemHtml +
                    '<a href="#" class="btn btn-popup mform-cl-multi-remove" title="Entfernen"><i class="rex-icon fa-trash"></i></a>' +
                    '</div>'
                );

                // Pre-fill hidden input; leave text input empty so customlink_init_widget's AJAX resolver can set the display name.
                $item.find('.rex-js-widget-customlink input[type=hidden]').first().val(val || '');
                // text input intentionally left empty – will be resolved via AJAX on rex:ready

                multiWidget.find('.mform-cl-multi-list').append($item);
                $(document).trigger('rex:ready', [$item]);
                customLinkMultiBindItem($item, multiWidget);
            });

            customLinkMultiSerialize(multiWidget);
        }
    }

    // Bind existing items
    multiWidget.find('.mform-cl-multi-item').each(function () {
        customLinkMultiBindItem($(this), multiWidget);
    });

    // Add button
    multiWidget.find('.mform-cl-multi-add').off('click.clmulti').on('click.clmulti', function (e) {
        e.preventDefault();
        let template = multiWidget.data('template');
        if (!template) return;

        let idx = 'clm' + randId();
        // Replace all occurrences of the placeholder ID
        let itemHtml = template.split('CMLIDX').join(idx);

        let $item = $(
            '<div class="mform-cl-multi-item">' +
            '<span class="mform-cl-multi-handle" title="Verschieben"><i class="rex-icon fa-bars"></i></span>' +
            itemHtml +
            '<a href="#" class="btn btn-popup mform-cl-multi-remove" title="Entfernen"><i class="rex-icon fa-trash"></i></a>' +
            '</div>'
        );

        multiWidget.find('.mform-cl-multi-list').append($item);

        // Init the newly added custom-link widget
        $(document).trigger('rex:ready', [$item]);
        customLinkMultiBindItem($item, multiWidget);
        customLinkMultiSerialize(multiWidget);
    });

    // Listen to rex:selectCustomLink events to keep JSON in sync
    $(window).on('rex:selectCustomLink.clmulti', function (e, linkurl, linktext, input) {
        let ownerMulti = $(input).closest('.rex-js-cl-multi');
        if (ownerMulti.is(multiWidget)) {
            customLinkMultiSerialize(multiWidget);
        }
    });

    // Sync on form submit once per form and serialize all multi widgets inside.
    let form = multiWidget.closest('form');
    if (form.length && !form.data('clmulti-submit-bound')) {
        form.data('clmulti-submit-bound', true);
        form.on('submit.clmulti', function () {
            $(this).find('.rex-js-cl-multi').each(function () {
                customLinkMultiSerialize($(this));
            });
        });
    }

    // Drag-to-reorder via SortableJS
    if (typeof window.Sortable === 'function') {
        var listEl = multiWidget.find('.mform-cl-multi-list').get(0);
        if (listEl) {
            var existingSortable = multiWidget.data('clmulti-sortable');
            if (existingSortable && typeof existingSortable.destroy === 'function') {
                existingSortable.destroy();
            }
            var sortable = new window.Sortable(listEl, {
                animation: 120,
                handle: '.mform-cl-multi-handle',
                draggable: '.mform-cl-multi-item',
                ghostClass: 'mform-cl-multi-sortable-ghost',
                chosenClass: 'mform-cl-multi-sortable-chosen',
                onEnd: function () {
                    customLinkMultiSerialize(multiWidget);
                }
            });
            multiWidget.data('clmulti-sortable', sortable);
        }
    }
}
