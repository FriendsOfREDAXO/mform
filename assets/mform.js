/**
 * Created by joachimdoerr on 19.09.16.
 */
$(document).on('rex:ready', function () {
    mform_init();
});

function mform_init() {
    let mform = $('.mform');

    // init tooltip
    initMFormTooltip(mform);
    // init toggle
    initMFormToggle(mform);

    // init by siteload
    if ($('#REX_FORM').length || mform.length || $('form.rex-yform').length || $($('form div.custom-link').length)) {
        let custom_link = $('div.custom-link');
        if (custom_link.length) {
            mform_custom_link(custom_link);
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
    let opened = false;

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
    let acc = $element.parent().parent().parent().find('.panel-group[data-group-select-accordion=true]');

    if (init && acc.length) {
        $element.find('option').remove();

        if (!$.isNumeric($element.attr('data-selected')) && acc.attr('data-group-open-collapse') > 0) {
            $element.attr('data-selected', (acc.attr('data-group-open-collapse')));
        }

        if (acc.attr('data-group-open-collapse') == 0) {
            $element.append('<option value="" selected="selected">' + $element.attr('data-group-selected-text') + '</option>');
        }

        acc.find('> .panel > a[data-toggle=collapse]').each(function (index) {
            let togglecollapse = $(this),
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

        let selected = $element.find(':selected'),
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
    let target = $element.attr('data-target');

    if (!$element.attr('data-target')) {
        let form_group = $element.parents('.form-group'),
            next_link = form_group.nextAll('a[data-toggle=collapse]');

        if (next_link.attr('data-target')) {
            target = next_link.attr('data-target');
        }
    }

    if (init && target.length) {
        collapseClass(target, 'add');
    }

    if (init) {
        if ($element.prop('checked')) {
            collapseClass(target, 'add');
        } else {
            collapseClass(target, 'remove');
        }
    } else {
        if ($element.prop('checked')) {
            collapseToogle(target, 'show');
        } else {
            collapseToogle(target, 'hide');
        }
    }
}

function collapseToogle(target, type) {
    if (target.length) {
        $(target).each(function(){
            let element = $(this);
            if ($(this).attr('data-target')) {
                element = $(this).next();
            }
            element.collapse(type);
        });
    }
}

function collapseClass(target, type) {
    if (target.length) {
        $(target).each(function(){
            let element = $(this);
            if ($(this).attr('data-target')) {
                element = $(this).next();
            }
            if (type == 'add') {
                element.addClass('in');
            } else {
                element.removeClass('in');
            }
        });
    }
}

function initMFormTooltip(mform) {
    mform.tooltip('destroy');
    mform.find('[data-toggle="tooltip"]').tooltip();
}

function initMFormToggle(mform) {
    mform.find('input[type=checkbox][data-mform-toggle^=toggle]').each(function(){
        let parent = $(this).parent();
        if (parent.hasClass('mform-toggle')) {
            $(this).clone(false).insertBefore(parent);
            parent.remove();
        }
    });

    mform.find('input[type=checkbox][data-mform-toggle^=toggle]').bootstrapMFormToggle('destroy').bootstrapMFormToggle();
}

function mform_custom_link(item) {
    item.each(function () {
        let $id = $(this).data('id'),
            $clang = $(this).data('clang'),
            $mediaTypes = $(this).data('types'),
            $mediaCategory = $(this).data('media_category'),
            $linkCategory = $(this).data('category'),
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
            let args = '';
            if ($mediaTypes !== undefined) {
                args = '&args[types]=' + $mediaTypes;
            }
            if ($mediaCategory !== undefined) {
                args = args + '&args[category]=' + $mediaCategory;
            }
            console.log(args);
            openREXMedia($id, args); // &args[preview]=1&args[types]=jpg%2Cpng
            return false;
        });
        link_button.unbind().bind('click', function () {
            show_hidden_link(hidden_input, showed_input);
            let query = '&clang=' + $clang;
            if ($linkCategory !== undefined) {
                query = query + '&category_id=' + $linkCategory;
            }
            openLinkMap('REX_LINK_' + $id, query);
            return false;
        });
        extern_button.unbind().bind('click', function () {
            show_hidden_link(hidden_input, showed_input);
            let extern_link = prompt('Link', 'http://');
            if (extern_link != 'http://' && extern_link != "" && extern_link != undefined) {
                showed_input.val(extern_link);
                hidden_input.val(extern_link);
            }
            return false;
        });
        mailto_button.unbind().bind('click', function () {
            show_hidden_link(hidden_input, showed_input);
            let mailto_link = prompt('Mail', 'mailto:');
            if (mailto_link != 'mailto:' && mailto_link != "" && mailto_link != undefined) {
                showed_input.val(mailto_link);
                hidden_input.val(mailto_link);
            }
            return false;
        });
        tel_button.unbind().bind('click', function () {
            show_hidden_link(hidden_input, showed_input);
            let tel_link = prompt('Telephone', 'tel:');
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
