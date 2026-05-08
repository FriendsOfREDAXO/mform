/**
 * MForm Docs – Copy-Buttons fuer Code-Bloecke + TOC-Filter
 *
 * Re-Init bei jeder PJAX-Navigation via jQuery `rex:ready`.
 */
(function ($) {
    'use strict';

    function initCopyButtons(root) {
        root.querySelectorAll('.rex-docs pre').forEach(function (pre) {
            if (pre.querySelector('.btn-copy-code')) {
                return;
            }
            var btn = document.createElement('button');
            btn.className = 'btn-copy-code';
            btn.type = 'button';
            btn.textContent = 'Kopieren';
            btn.addEventListener('click', function () {
                var code = pre.querySelector('code');
                if (!code) {
                    return;
                }
                navigator.clipboard.writeText(code.textContent || '').then(function () {
                    btn.textContent = '\u2713 Kopiert';
                    btn.classList.add('copied');
                    setTimeout(function () {
                        btn.textContent = 'Kopieren';
                        btn.classList.remove('copied');
                    }, 2000);
                });
            });
            pre.appendChild(btn);
        });
    }

    function initTocFilter(root) {
        var filter = root.querySelector('#mform-toc-filter');
        if (!filter || filter.dataset.mformInitialized === '1') {
            return;
        }
        filter.dataset.mformInitialized = '1';
        filter.addEventListener('input', function () {
            var val = this.value.toLowerCase();
            document.querySelectorAll('#mform-toc-list .list-group-item').forEach(function (item) {
                item.style.display =
                    '' === val || item.textContent.toLowerCase().includes(val) ? '' : 'none';
            });
        });
    }

    function init() {
        initCopyButtons(document);
        initTocFilter(document);
    }

    if (typeof $ === 'function' && $.fn) {
        // REDAXO PJAX-faehiger Init: feuert initial UND nach jeder Pjax-Navigation.
        $(document).on('rex:ready', function () {
            init();
        });
    } else {
        document.addEventListener('DOMContentLoaded', init);
    }
})(window.jQuery);
