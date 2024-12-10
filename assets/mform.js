$(document).on('rex:ready mblock:change', function (event, element) {
    initMFormElements($(element));
});

function initMFormElements(mform) {
    setTimeout(function () {
        // init tooltip
        initMFormTooltip(mform);
        // init toggle
        initMFormToggle(mform);
        // init tabs
        initMFormTabs(mform);
        // init collapse
        initMFormCollapses(mform);
        // init selectPicker
        initMFormSelectPicker(mform);
        // init radio img inlines
        initMFormRadioImgInlines(mform);
    }, 1)
}

function initMFormSelectPicker(mform) {
    mform.find('.selectpicker').each(function () {
        $(this).selectpicker('destroy');
        $(this).selectpicker();
    });
}

function initMFormTabs(mform) {
    mform.find('.mform-tabs').each(function () {
        let wrapper = $(this);
        $(this).find('ul[role=tablist] a').unbind().bind('click', function () {
            let tab = wrapper.find('div[data-tab-group-nav-tab-id=' + $(this).data('tab-item') + ']'),
                uid = Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
            tab.attr('id', uid);
            $(this).attr('href', '#' + uid);
            $('#' + uid).tab("show");
        });
    });
}

function initMFormCollapses(mform) {
    // toggle mform collapse
    mform.find('input[type=checkbox][data-checkbox-toggle]').each(function () {
        initMFormToggleCollapse($(this), true);
        $(this).unbind().bind("change", function () {
            initMFormToggleCollapse($(this), false);
        });
    });
    // select collapse
    mform.find('select[data-toggle=collapse]').each(function () {
        let that = $(this);
        initMFormSelectCollapse($(this), true);
        $(this).off('change.mform_toggle_collapse').on('change.mform_toggle_collapse', function () {
            initMFormSelectCollapse(that, false);
        });
    });
    // radio collapse
    mform.find('input[type=radio][data-radio-toggle=collapse]').each(function () {
        initMFormRadioCollapse($(this), true);
        $(this).unbind().bind("change", function () {
            initMFormRadioCollapse($(this), false);
        });
    });
    // default collapse
    mform.find('.collapse-group[data-group-accordion=0]').each(function () {
        initMFormLinkCollapse($(this), false);
    })
    // accordion collapse
    mform.find('.collapse-group[data-group-accordion=1]').each(function () {
        initMFormLinkCollapse($(this), true);
    });
}

function initMFormLinkCollapse(element, accordion) {
    element.each(function () {
        $(this).find('.collapse').prev().unbind().bind('click', function () {
            if (accordion === true) {
                $(this).parent().find('> .collapse').collapse('hide');
            }
            if ($(this).attr('aria-expanded') === 'true') {
                $(this).parent().find('a[aria-expanded=true]').attr('aria-expanded', 'false');
            } else {
                $(this).parent().find('a[aria-expanded=true]').attr('aria-expanded', 'false');
                $(this).attr('aria-expanded', 'true');
            }
            $(this).next().collapse('toggle');
        });
    });
}

function initMFormRadioCollapse(element, init) {
    let parent = getParentMForm(element);
    let checkedRadios = element.parents('.form-group').find('input[type=radio]:checked');
    let collapseIds = new Set(); // Set für unique collapse IDs

    checkedRadios.each(function() {
        let toggleItem = $(this).data('toggle-item');
        if (toggleItem !== undefined && toggleItem !== '') {
            collapseIds.add(toggleItem);
        }
    });

    element.parents('.form-group').find('input[type=radio]').each(function() {
        let toggleItem = $(this).data('toggle-item');
        if (toggleItem === undefined || toggleItem === '') return;

        let target = parent.find('.collapse[data-group-collapse-id=' + toggleItem + ']');

        if ($(this).is(":checked")) {
            if (!target.hasClass('in')) {
                toggleCollapseElement(target, 'show', init);
            }
        } else {
            if (!collapseIds.has(toggleItem)) {
                if (target.hasClass('in')) {
                    toggleCollapseElement(target, 'hide', init);
                }
            }
        }
    });
}

function initMFormSelectCollapse(element, init) {
    let parent = getParentMForm(element),
        collapseId = element.children("option:selected").data('toggle-item');
    if (collapseId !== undefined) {
        parent.find('.collapse[data-group-collapse-id=' + collapseId + ']').parent().find('> .collapse').each(function () {
            if ($(this).data('group-collapse-id') === collapseId) {
                toggleCollapseElement($(this), 'show', init);
            } else {
                toggleCollapseElement($(this), 'hide', init);
            }
        });
    }
}

function initMFormToggleCollapse(element, init) {
    let parent = getParentMForm(element),
        target = parent.find('.collapse[data-group-collapse-id=' + element.data('toggle-item') + ']');
    if (init && element.is(':checked')) {
        toggleCollapseElement(target, 'show', init);
    } else {
        if (element.prop('checked')) {
            toggleCollapseElement(target, 'show', init);
            target.collapse('show');
        } else {
            toggleCollapseElement(target, 'hide', init);
        }
    }
}

function getParentMForm(element) {
    let parents = element.parents('.mform');
    return (parents.length > 1) ? $(parents[0]) : parents
}

function initMFormTooltip(mform) {
    if (mform.find('[data-toggle="tooltip"]').length) {
        try {
            mform.tooltip('destroy');
        } catch (exc) {
            // console.log(exc);
        }
    }
    mform.find('[data-toggle="tooltip"]').tooltip();
}

function initMFormToggle(mform) {
    mform.find('input[type=checkbox][data-mform-toggle^=toggle]').each(function () {
        let parent = $(this).parent();
        if (parent.hasClass('mform-toggle')) {
            $(this).clone(false).insertBefore(parent);
            parent.remove();
        }
    });
    mform.find('input[type=checkbox][data-mform-toggle^=toggle]').bootstrapMFormToggle('destroy').bootstrapMFormToggle();
}

function toggleCollapseElement(element, type, init) {
    if (init) {
        if (type === 'show') {
            element.addClass('in').removeClass('collapsed');
        } else if (type === 'hide') {
            element.addClass('collapsed').removeClass('in');
        }
    } else {
        element.collapse(type);
    }
}

function initMFormRadioImgInlines(mform) {
    mform.find('div.radio').each(function () {
        let that = $(this);
        $(this).find('input[type=radio]').each(function () {
            $(this).on('change', function () {
                that.parent().find('label').removeClass('active');
                if ($(this).prop('checked')) {
                    $(this).parent().addClass('active');
                }
            });
            if ($(this).prop('checked')) {
                $(this).parent().addClass('active');
            }
        });
    });
}
