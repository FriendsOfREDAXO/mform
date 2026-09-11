/**
 * MForm A11y-Metadaten-Pruefung (#397).
 *
 * Felder mit data-mform-a11y (JSON-Regeln) am form-group werden zur Laufzeit gegen den
 * Medienpool geprueft (API mform_a11y_check). Ergebnis erscheint als Hinweisliste unter dem
 * Widget; bei data-mform-a11y-strict="1" blockiert ein offener Befund das Speichern.
 * Laeuft im klassischen Formular und in Flex-Repeater-Items (rex:ready nach dem Klonen).
 */
(function ($) {
    'use strict';

    var CFG = (window.rex && window.rex.mform_a11y) || {};
    var API = CFG.api || (window.rex && window.rex.backend_url ? window.rex.backend_url + 'index.php?rex-api-call=mform_a11y_check' : 'index.php?rex-api-call=mform_a11y_check');
    var POLL_MS = 1200;

    function looksLikeFilename(value) {
        value = $.trim(value || '');
        if (!value || value.indexOf('/') !== -1 || value.indexOf('\\') !== -1) return false;
        if (/^(https?:|mailto:|tel:|#|\d+$)/i.test(value)) return false;
        return /\.[a-z0-9]{2,5}$/i.test(value);
    }

    function collectFiles($group) {
        var files = [];
        var push = function (v) {
            $.each(String(v || '').split(','), function (_, part) {
                if (looksLikeFilename(part) && files.indexOf($.trim(part)) === -1) files.push($.trim(part));
            });
        };
        // Core-Medienwidget: Textfeld REX_MEDIA_<n>, Medialist: Select-Optionen
        $group.find('input[id^="REX_MEDIA_"], input[name^="REX_INPUT_MEDIA["], input[data-mfr-field][type="text"], input[data-mfr-field][type="hidden"]').each(function () { push(this.value); });
        $group.find('select[id^="REX_MEDIALIST_SELECT_"] option').each(function () { push(this.value); });
        // MForm-Widgets: Custom Link (Hidden-Wert), Listen (CSV), Custom Link Multiple
        $group.find('.rex-js-widget-customlink input[type="hidden"], .mform-list-value, .mform-cl-multi-value').each(function () {
            var v = this.value;
            if (v && v.charAt(0) === '[') {
                try { $.each(JSON.parse(v), function (_, item) { push(typeof item === 'object' && item ? item.value : item); }); return; } catch (e) { /* kein JSON */ }
            }
            push(v);
        });
        return files;
    }

    function feedbackContainer($group) {
        var $box = $group.data('mformA11yBox');
        if (!$box || !$.contains($group[0], $box[0])) {
            $box = $('<div class="mform-a11y-feedback" aria-live="polite"></div>');
            var $col = $group.find('.col-sm-10, .col-sm-12, .mfr-field-col').first();
            ($col.length ? $col : $group).append($box);
            $group.data('mformA11yBox', $box);
        }
        return $box;
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; });
    }

    function render($group, results, files) {
        var $box = feedbackContainer($group);
        var issues = 0;
        var html = '';
        $.each(results, function (_, r) {
            if (!r.issues || !r.issues.length) return;
            issues += r.issues.length;
            var name = files.length > 1 || !r.exists ? '<strong>' + escapeHtml(r.filename) + '</strong>: ' : '';
            $.each(r.issues, function (_, issue) {
                var text = issue.message + (issue.languages && issue.languages.length ? ' (' + escapeHtml(issue.languages.join(', ')) + ')' : '');
                var link = r.edit_url ? ' <a href="' + escapeHtml(r.edit_url) + '" target="_blank" rel="noopener" class="mform-a11y-edit">' + escapeHtml(CFG.edit || 'Metadaten bearbeiten') + '</a>' : '';
                html += '<li>' + name + escapeHtml(text) + link + '</li>';
            });
        });
        $group.toggleClass('mform-a11y-has-issues', issues > 0);
        if (issues > 0) {
            $box.html('<ul class="mform-a11y-list"><li class="mform-a11y-head"><i class="rex-icon fa-universal-access"></i></li>' + html + '</ul>').show();
        } else if (files.length) {
            $box.html('<span class="mform-a11y-ok"><i class="rex-icon fa-check"></i> ' + escapeHtml(CFG.ok || 'OK') + '</span>').show();
        } else {
            $box.empty().hide();
        }
        $group.data('mformA11yIssues', issues);
    }

    function check($group) {
        var rules = $group.attr('data-mform-a11y');
        var files = collectFiles($group);
        var key = files.join('|');
        if ($group.data('mformA11yKey') === key) return;
        $group.data('mformA11yKey', key);
        if (!files.length) { render($group, [], files); return; }
        $.ajax({
            url: API,
            type: 'POST',
            dataType: 'json',
            data: { files: JSON.stringify(files), rules: rules }
        }).done(function (data) {
            render($group, (data && data.results) || [], files);
        }).fail(function () {
            $group.data('mformA11yKey', null);
        });
    }

    function init(root) {
        $(root || document).find('[data-mform-a11y]').addBack('[data-mform-a11y]').each(function () {
            var $group = $(this);
            if ($group.data('mformA11yInit')) return;
            $group.data('mformA11yInit', true);
            check($group);
            $group.on('change input rex:selectMedia', function () { setTimeout(function () { check($group); }, 50); });
            // Widgets setzen Werte oft per JS ohne Event: kleiner Poll auf Wertaenderung.
            var timer = setInterval(function () {
                if (!$.contains(document, $group[0])) { clearInterval(timer); return; }
                check($group);
            }, POLL_MS);
        });
    }

    // Strict: Speichern blockieren, solange Befunde offen sind.
    $(document).on('submit', 'form', function (e) {
        var $blocking = $(this).find('[data-mform-a11y-strict="1"]').filter(function () { return ($(this).data('mformA11yIssues') || 0) > 0; });
        if (!$blocking.length) return;
        e.preventDefault();
        $blocking.first().addClass('has-error')[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        window.alert(CFG.blocked || 'Barrierefreiheits-Metadaten fehlen.');
    });

    $(window).on('rex:selectMedia', function () { $('[data-mform-a11y]').each(function () { var $g = $(this); setTimeout(function () { check($g); }, 100); }); });
    $(document).on('rex:ready', function (e, container) { init(container && container[0] ? container[0] : document); });
    $(function () { init(document); });
})(jQuery);
