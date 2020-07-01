let mform_custom_link = '.rex-js-widget-customlink';

$(document).on('rex:ready', function (e, container) {
    if (container.find(mform_custom_link).length) {
        container.find(mform_custom_link).each(function () {
            customlink_init_widget($(this).find('.input-group.custom-link'));
        });
    }
});

function customlink_init_widget(element) {
    let id = element.data('id'),
        clang = element.data('clang'),
        media_types = element.data('types'),
        media_Category = element.data('media_category'),
        extern_link_prefix = (element.data('extern-link-prefix') === undefined) ? 'https://' :  element.data('extern-link-prefix'),
        link_category = element.data('category'),
        hidden_input = element.find('input[type=hidden]'),
        showed_input = element.find('input[type=text]'),
        value, text, args;

    // media element
    element.find('a#mform_media_' + id).unbind().bind('click', function () {
        id = element.data('id');
        args = '';
        if (media_types !== undefined) {
            args = '&args[types]=' + media_types;
        }
        if (media_Category !== undefined) {
            args = args + '&args[category]=' + media_Category;
        }
        hidden_show_media(element, id);
        openREXMedia(id, args); // &args[preview]=1&args[types]=jpg%2Cpng
        return false;
    });

    // link element
    element.find('a#mform_link_' + id).unbind().bind('click', function () {
        id = element.attr('data-id');
        args = '&clang=' + clang;
        if (link_category !== undefined) {
            args = args + '&category_id=' + link_category;
        }
        show_hidden_link(element, id);
        openLinkMap('REX_LINK_' + id, args);
        return false;
    });

    // extern link
    element.find('a#mform_extern_' + id).unbind().bind('click', function () {
        id = element.attr('data-id');
        value = hidden_input.val();
        text = showed_input.val();

        if (value == '' || value.indexOf(extern_link_prefix) < 0) {
            value = extern_link_prefix;
        }

        show_hidden_link(element, id);
        let extern_link = prompt('Link', value);

        hidden_input.addClass('form-control').attr('readonly', true);

        if (extern_link !== 'https://' && extern_link !== "" && extern_link !== undefined && extern_link != null) {
            hidden_input.val(extern_link);
            showed_input.val(extern_link);
        }
        if (extern_link == null) {
            hidden_input.val(value);
            showed_input.val(text);
        }
        return false;
    });

    // mail to link
    element.find('a#mform_mailto_' + id).unbind().bind('click', function () {
        id = $(this).parent().parent().attr('data-id');
        value = hidden_input.val();
        text = showed_input.val();

        if (value == '' || value.indexOf("mailto:") < 0) {
            value = 'mailto:';
        }

        show_hidden_link(element, id);
        let mailto_link = prompt('Mail', value);

        hidden_input.addClass('form-control').attr('readonly', true);

        if (mailto_link !== 'mailto:' && mailto_link !== "" && mailto_link !== undefined && mailto_link != null) {
            showed_input.val(mailto_link);
            hidden_input.val(mailto_link);
        }
        if (mailto_link == null) {
            hidden_input.val(value);
            showed_input.val(text);
        }
        return false;
    });

    // phone link
    element.find('a#mform_tel_' + id).unbind().bind('click', function () {
        id = $(this).parent().parent().attr('data-id');
        value = hidden_input.val();
        text = showed_input.val();

        if (value == '' || value.indexOf("tel:") < 0) {
            value = 'tel:';
        }

        show_hidden_link(element, id);
        let tel_link = prompt('Telephone', value);

        hidden_input.addClass('form-control').attr('readonly', true);

        if (tel_link !== 'tel:' && tel_link !== "" && tel_link !== undefined && tel_link != null) {
            showed_input.val(tel_link);
            hidden_input.val(tel_link);
        }
        if (tel_link == null) {
            hidden_input.val(value);
            showed_input.val(text);
        }
        return false;
    });

    // delete link
    element.find('a#mform_delete_' + id).unbind().bind('click', function () {
        showed_input.val('');
        hidden_input.val('');
        return false;
    });
}

function hidden_show_media(element, id) {
    element.find('#REX_LINK_' + id + '_NAME').val('').attr('type', 'hidden');
    element.find('#REX_LINK_' + id).attr('type', 'text').addClass('form-control');
    element.find('#REX_LINK_' + id).attr('id', 'REX_MEDIA_' + id);
}

function show_hidden_link(element, id) {
    element.find('#REX_MEDIA_' + id).attr('id', 'REX_LINK_' + id).val('');
    element.find('#REX_LINK_' + id + '_NAME').val('').attr('type', 'text');
    element.find('#REX_LINK_' + id).attr('type', 'hidden');
}
