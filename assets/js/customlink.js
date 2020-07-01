let mform_custom_link = '.rex-js-widget-customlink';

$(document).on('rex:ready', function (e, container) {
    if (container.find(mform_custom_link).length) {
        container.find(mform_custom_link).each(function () {
            customlink_init_widget($(this));
        });
    }
});

function customlink_init_widget(element) {
    let id = $(this).data('id'),
        clang = $(this).data('clang'),
        media_types = $(this).data('types'),
        media_Category = $(this).data('media_category'),
        link_category = $(this).data('category'),
        input_hidden = $(this).find('input[type=hidden]'),
        input_text = $(this).find('input[type=text]');

    $(this).find('a#mform_media_' + id).unbind().bind('click', function () {
        id = $(this).parent().parent().attr('data-id');
        let args = '';
        if (media_types !== undefined) {
            args = '&args[types]=' + media_types;
        }
        if (media_Category !== undefined) {
            args = args + '&args[category]=' + media_Category;
        }
        hidden_show_media($(this).parent().parent(), id);
        openREXMedia(id, args); // &args[preview]=1&args[types]=jpg%2Cpng
        return false;
    });
    $(this).find('a#mform_link_' + id).unbind().bind('click', function () {
        id = $(this).parent().parent().attr('data-id');
        let query = '&clang=' + clang;
        if (link_category !== undefined) {
            query = query + '&category_id=' + link_category;
        }
        show_hidden_link($(this).parent().parent(), id);
        openLinkMap('REX_LINK_' + id, query);
        return false;
    });
    $(this).find('a#mform_extern_' + id).unbind().bind('click', function () {
        id = $(this).parent().parent().attr('data-id');
        console.log(id);
        show_hidden_link($(this).parent().parent(), id);
        let extern_link = prompt('Link', 'https://'),
            hidden_input = $(this).parent().parent().find('input[type=hidden]').addClass('form-control').attr('readonly', true),
            showed_input = $(this).parent().parent().find('input[type=text]');
        console.log(extern_link);
        if (extern_link !== 'https://' && extern_link !== "" && extern_link !== undefined) {
            console.log('go');
            showed_input.val(extern_link);
            hidden_input.val(extern_link);
        }
        return false;
    });
    $(this).find('a#mform_mailto_' + id).unbind().bind('click', function () {
        id = $(this).parent().parent().attr('data-id');
        show_hidden_link($(this).parent().parent(), id);
        let mailto_link = prompt('Mail', 'mailto:'),
            hidden_input = $(this).parent().parent().find('input[type=hidden]').addClass('form-control').attr('readonly', true),
            showed_input = $(this).parent().parent().find('input[type=text]');
        if (mailto_link !== 'mailto:' && mailto_link !== "" && mailto_link !== undefined) {
            showed_input.val(mailto_link);
            hidden_input.val(mailto_link);
        }
        return false;
    });
    $(this).find('a#mform_tel_' + id).unbind().bind('click', function () {
        id = $(this).parent().parent().attr('data-id');
        show_hidden_link($(this).parent().parent(), id);
        let tel_link = prompt('Telephone', 'tel:'),
            hidden_input = $(this).parent().parent().find('input[type=hidden]').addClass('form-control').attr('readonly', true),
            showed_input = $(this).parent().parent().find('input[type=text]');
        if (tel_link !== 'tel:' && tel_link !== "" && tel_link !== undefined) {
            showed_input.val(tel_link);
            hidden_input.val(tel_link);
        }
        return false;
    });
    $(this).find('a#mform_delete_' + id).unbind().bind('click', function () {
        $(this).parent().parent().find('input').val('');
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
    console.log(element.find('#REX_MEDIA_' + id).length);
}
