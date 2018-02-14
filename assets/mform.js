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

    // init tooltip
    initMFormTooltip(mform);
    // init toggle
    initMFormToggle(mform);

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
    initMFormCollapseData(mform);

    if (typeof mblock_module !== 'undefined') {
        mblock_module.registerCallback('reindex_end', function () {
            initMForm4Mblock(mform);
        });
    }
}

function initMForm4Mblock(mform) {
    // init tooltip
    initMFormTooltip(mform);
    // init toggle
    initMFormToggle(mform);
    // init collapse
    initMFormCollapseData(mform, true);
}

function initMFormCollapseData(mform, reinit) {
    mform.find('input[data-toggle=collapse]').each(function () {
        // initial
        initMFormCollapseToggle($(this), true);
        // on change
        $(this).unbind().bind("change", function () {
            initMFormCollapseToggle($(this), false);
        });
    });

    mform.find('select[data-toggle=accordion]').each(function () {
        initMFormSelectAccordionToggle($(this), true, reinit);
        // on change
        $(this).unbind().bind("change", function () {
            initMFormSelectAccordionToggle($(this), false, false);
        });
    });

    mform.find('.panel-group[data-group-select-accordion=false]').each(function () {
        initMFormAccordionToggle($(this), reinit);
    });
}

function initMFormAccordionToggle($element, reinit) {
    var opened = false;

    $element.find('.collapse').each(function () {
        if ($(this).hasClass('in')) {
            opened = true;
        }
    });

    if (!opened && $element.attr('data-group-open-collapse') > 0) {
        $element.find('.collapse').each(function (index) {
            if ((index+1) == $element.attr('data-group-open-collapse')) {
                $(this).addClass('in');
            }
        });
    }
}

function initMFormSelectAccordionToggle($element, init, reinit) {
    var acc = $element.parent().parent().parent().find('.panel-group[data-group-select-accordion=true]');

    if (init && acc.length) {
        $element.find('option').remove();

        if (!$.isNumeric($element.attr('data-selected')) && acc.attr('data-group-open-collapse') > 0) {
            $element.attr('data-selected', (acc.attr('data-group-open-collapse')));
        }

        if (acc.attr('data-group-open-collapse') == 0) {
            $element.append('<option value="" selected="selected">' + $element.attr('data-group-selected-text') + '</option>');
        }

        acc.find('a[data-toggle=collapse]').each(function (index) {
            var togglecollapse = $(this),
                indexId = (index + 1),
                target = togglecollapse.attr('data-target');

            $element.append('<option value="' + indexId + '" data-target="' + target + '" data-parent="' + togglecollapse.attr('data-parent') + '">' + togglecollapse.text() + '</option>');
            togglecollapse.attr('data-index', indexId);

            if (reinit) {
                $(target).removeClass('in');
            }

            if ($.isNumeric($element.attr('data-selected')) && $element.attr('data-selected') == indexId) {
                $element.find('option[value=' + indexId + ']').attr('selected', 'selected');
                $(target).addClass('in').css('height','');
            }
        });
    }

    if (acc.length) {

        var selected = $element.find(':selected'),
            target = selected.attr('data-target');

        if (!selected.length) {
            target = $('a[data-index="' + $element.attr('data-selected') + '"]').attr('data-target');
        } else {
            $element.attr('data-selected', selected.attr('value'));
        }

        if (!$(target).hasClass('in') && !init) {
            $target_elem = $('a[data-target="' + target + '"]');
            $target_elem.trigger('click');
        }
    }
}

function initMFormCollapseToggle($element, init) {
    var target = $element.attr('data-target');

    if (!$element.attr('data-target')) {
        var form_group = $element.parents('.form-group'),
            next_link = form_group.nextAll('a[data-toggle=collapse]');

        if (next_link.attr('data-target')) {
            target = next_link.attr('data-target');
        }
    }

    if (init && target.length) {
        $(target).addClass('in');
    }

    if (init) {
        if ($element.prop('checked')) {
            $(target).addClass('in');
        } else {
            $(target).removeClass('in');
        }
    } else {
        if ($element.prop('checked')) {
            $(target).collapse('show');
        } else {
            $(target).collapse('hide')
        }
    }
}

function initMFormTooltip(mform) {
    mform.tooltip('destroy');
    mform.find('[data-toggle="tooltip"]').tooltip();
}

function initMFormToggle(mform) {
    mform.find('input[type=checkbox][data-mform-toggle^=toggle]').each(function(){
        var parent = $(this).parent();
        if (parent.hasClass('mform-toggle')) {
            $(this).clone(false).insertBefore(parent);
            parent.remove();
        }
    });

    mform.find('input[type=checkbox][data-mform-toggle^=toggle]').bootstrapMFormToggle('destroy').bootstrapMFormToggle();
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
    item.each(function () {
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
            openREXMedia($id, '');
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
        hidden_input.attr('id', 'REX_MEDIA_' + id);
        showed_input.val('').attr('type', 'hidden');
        hidden_input.val('').attr('type', 'text');
    }
}

function show_hidden_link(hidden_input, showed_input) {
    if (hidden_input.attr('type') == 'text') {
        hidden_input.attr('id', hidden_input.data('link_id'));
        showed_input.val('').attr('type', 'text');
        hidden_input.val('').attr('type', 'hidden');
    }
}