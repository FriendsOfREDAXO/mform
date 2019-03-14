let mform_img_list = '.rex-js-widget-imglist';

$(document).on('rex:ready', function (e, container) {
    setTimeout(function () {
        if (container.find(mform_img_list).length) {
            container.find(mform_img_list).each(function () {
                imglist_init_widget($(this));
                imglist_list_items_action($(this));
                imglist_widget_actions($(this));
            });
        }
    }, 2);
});

function imglist_init_widget(element) {
    let n = element.find('select').attr('id').match(/\d+/g),
        widget_id = element.attr('data-widget-id');

    if (n != widget_id) {
        $('#REX_IMGLIST_' + widget_id).attr('id', 'REX_IMGLIST_' + n);
        element.attr('data-widget-id', n);
        widget_id = n;
    }

    element.find('ul.thumbnail-list').sortable({
        opacity: 0.6,
        cursor: 'move',
        placeholder: "li-placeholder",
        start: function (event, ui) {
            if (ui.item.find('img').length) {
                imglist_hide_tooltip(element, ui.item.find('img'));
            }
        },
        stop: function () {
            // refresh input
            imglist_write_input(widget_id, 'REX_MEDIALIST_', 'REX_IMGLIST_');
        }
    });
}

function writeREXMedialist(id) {
    let letitgo = true,
        element;

    $(mform_img_list).each(function () {
        // if the the widget id a imglist?
        if ($(this).attr('data-widget-id') == id) {
            // yes don't let it go ;)
            letitgo = false;
            // and selt element for the next steps
            element = $(this);
        }
    });

    if (letitgo) {
        // default widget action for input write
        writeREX(id, 'REX_MEDIALIST_', 'REX_MEDIALIST_SELECT_');
    } else {
        // add li by write event from list
        imglist_add_img_by_last_list_item(element);
    }
    return false;
}

function imglist_widget_actions(element) {
    let widget_id = element.attr('data-widget-id'),
        param = element.attr('data-params');

    // REMOVE BUTTON
    element.find('.btn-popup.delete').on('click', function () {
        let selected = element.find('ul.thumbnail-list > li.selected'),
            next_selected = selected.next(),
            prev_selected = selected.prev();

        if (selected.length) {
            // remove element
            selected.remove();
            element.find('select option:selected').remove();

            // set new selected item
            if (next_selected.length) {
                imglist_list_items_select(element, next_selected);
            } else if (prev_selected.length) {
                imglist_list_items_select(element, prev_selected);
            }

            // refresh input
            imglist_write_input(widget_id, 'REX_MEDIALIST_', 'REX_IMGLIST_');

            // refresh sortable
            element.find('ul.thumbnail-list').sortable('refresh');
        }
        return false;
    });

    // OPEN BUTTON
    element.find('.btn-popup.open').on('click', function () {
        openREXMedialist(widget_id, param);
        return false;
    });

    // ADD BUTTON
    element.find('.btn-popup.add').on('click', function () {
        addREXMedialist(widget_id, param);
        return false;
    });

    // VIEW BUTTON
    element.find('.btn-popup.view').on('click', function () {
        viewREXMedialist(widget_id, param);
        return false;
    });
    // element.find('.btn-popup.open').attr('onclick', 'openREXMedialist(' + parseInt(widget_id) + ',\'' + param + '\');return false');
    // element.find('.btn-popup.add').attr('onclick', 'addREXMedialist(' + parseInt(widget_id) + ',\'' + param + '\');return false');
    // element.find('.btn-popup.view').attr('onclick', 'viewREXMedialist(' + parseInt(widget_id) + ',\'' + param + '\');return false');
    // element.find('.btn-popup.delete').attr('onclick', 'deleteREXImagelist(' + parseInt(widget_id) + ',\'' + param + '\');return false');
}

// function deleteREXImagelist(widget_id, param) {
//     alert('delete');
// }

function imglist_add_img_by_last_list_item(element) {
    let widget_id = element.attr('data-widget-id'),
        go_go_go = false;

    for (let i = 0; i < element.find('select option').length; i++) {
        if ((element.find('ul.thumbnail-list li').length - 1) < i) {
            // add new element
            let item = element.find('select option').eq(i);
            item.attr('data-key', i);

            let new_li = $('<li data-key="' + i + '" value="' + item.val() + '" data-value="' + item.val() + '"><img class="thumbnail" src="index.php?rex_media_type=rex_medialistbutton_preview&rex_media_file=' + item.val() + '" title="' + item.val() + '" /></li>');

            imglist_add_tooltip(element, new_li.find('img'));

            // add li img element
            element.find('ul.thumbnail-list').append(new_li);
            go_go_go = true; // go forward
        }
    }
    if (go_go_go) {
        // refresh input
        imglist_write_input(widget_id, 'REX_MEDIALIST_', 'REX_IMGLIST_');

        // refresh sortable
        element.find('ul.thumbnail-list').sortable('refresh');
    }
}

function imglist_list_items_action(element) {
    element.find('ul.thumbnail-list').on('click', 'li', function () {
        let selected = ($(this).attr('data-selected') == 1);
        $(this).parent().find('li').removeClass('selected').attr('data-selected', 0);
        element.find('select option:selected').prop('selected', false);

        if (!selected) {
            imglist_list_items_select(element, $(this));
        }
        if ($(this).find('img').length) {
            imglist_hide_tooltip(element, $(this).find('img'));
        }
    });

    if (element.find('ul.thumbnail-list li img').length) {
        imglist_add_tooltip(element, element.find('ul.thumbnail-list li img'));
    }
}

function imglist_add_tooltip(element, item) {
    if (element.hasClass('rex-js-widget-tooltip')) {
        item.tooltip({
            container: 'body',
            template: '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner rex-img-list-tooltip"></div></div>'
        });
    }
}

function imglist_hide_tooltip(element, item) {
    if (element.hasClass('rex-js-widget-tooltip')) {
        item.tooltip('hide');
    }
}

function imglist_list_items_select(element, item) {
    item.addClass('selected').attr('data-selected', 1);
    element.find('select option').each(function () {
        if ($(this).attr('data-key') === item.attr('data-key')) {
            $(this).prop('selected', true);
        }
    });
}

function imglist_write_input(id, i_list, i_select) {
    let source_elements = $('#' + i_select + id + ' li'),
        new_value = '';

    for (let i = 0; i < source_elements.length; i++) {
        new_value = new_value + $(source_elements[i]).attr('value');
        if (source_elements.length > (i + 1)) new_value = new_value + ',';
    }

    $('#' + i_list + id).val(new_value);
}
