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

function initMFormAccordionToggle(element, reinit) {
    let opened = false;

    element.find('.collapse').each(function () {
        if ($(this).hasClass('in')) {
            opened = true;
        }
    });

    if (!opened && element.attr('data-group-open-collapse') > 0) {
        element.find('.collapse').each(function (index) {
            if ((index+1) == element.attr('data-group-open-collapse')) {
                $(this).addClass('in');
            }
        });
    }
}

function initMFormSelectAccordionToggle(element, init, reinit) {
    let acc = element.parent().parent().parent().find('.panel-group[data-group-select-accordion=true]');

    if (init && acc.length) {
        element.find('option').remove();

        if (!$.isNumeric(element.attr('data-selected')) && acc.attr('data-group-open-collapse') > 0) {
            element.attr('data-selected', (acc.attr('data-group-open-collapse')));
        }

        if (acc.attr('data-group-open-collapse') == 0) {
            element.append('<option value="" selected="selected">' + element.attr('data-group-selected-text') + '</option>');
        }

        acc.find('> .panel > a[data-toggle=collapse]').each(function (index) {
            let togglecollapse = $(this),
                indexId = (index + 1),
                target = togglecollapse.attr('data-target');

            element.append('<option value="' + indexId + '" data-target="' + target + '" data-parent="' + togglecollapse.attr('data-parent') + '">' + togglecollapse.text() + '</option>');
            togglecollapse.attr('data-index', indexId);

            if (reinit) {
                $(target).removeClass('in');
            }

            if ($.isNumeric(element.attr('data-selected')) && element.attr('data-selected') == indexId) {
                element.find('option[value=' + indexId + ']').attr('selected', 'selected');
                $(target).addClass('in').css('height','');
            }
        });
    }

    if (acc.length) {

        let selected = element.find(':selected'),
            target = selected.attr('data-target');

        if (!selected.length) {
            target = $('a[data-index="' + element.attr('data-selected') + '"]').attr('data-target');
        } else {
            element.attr('data-selected', selected.attr('value'));
        }

        if (!$(target).hasClass('in') && !init) {
            $target_elem = $('a[data-target="' + target + '"]');
            $target_elem.trigger('click');
        }
    }
}

function initMFormCollapseToggle(element, init) {
    let target = element.attr('data-target');

    if (!element.attr('data-target')) {
        let form_group = element.parents('.form-group'),
            next_link = form_group.nextAll('a[data-toggle=collapse]');

        if (next_link.attr('data-target')) {
            target = next_link.attr('data-target');
        }
    }

    if (init && target.length) {
        collapseClass(target, 'add');
    }

    if (init) {
        if (element.prop('checked')) {
            collapseClass(target, 'add');
        } else {
            collapseClass(target, 'remove');
        }
    } else {
        if (element.prop('checked')) {
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

        let id = $(this).data('id'),
            clang = $(this).data('clang'),
            media_types = $(this).data('types'),
            media_Category = $(this).data('media_category'),
            link_category = $(this).data('category');

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
