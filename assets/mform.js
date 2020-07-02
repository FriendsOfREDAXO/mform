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
    let acc = element.parents().find('.panel-group[data-group-select-accordion=true]'),
        parent_group = element.parents('.form-group');

    if (parent_group.next().hasClass('mform')) {
        acc = parent_group.next().find('.panel-group[data-group-accordion]')
    }

    if (init && acc.length) {
        element.find('option').remove();

        if (!$.isNumeric(element.attr('data-selected')) && acc.attr('data-group-open-collapse') > 0) {
            element.attr('data-selected', (acc.attr('data-group-open-collapse')));
        }

        if (acc.attr('data-group-open-collapse') == 0) {
            element.append('<option value="" data-chose-accordion-msg="1">' + element.attr('data-group-selected-text') + '</option>');
        }

        if (element.attr('data-hide-toggle-links') == 1) {
            acc.find('a[data-toggle=collapse]').hide();
        }

        acc.find('> .panel > a[data-toggle=collapse]').each(function (index) {
            let togglecollapse = $(this),
                indexId = (index + 1),
                target = indexId,
                selected;

            if ($(this).attr('data-select-collapse-id') !== undefined) {
                indexId = $(this).attr('data-select-collapse-id')
                target = indexId;
            }

            if (element.attr('data-selected') === indexId) {
                selected = ' selected="selected"';
            }

            element.append('<option value="' + indexId + '" data-target="' + target + '" data-parent="' + togglecollapse.attr('data-parent') + '"'+ selected + '>' + togglecollapse.text() + '</option>');
            togglecollapse.attr('data-index', indexId);

            if (reinit) {
                $(target).removeClass('in').attr('aria-expanded', false);
            }

            if (element.attr('data-selected') === indexId) {
                togglecollapse.next().addClass('in').css('height','').attr('aria-expanded', true);
            }
        });
    }

    if (acc.length) {
        let selected = element.find(':selected'),
            targetId = (!selected.length) ? element.attr('data-selected') : selected.attr('data-target'),
            targetLink = $('a[data-index="' + targetId + '"]'),
            target = targetLink.next();

        // console.log([selected,targetId,targetLink,target]);

        if (selected.length) {
            element.attr('data-selected', selected.attr('value'));
        }

        if (!target.hasClass('in') && !init) {
            targetLink.trigger('click');
        }

        if (selected.val() == '') {
            acc.find('.panel > .collapse.in').each(function () {
                $(this).collapse('hide');
            });
        }
    }
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
