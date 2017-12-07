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
    // init toggle
    initMFormToggle();
    // init by siteload
    if ($('#REX_FORM').length && mform.length) {
        var custom_link = mform.find('.custom-link'),
            tabs = mform.find('a[data-toggle="tab"]');

        if (custom_link.length) {
            mform_custom_link(custom_link);
        }
        if (tabs.length) {
            mform_tabs();
        }
    }
    // init mform collapse
    initMFormCollapseData();
}

function initMFormCollapseData() {
    $('input[data-toggle=collapse]').each(function(){
        // initial
        mFormCollapseToggle($(this));
        // on change
        $(this).change(function(){
            mFormCollapseToggle($(this));
        });
    });
}

function mFormCollapseToggle($element) {
    var collapse_class = $element.attr('data-target');
    collapse_class = collapse_class.replace('#','.');

    if($element.prop('checked')) {
        $(collapse_class).collapse('show');
    } else {
        $(collapse_class).collapse('hide')
    }
}

function initMFormToggle() {
    $('input[type=checkbox][data-mform-toggle^=toggle]').bootstrapMFormToggle('destroy').bootstrapMFormToggle();
}

function mform_tabs() {
    $('.mform-tabs a[data-toggle="tab"]').on("shown.bs.tab", function (e) {
        var id = $(e.target).attr("href");
        localStorage.setItem('selectedTab', id)
    });

    var selectedTab = localStorage.getItem('selectedTab');
    if (selectedTab != null) {
        $('.mform-tabs a[data-toggle="tab"][href="' + selectedTab + '"]').tab('show');
    }
}

function mform_custom_link(item) {
    item.each(function(){
        var $id = $(this).data('id'),
            $clang = $(this).data('clang'),
            media_button = $(this).find('a#mform_media_' + $id),
            link_button = $(this).find('a#mform_link_' + $id),
            delete_button = $(this).find('a#mform_delete_' + $id),
            extern_button = $(this).find('a#mform_extern_' + $id),
            mailto_button = $(this).find('a#mform_mailto_' + $id),
            tel_button = $(this).find('a#mform_tel_' + $id),
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
        mailto_button.unbind().bind('click', function () {
            show_hidden_link(hidden_input, showed_input);
            var mailto_link = prompt('Mail', 'mailto:');
            if (mailto_link != 'mailto:' && mailto_link != "" && mailto_link != undefined) {
                showed_input.val(mailto_link);
                hidden_input.val(mailto_link);
            }
            return false;
        });
        tel_button.unbind().bind('click', function () {
            show_hidden_link(hidden_input, showed_input);
            var tel_link = prompt('Telephone', 'tel:');
            if (tel_link != 'tel:' && tel_link != "" && tel_link != undefined) {
                showed_input.val(tel_link);
                hidden_input.val(tel_link);
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