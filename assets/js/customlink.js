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
        extern_link_prefix = (element.data('extern-link-prefix') === undefined) ? 'https://' : element.data('extern-link-prefix'),
        link_category = element.data('category'),
        hidden_input = element.find('input[type=hidden]'),
        showed_input = element.find('input[type=text]'),
        value, text, args, timer;

    // media element
    element.find('a#mform_media_' + id).unbind().bind('click', function () {
        id = element.data('id');
        value = hidden_input.val();
        text = showed_input.val();
        args = '';

        clearInterval(timer);

        if (media_types !== undefined) {
            args = '&args[types]=' + media_types;
        }
        if (media_Category !== undefined) {
            args = args + '&args[category]=' + media_Category;
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

        return false;
    });

    // link element
    element.find('a#mform_link_' + id).unbind().bind('click', function () {
        id = element.attr('data-id');
        value = hidden_input.val();
        text = showed_input.val();
        args = '&clang=' + clang;

        clearInterval(timer);

        if (link_category !== undefined) {
            args = args + '&category_id=' + link_category;
        }

        hidden_input.attr('id', 'REX_LINK_' + id);

        openLinkMap('REX_LINK_' + id, args);

        return false;
    });

    // extern link
    element.find('a#mform_extern_' + id).unbind().bind('click', function () {
        id = element.attr('data-id');
        value = hidden_input.val();
        text = showed_input.val();

        clearInterval(timer);

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
        return false;
    });

    // mail to link
    element.find('a#mform_mailto_' + id).unbind().bind('click', function () {
        id = $(this).parent().parent().attr('data-id');
        value = hidden_input.val();
        text = showed_input.val();

        clearInterval(timer);

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
        return false;
    });

    // phone link
    element.find('a#mform_tel_' + id).unbind().bind('click', function () {
        id = $(this).parent().parent().attr('data-id');
        value = hidden_input.val();
        text = showed_input.val();

        clearInterval(timer);

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
        return false;
    });

    // delete link
    element.find('a#mform_delete_' + id).unbind().bind('click', function () {
        clearInterval(timer);
        showed_input.val('');
        hidden_input.val('');
        return false;
    });
}
