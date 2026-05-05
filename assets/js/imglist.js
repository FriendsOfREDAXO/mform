const mform_img_list = '.rex-js-widget-imglist';

$(document).on('rex:ready', function (e, container) {
    setTimeout(function () {
        if (container && container.find(mform_img_list).length) {
            container.find(mform_img_list).each(function () {
                imglist_init_widget($(this));
                imglist_list_items_action($(this));
                imglist_widget_actions($(this));
                imglist_update_empty_state($(this));
            });
        }
    }, 2);
});

function imglist_init_widget(element) {
    const selectId = element.find('select').attr('id');
    if (!selectId) return;

    const matches = selectId.match(/\d+/g);
    if (!matches || !matches[0]) return;

    let n = matches[0],
        widget_id = element.attr('data-widget-id');

    if (n !== widget_id) {
        $('#REX_IMGLIST_' + widget_id).attr('id', 'REX_IMGLIST_' + n);
        element.attr('data-widget-id', n);
        widget_id = n;
    }

    const list = element.find('ul.thumbnail-list');
    if (list.data('ui-sortable')) {
        list.sortable('destroy');
    }

    list.sortable({
        opacity: 0.6,
        cursor: 'move',
        placeholder: 'li-placeholder',
        start: function (event, ui) {
            if (ui.item.find('img').length) {
                imglist_hide_tooltip(element, ui.item.find('img'));
            }
        },
        stop: function () {
            imglist_write_input(element);
            imglist_update_empty_state(element);
        }
    });
}

function writeREXMedialist(id) {
    let letitgo = true;
    let element;

    $(mform_img_list).each(function () {
        if ($(this).attr('data-widget-id') == id) {
            letitgo = false;
            element = $(this);
        }
    });

    if (letitgo) {
        writeREX(id, 'REX_MEDIALIST_', 'REX_MEDIALIST_SELECT_');
    } else {
        imglist_add_img_by_last_list_item(element);
    }
    return false;
}

function imglist_widget_actions(element) {
    const widget_id = element.attr('data-widget-id');
    const param = element.attr('data-params');

    element.find('.btn-popup.delete').off('click.mformImglist').on('click.mformImglist', function () {
        const selected = element.find('ul.thumbnail-list > li.selected');
        const next_selected = selected.next();
        const prev_selected = selected.prev();

        if (selected.length) {
            element.find('select option').each(function () {
                if ($(this).attr('data-key') === selected.attr('data-key')) {
                    $(this).remove();
                }
            });

            selected.remove();

            if (next_selected.length) {
                imglist_list_items_select(element, next_selected);
            } else if (prev_selected.length) {
                imglist_list_items_select(element, prev_selected);
            }

            imglist_write_input(element);
            imglist_update_empty_state(element);

            element.find('ul.thumbnail-list').sortable('refresh');
        }
        return false;
    });

    element.find('.btn-popup.open').off('click.mformImglist').on('click.mformImglist', function () {
        openREXMedialist(widget_id, param);
        return false;
    });

    element.find('.btn-popup.add').off('click.mformImglist').on('click.mformImglist', function () {
        addREXMedialist(widget_id, param);
        return false;
    });

    element.find('.btn-popup.view').off('click.mformImglist').on('click.mformImglist', function () {
        viewREXMedialist(widget_id, param);
        return false;
    });
}

// function deleteREXImagelist(widget_id, param) {
//     alert('delete');
// }

function imglist_add_img_by_last_list_item(element) {
    let go_go_go = false;
    const options = element.find('select option');
    const listItems = element.find('ul.thumbnail-list li');
    const startIndex = Math.max(0, listItems.length);

    for (let i = startIndex; i < options.length; i++) {
        if ((listItems.length - 1) < i) {
            const item = options.eq(i);
            item.attr('data-key', i);

            const file = item.val();
            const extension = file.replace(/^.*\./, '').toLowerCase();
            const encodedFile = encodeURIComponent(file);
            let url = 'index.php?rex_media_type=rex_medialistbutton_preview&rex_media_file=';
            const isVideo = ['mp4', 'webm', 'ogg'].includes(extension);
            if (extension === 'svg' || isVideo) {
                url = '/media/';
            }
            const source = isVideo ? (url + file) : (url + encodedFile);
            const media = isVideo
                ? `<video playsinline autoplay muted loop class="thumbnail"><source src="${source}" type="video/${extension}"></video>`
                : `<img class="thumbnail" src="${source}" title="${file}" />`;

            const new_li = $(`<li data-key="${i}" value="${file}" data-value="${file}" tabindex="0" role="button" aria-label="${file}">${media}</li>`);

            imglist_add_tooltip(element, new_li.find('img'));
            element.find('ul.thumbnail-list').append(new_li);
            go_go_go = true;
        }
    }

    if (go_go_go) {
        imglist_write_input(element);
        imglist_update_empty_state(element);
        element.find('ul.thumbnail-list').sortable('refresh');
    }
}

function imglist_list_items_action(element) {
    const list = element.find('ul.thumbnail-list');
    list.off('click.mformImglist keydown.mformImglist');

    list.on('click.mformImglist', 'li', function () {
        let selected = ($(this).data('selected') === 1);
        $(this).parent().find('li').removeClass('selected').data('selected', 0);
        element.find('select option:selected').prop('selected', false).data('selected', 0);

        if (!selected) {
            imglist_list_items_select(element, $(this));
        }
        if ($(this).find('img').length) {
            imglist_hide_tooltip(element, $(this).find('img'));
        }
    });

    list.on('keydown.mformImglist', 'li', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            $(this).trigger('click');
            return;
        }

        if (e.key === 'Delete' || e.key === 'Backspace') {
            e.preventDefault();
            element.find('.btn-popup.delete').trigger('click');
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
            $(this).data('selected', 1);
            $(this).prop('selected', true);
        }
    });
}

function imglist_write_input(element) {
    let source_elements = element.find('ul li'),
        new_value = '';
    if (source_elements.length) {
        for (let i = 0; i < source_elements.length; i++) {
            new_value = new_value + $(source_elements[i]).attr('value');
            if (source_elements.length > (i + 1)) new_value = new_value + ',';
        }
        element.find('input').val(new_value);
    } else {
        element.find('input').val('');
    }
}

function imglist_update_empty_state(element) {
    const hasItems = element.find('ul.thumbnail-list li').length > 0;
    element.toggleClass('is-empty', !hasItems);
}
