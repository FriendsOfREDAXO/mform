/**
 * Created by joachimdoerr on 19.09.16.
 */
$(function () {
    mform_init();
    $(document).on('pjax:end', function () {
        mform_init();
    });
});

function mform_init() {
    var mform = $('.mform');
    // init by siteload
    if ($('#REX_FORM').length && mform.length) {

        mform.parsley();

        var custom_link = mform.find('.custom-link'),
            multiple_select = mform.find('.multiple-select');

        if (custom_link.length) {
            mform_custom_link(custom_link);
        }
        if (multiple_select.length) {
            mform_multiple_select(multiple_select);
        }
    }
}

function mform_multiple_select(item) {
    item.each(function(){
        $(this).change(function() {
            item.next('input[type=hidden]').val($(this).val());
        });
    });
}

function mform_custom_link(item) {
    item.each(function(){
        var $id = $(this).data('id'),
            $clang = $(this).data('clang'),
            media_button = $(this).find('a#mform_media_' + $id),
            link_button = $(this).find('a#mform_link_' + $id),
            delete_button = $(this).find('a#mform_delete_' + $id),
            extern_button = $(this).find('a#mform_extern_' + $id),
            hidden_input = $(this).find('input[type=hidden]').addClass('form-control').attr('readonly', true),
            showed_input = $(this).find('input[type=text]');

        media_button.unbind().bind('click', function () {
            hidden_show_media(hidden_input, showed_input, $id);
            openREXMedia($id,'');
            return false;
        });
        link_button.unbind().bind('click', function () {
            show_hidden_link(hidden_input, showed_input);
            openLinkMap('REX_LINK_' + $id, '&clang=' + $clang);
            return false;
        });
        extern_button.unbind().bind('click', function () {
            show_hidden_link(hidden_input, showed_input);
            var extern_link = prompt('Link', 'http://');
            if (extern_link != 'http://' && extern_link != "" && extern_link != undefined) {
                showed_input.val(extern_link);
                hidden_input.val(extern_link);
            }
            return false;
        });
        delete_button.unbind().bind('click', function () {
            showed_input.val('');
            hidden_input.val('');
            return false;
        });
    });
}

function hidden_show_media(hidden_input, showed_input, id) {
    if (hidden_input.attr('type') != 'text') {
        hidden_input.data('link_id', hidden_input.attr('id'));
        hidden_input.attr('id','REX_MEDIA_' + id);
        showed_input.val('').attr('type','hidden');
        hidden_input.val('').attr('type','text');
    }
}
function show_hidden_link(hidden_input, showed_input) {
    if (hidden_input.attr('type') == 'text') {
        hidden_input.attr('id', hidden_input.data('link_id'));
        showed_input.val('').attr('type','text');
        hidden_input.val('').attr('type','hidden');
    }
}