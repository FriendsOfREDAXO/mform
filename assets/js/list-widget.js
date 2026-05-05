let mform_list_widget = '.mform-list-widget';
let mformListWidgetCounter = 0;

function mformListGetPopupCallbackStore() {
    if (!window.mformListWidgetPopupCallbacks) {
        window.mformListWidgetPopupCallbacks = {
            linklist: {},
            medialist: {}
        };
    }
    return window.mformListWidgetPopupCallbacks;
}

function mformListInstallPopupBridge() {
    if (window.mformListWidgetPopupBridgeInstalled) {
        return;
    }
    window.mformListWidgetPopupBridgeInstalled = true;

    const originalWriteREXLinklist = window.writeREXLinklist;
    const originalWriteREXMedialist = window.writeREXMedialist;

    window.writeREXLinklist = function (id) {
        let result;
        if (typeof originalWriteREXLinklist === 'function') {
            result = originalWriteREXLinklist(id);
        }

        const key = String(id || '');
        const cb = mformListGetPopupCallbackStore().linklist[key];
        if (typeof cb === 'function') {
            cb();
        }

        return result;
    };

    window.writeREXMedialist = function (id) {
        let result;
        if (typeof originalWriteREXMedialist === 'function') {
            result = originalWriteREXMedialist(id);
        }

        const key = String(id || '');
        const cb = mformListGetPopupCallbackStore().medialist[key];
        if (typeof cb === 'function') {
            cb();
        }

        return result;
    };
}

function mformListRegisterPopupCallback(type, baseId, callback) {
    mformListInstallPopupBridge();
    const store = mformListGetPopupCallbackStore();
    const key = String(baseId || '');
    if (!store[type]) {
        store[type] = {};
    }
    store[type][key] = callback;
}

$(document).on('rex:ready', function (e, container) {
    setTimeout(function () {
        if (!container || !container.find(mform_list_widget).length) {
            return;
        }
        container.find(mform_list_widget).each(function () {
            mformListWidgetInit($(this));
        });
    }, 2);
});

function mformListWidgetInit(widget) {
    const type = String(widget.data('widget-type') || '').toLowerCase();
    if (!type) return;

    const ids = mformListEnsureIds(widget, type);
    if (!ids || !ids.baseId) return;

    const list = widget.find('ul.mform-list-items');
    const select = widget.find('select.mform-list-select');
    const hidden = widget.find('input.mform-list-value');

    if (!select.find('option').length && hidden.length && hidden.val()) {
        mformListBuildOptionsFromHidden(widget, type);
    }

    mformListRender(widget, type);
    mformListWriteHidden(widget);

    mformListRegisterPopupCallback(type, ids.baseId, function () {
        mformListRender(widget, type);
        mformListWriteHidden(widget);
    });

    list.off('click.mformListWidget').on('click.mformListWidget', 'li', function () {
        mformListSelect(widget, $(this).data('index'));
    });

    widget.find('.mform-list-btn').off('click.mformListWidget').on('click.mformListWidget', function (event) {
        event.preventDefault();
        if ($(this).is('[disabled], .disabled')) {
            return false;
        }

        const action = String($(this).data('action') || '');
        const params = String(widget.attr('data-params') || '');
        const baseId = ids.baseId;

        if (action === 'delete') {
            if (type === 'medialist') {
                deleteREXMedialist(baseId);
            } else {
                deleteREXLinklist(baseId);
            }
            mformListRender(widget, type);
            mformListWriteHidden(widget);
            return false;
        }

        if (action === 'up' || action === 'down') {
            if (type === 'medialist') {
                moveREXMedialist(baseId, action);
            } else {
                moveREXLinklist(baseId, action);
            }
            mformListRender(widget, type);
            mformListWriteHidden(widget);
            return false;
        }

        let popup = null;

        if (type === 'medialist') {
            if (action === 'open') popup = openREXMedialist(baseId, params);
            if (action === 'add') popup = addREXMedialist(baseId, params);
            if (action === 'view') popup = viewREXMedialist(baseId, params);
        }

        if (type === 'linklist' && action === 'open') {
            popup = openREXLinklist(baseId, params);
        }

        mformListBindPopupSync(popup, function () {
            mformListRender(widget, type);
            mformListWriteHidden(widget);
        });

        return false;
    });
}

