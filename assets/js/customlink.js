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
    let id = 'cl' + randId(),
        clang = element.data('clang'),
        media_types = element.data('types'),
        media_Category = element.data('media_category'),
        extern_link_prefix = (element.data('extern-link-prefix') === undefined) ? 'https://' : element.data('extern-link-prefix'),
        link_category = element.data('category'),
        hidden_input = element.find('input[type=hidden]'),
        showed_input = element.find('input[type=text]'),
        value, text, args, timer, repeaterLink = (showed_input.attr('repeater_link') === '1');

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
        dispatchCustomLinkEvent(hidden_input, hidden_input.val(), showed_input.val());
    }

    function promptValue(promptLabel, currentValue, fallbackPrefix) {
        let inputValue = currentValue;
        if (!inputValue || (fallbackPrefix && String(inputValue).indexOf(fallbackPrefix) < 0)) {
            inputValue = fallbackPrefix || '';
        }
        return window.prompt(promptLabel, inputValue);
    }

    if (!element.hasClass('init_custom_link_widget')) {
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

        element.data('id', id)
        element.find('ul.dropdown-menu').attr('id', 'mform_ylink_' + id);
        element.toggleClass('is-empty', !(hidden_input.val() && String(hidden_input.val()).trim() !== ''));
        updateActiveButton(hidden_input.val());

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

            if (media_types !== undefined) {
                args = '&args[types]=' + media_types;
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

            let mailto_link = promptValue('Mail', value, 'mailto:');

            if (mailto_link !== 'mailto:' && mailto_link !== "" && mailto_link !== undefined && mailto_link != null) {
                if (!String(mailto_link).startsWith('mailto:')) {
                    mailto_link = 'mailto:' + mailto_link;
                }
                setLinkValue(mailto_link, mailto_link);
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

            let tel_link = promptValue('Telephone', value, 'tel:');

            if (tel_link !== 'tel:' && tel_link !== "" && tel_link !== undefined && tel_link != null) {
                if (!String(tel_link).startsWith('tel:')) {
                    tel_link = 'tel:' + tel_link;
                }
                setLinkValue(tel_link, tel_link);
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
            '<a href="#" class="btn btn-popup mform-cl-multi-remove" title="Entfernen"><i class="rex-icon rex-icon-delete-link"></i></a>' +
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

    // Sync on form submit
    multiWidget.closest('form').off('submit.clmulti').on('submit.clmulti', function () {
        customLinkMultiSerialize(multiWidget);
    });

    // Sortable via move-up/move-down on drag handle click (simple swap)
    multiWidget.on('mousedown.clmulti', '.mform-cl-multi-handle', function (e) {
        // Only drag-to-reorder is complex; skip for now – dragging not wired without dragula/sortable
        // A future improvement can add sortable library here
    });
}
