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

    mform.find('select[data-toggle=collapse]').each(function () {
        let element = $(this);
        // initial
        initMFormSelectCollapseToggle($(this), true);
        // on change
        $(this).unbind().bind("change", function () {
            initMFormSelectCollapseToggle($(this), false);
        });
    });

    mform.find('.panel-group[data-group-accordion=1]').each(function () {
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

function initMFormSelectCollapseToggle(element, init) {
    let toggleId = element.children("option:selected").data('toggle-item');
    element.children("option:selected").parents('.form-group').next().find('.collapse').each(function(){
        if ($(this).data('group-select-collapse-id') == toggleId) {
            $(this).collapse('show');
        } else {
            $(this).collapse('hide');
        }
    });
}

function initMFormCollapseToggle(element, init) {
    let target = element.attr('data-target');

    if (!element.attr('data-target')) {
        let form_group = element.parents('.form-group'),
            next_link = form_group.nextAll('a[data-toggle=collapse]');

        if (!next_link.length) {
            let next = form_group.next();
            if (next.is('div') && next.hasClass('mform')) {
                next_link = next.find('> a[data-toggle=collapse]');
            }
        }

        if (next_link.attr('data-target')) {
            target = next_link.attr('data-target');
        }
    }

    if (init && target !== undefined && target.length) {
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
    if (target !== undefined && target.length) {
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
    if (target !== undefined && target.length) {
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