function mformListEnsureIds(widget, type) {
    const hidden = widget.find('input.mform-list-value');
    const select = widget.find('select.mform-list-select');

    let baseId = String(widget.attr('data-widget-id') || '');
    const hiddenId = String(hidden.attr('id') || '');
    const selectId = String(select.attr('id') || '');

    if (!baseId && hiddenId && hiddenId.indexOf('REX_') === 0) {
        baseId = hiddenId.replace(/^REX_(?:MEDIA|MEDIALIST|LINKLIST)_/, '');
    }
    if (!baseId && selectId && selectId.indexOf('REX_') === 0) {
        baseId = selectId.replace(/^REX_(?:MEDIA|MEDIALIST|LINKLIST)_SELECT_/, '');
    }
    if (!baseId) {
        mformListWidgetCounter += 1;
        baseId = 'mfl_' + mformListWidgetCounter;
    }

    widget.attr('data-widget-id', baseId);

    const hiddenPrefix = type === 'linklist' ? 'REX_LINKLIST_' : 'REX_MEDIALIST_';
    const selectPrefix = type === 'linklist' ? 'REX_LINKLIST_SELECT_' : 'REX_MEDIALIST_SELECT_';

    if (!hidden.attr('id')) {
        hidden.attr('id', hiddenPrefix + baseId);
    }
    if (!select.attr('id')) {
        select.attr('id', selectPrefix + baseId);
    }

    if (!select.attr('name')) {
        const selectNamePrefix = type === 'linklist' ? 'REX_LINKLIST_SELECT' : 'REX_MEDIALIST_SELECT';
        select.attr('name', selectNamePrefix + '[' + baseId + ']');
    }

    return { baseId: baseId };
}

function mformListBuildOptionsFromHidden(widget, type) {
    const hidden = widget.find('input.mform-list-value');
    const select = widget.find('select.mform-list-select');
    const raw = String(hidden.val() || '');
    if (!raw) return;

    const parts = raw.split(',').map(function (item) {
        return String(item || '').trim();
    }).filter(function (item) {
        return item !== '';
    });

    if (!parts.length) return;

    const options = [];
    parts.forEach(function (value) {
        const text = type === 'linklist' ? ('Artikel ' + value) : value;
        options.push($('<option/>').attr('value', value).text(text));
    });

    select.empty();
    options.forEach(function (opt) { select.append(opt); });
}

function mformListRender(widget, type) {
    const list = widget.find('ul.mform-list-items');
    const select = widget.find('select.mform-list-select');
    const options = select.find('option');

    list.empty();

    if (!options.length) {
        widget.addClass('is-empty');
        return;
    }

    widget.removeClass('is-empty');

    if (!select.find('option:selected').length) {
        select.find('option').first().prop('selected', true);
    }

    options.each(function (index) {
        const option = $(this);
        const selected = option.is(':selected');
        const li = $('<li/>')
            .attr('data-index', index)
            .attr('tabindex', '0')
            .toggleClass('is-selected', selected)
            .text(option.text() || option.val());
        list.append(li);
    });
}

function mformListSelect(widget, index) {
    const select = widget.find('select.mform-list-select');
    select.find('option').prop('selected', false);
    const option = select.find('option').eq(parseInt(index, 10));
    if (option.length) {
        option.prop('selected', true);
    }
    mformListRender(widget, String(widget.data('widget-type') || '').toLowerCase());
    mformListWriteHidden(widget);
}

function mformListWriteHidden(widget) {
    const select = widget.find('select.mform-list-select');
    const hidden = widget.find('input.mform-list-value');

    const values = [];
    select.find('option').each(function () {
        values.push(String($(this).val() || ''));
    });

    hidden.val(values.join(','));
    hidden.trigger('input');
    hidden.trigger('change');
}

function mformListBindPopupSync(popup, callback) {
    if (!popup || typeof callback !== 'function') {
        callback();
        return;
    }

    try {
        popup.addEventListener('beforeunload', function () {
            window.setTimeout(callback, 40);
        });
    } catch (e) {
        window.setTimeout(callback, 80);
    }
}
