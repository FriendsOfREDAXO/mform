let mform_custom_link = '.rex-js-widget-customlink';

$(document).on('rex:ready', function (e, container) {
    if (container.find(mform_custom_link).length) {
        container.find(mform_custom_link).each(function () {
            customlink_init_widget($(this).find('.input-group.custom-link'));
        });
    }
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
        value, text, args, timer, repeaterLink = (showed_input.attr('repeater_link') === 1);

    if (!element.hasClass('init_custom_link_widget')) {
        element.addClass('init_custom_link_widget');

        if (repeaterLink) {
            let parent = element.parents('.repeater-group'),
                index = parent.attr('iteration'),
                groups = input.attr('groups').split('.');
            if (groups.length > 1) {
                let parentParent = parent.parents('.repeater-group'),
                    parentIndex = (parentParent) ? parentParent.attr('iteration') : undefined;
                if (parentIndex !== undefined) {
                    index = index + 0 + parentIndex;
                }
            }
            id = index;
        }

        element.data('id', id)
        element.find('ul.dropdown-menu').attr('id', 'mform_ylink_' + id);

        // ylink
        element
            .find('.input-group-btn a.ylink')
            .unbind()
            .bind('click', function () {
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
                })

                $(pool).on('rex:YForm_selectData', function (event, id, label) {
                    event.preventDefault()
                    YForm_selectData(id, label, pool, hidden_input, showed_input, table)
                })

                return false
            })

        // media element
        element.find('a.media_link').unbind().bind('click', function () {
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

            openREXMedia(id, args); // &args[preview]=1&args[types]=jpg%2Cpng

            timer = setInterval(function () {
                if (!$('#REX_MEDIA_' + id).length) {
                    clearInterval(timer);
                } else {
                    if (value != hidden_input.val()) {
                        clearInterval(timer);
                        showed_input.val(hidden_input.val());
                    }
                }
            }, 10);

            let mediaMap = openREXMedia(id, args); // &args[preview]=1&args[types]=jpg%2Cpng
            $(mediaMap).on('rex:selectMedia', (event, mediaName) => {
                hidden_input.val(mediaName)
                showed_input.val(mediaName);
                dispatchCustomLinkEvent(hidden_input, mediaName, mediaName);
            });
            return false;
        });

        // link element
        element.find('a.intern_link').unbind().bind('click', function () {
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
                dispatchCustomLinkEvent(hidden_input, linkurl, linktext);
            });
            return false;
        });

        // extern link
        element.find('a.external_link').unbind().bind('click', function () {
            let id = element.data('id'),
                value = hidden_input.val(),
                text = showed_input.val();

            clearInterval(timer);
            closeDropDown(id);

            if (value == '' || value.indexOf(extern_link_prefix) < 0) {
                value = extern_link_prefix;
            }

            let extern_link = prompt('Link', value);

            hidden_input.attr('id', 'REX_LINK_' + id).addClass('form-control').attr('readonly', true);

            if (extern_link !== 'https://' && extern_link !== "" && extern_link !== undefined && extern_link != null) {
                hidden_input.val(extern_link);
                showed_input.val(extern_link);
            }
            if (extern_link == null) {
                hidden_input.val(value);
                showed_input.val(text);
            }
            dispatchCustomLinkEvent(hidden_input, hidden_input.val(), showed_input.val());
            return false;
        });

        // mail to link
        element.find('a.email_link').unbind().bind('click', function () {
            let id = element.data('id'),
                value = hidden_input.val(),
                text = showed_input.val();

            clearInterval(timer);
            closeDropDown(id);

            if (value == '' || value.indexOf("mailto:") < 0) {
                value = 'mailto:';
            }

            hidden_input.attr('id', 'REX_LINK_' + id).addClass('form-control').attr('readonly', true);

            let mailto_link = prompt('Mail', value);

            if (mailto_link !== 'mailto:' && mailto_link !== "" && mailto_link !== undefined && mailto_link != null) {
                showed_input.val(mailto_link);
                hidden_input.val(mailto_link);
            }
            if (mailto_link == null) {
                hidden_input.val(value);
                showed_input.val(text);
            }
            dispatchCustomLinkEvent(hidden_input, hidden_input.val(), showed_input.val());
            return false;
        });

        // phone link
        element.find('a.phone_link').unbind().bind('click', function () {
            let id = element.data('id'),
                value = hidden_input.val(),
                text = showed_input.val();

            clearInterval(timer);
            closeDropDown(id);

            if (value == '' || value.indexOf("tel:") < 0) {
                value = 'tel:';
            }

            hidden_input.attr('id', 'REX_LINK_' + id).addClass('form-control').attr('readonly', true);

            let tel_link = prompt('Telephone', value);

            if (tel_link !== 'tel:' && tel_link !== "" && tel_link !== undefined && tel_link != null) {
                showed_input.val(tel_link);
                hidden_input.val(tel_link);
            }
            if (tel_link == null) {
                hidden_input.val(value);
                showed_input.val(text);
            }
            dispatchCustomLinkEvent(hidden_input, hidden_input.val(), showed_input.val());
            return false;
        });

        // delete link
        element.find('a.delete_link').unbind().bind('click', function () {
            let id = element.data('id');
            clearInterval(timer);
            closeDropDown(id);
            showed_input.val('');
            hidden_input.val('');
            dispatchCustomLinkEvent(hidden_input, '', '');
            return false;
        });

        // anchor link element
        element.find('a.anchor_link').unbind().bind('click', function () {
            let id = element.data('id'),
                value = hidden_input.val(),
                text = showed_input.val();

            clearInterval(timer);
            closeDropDown(id);

            // Extract current anchor ID if present
            if (value == '' || value.indexOf('#') < 0) {
                value = '#';
            }

            let anchor_link = prompt('Anker-ID (mit #)', value);

            hidden_input.attr('id', 'REX_LINK_' + id).addClass('form-control').attr('readonly', true);

            if (anchor_link !== '#' && anchor_link !== "" && anchor_link !== undefined && anchor_link != null) {
                // Ensure anchor starts with #
                if (!anchor_link.startsWith('#')) {
                    anchor_link = '#' + anchor_link;
                }
                hidden_input.val(anchor_link);
                showed_input.val('Anker: ' + anchor_link);
            }
            if (anchor_link == null) {
                hidden_input.val(value);
                showed_input.val(text);
            }
            dispatchCustomLinkEvent(hidden_input, hidden_input.val(), showed_input.val());
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

    value = hidden_input.val()
    text = showed_input.val()

    let linkUrl = table.split('_').join('-') + '://' + id

    hidden_input.val(linkUrl)
    showed_input.val(label)

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