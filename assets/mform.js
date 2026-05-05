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
        // init conditional fieldsets
        initMFormConditionals(mform);
    }, 1)
}

function initMFormConditionals(mform) {
    var targets = mform.find('.mform-conditional-target[data-mform-conditional-source]');
    if (!targets.length) {
        return;
    }

    function normalizeOperator(rawOperator) {
        var op = (rawOperator || '=').toString().trim().toLowerCase();
        if (op === '==' || op === 'eq' || op === 'equals') return '=';
        if (op === '!==') return '!=';
        return op;
    }

    function findSourceFields(source) {
        var sourceStr = (source || '').toString().trim();
        if (!sourceStr.length) return $();

        var candidates = [
            sourceStr,
            'REX_INPUT_VALUE[' + sourceStr + ']',
            'REX_INPUT_VALUE[' + sourceStr + '][]'
        ];

        return mform.find(':input').filter(function () {
            var name = this.name || '';
            var id = this.id || '';

            if (sourceStr.charAt(0) === '#') {
                return ('#' + id) === sourceStr;
            }

            if (id === sourceStr) return true;
            if (candidates.indexOf(name) !== -1) return true;
            if (name.endsWith('[' + sourceStr + ']')) return true;
            if (name.endsWith('[' + sourceStr + '][]')) return true;
            return false;
        });
    }

    function getFieldValue(fields) {
        if (!fields.length) return '';

        var first = fields.first();
        var type = (first.attr('type') || '').toLowerCase();
        var tag = (first.prop('tagName') || '').toLowerCase();

        if (type === 'radio') {
            var checkedRadio = fields.filter(':checked').first();
            return checkedRadio.length ? String(checkedRadio.val()) : '';
        }

        if (type === 'checkbox') {
            if (fields.length > 1) {
                return fields.filter(':checked').map(function () {
                    return String($(this).val());
                }).get();
            }
            return first.is(':checked') ? String(first.val() || '1') : '';
        }

        if (tag === 'select' && first.prop('multiple')) {
            return first.val() || [];
        }

        return String(first.val() || '');
    }

    function isEmptyValue(value) {
        if (Array.isArray(value)) return value.length === 0;
        return value === null || value === undefined || String(value).trim() === '';
    }

    function compareValue(sourceValue, compareValue, operator) {
        var op = normalizeOperator(operator);
        var sourceText = Array.isArray(sourceValue) ? sourceValue.join(',') : String(sourceValue);
        var compareText = String(compareValue || '');

        if (op === 'empty') return isEmptyValue(sourceValue);
        if (op === '!empty') return !isEmptyValue(sourceValue);

        if (op === 'contains') {
            if (Array.isArray(sourceValue)) return sourceValue.indexOf(compareText) !== -1;
            return sourceText.indexOf(compareText) !== -1;
        }

        if (op === 'in') {
            var compareItems = compareText.split(',').map(function (item) {
                return item.trim();
            }).filter(function (item) {
                return item.length > 0;
            });
            if (Array.isArray(sourceValue)) {
                return sourceValue.some(function (item) {
                    return compareItems.indexOf(String(item)) !== -1;
                });
            }
            return compareItems.indexOf(sourceText) !== -1;
        }

        if (op === '>' || op === '<') {
            var sourceNum = parseFloat(sourceText);
            var compareNum = parseFloat(compareText);
            if (!Number.isNaN(sourceNum) && !Number.isNaN(compareNum)) {
                return op === '>' ? sourceNum > compareNum : sourceNum < compareNum;
            }
            return op === '>' ? sourceText > compareText : sourceText < compareText;
        }

        if (op === '!=') return sourceText !== compareText;
        return sourceText === compareText;
    }

    function applyConditional(target) {
        var source = target.data('mform-conditional-source');
        var operator = target.data('mform-conditional-operator') || '=';
        var compare = target.data('mform-conditional-value') || '';
        var action = (target.data('mform-conditional-action') || 'show').toString().toLowerCase();
        var fields = findSourceFields(source);

        if (!fields.length) {
            target.show();
            return;
        }

        var matched = compareValue(getFieldValue(fields), compare, operator);
        var shouldShow = action === 'hide' ? !matched : matched;

        target.toggleClass('mform-conditional-hidden', !shouldShow);
        if (shouldShow) {
            target.show();
        } else {
            target.hide();
        }
    }

    function evaluateAllConditionals() {
        targets.each(function () {
            applyConditional($(this));
        });
    }

    mform.off('.mformConditional').on('change.mformConditional input.mformConditional', ':input', function () {
        evaluateAllConditionals();
    });

    evaluateAllConditionals();
}

function initMFormSelectPicker(mform) {
    mform.find('.selectpicker').each(function () {
        // Stellen Sie sicher, dass der Wert '0' korrekt behandelt wird
        var selectedValue = $(this).attr('data-selected');
        
        $(this).selectpicker('destroy');
        $(this).selectpicker();
        
        // Wenn selectedValue '0' ist, manuell den ausgewählten Wert setzen
        if (selectedValue === '0' || selectedValue === 0) {
            $(this).val('0').selectpicker('refresh');
        }
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
