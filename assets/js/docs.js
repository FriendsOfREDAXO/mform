/**
 * MForm Docs – Copy-Buttons fuer Code-Bloecke + TOC-Filter
 *
 * Re-Init bei jeder PJAX-Navigation via jQuery `rex:ready`.
 */
(function ($) {
    'use strict';

    function initCopyButtons(root) {
        function setCopiedState(btn) {
            btn.textContent = '\u2713 Kopiert';
            btn.classList.add('copied');
            setTimeout(function () {
                btn.textContent = 'Kopieren';
                btn.classList.remove('copied');
            }, 2000);
        }

        function copyText(text, onSuccess) {
            function fallback() {
                try {
                    var ta = document.createElement('textarea');
                    ta.value = text;
                    ta.setAttribute('readonly', '');
                    ta.style.position = 'fixed';
                    ta.style.opacity = '0';
                    document.body.appendChild(ta);
                    ta.select();
                    var ok = document.execCommand && document.execCommand('copy');
                    document.body.removeChild(ta);
                    if (ok) {
                        onSuccess();
                    }
                } catch (e) {
                    // ignore
                }
            }

            if (
                navigator.clipboard &&
                typeof navigator.clipboard.writeText === 'function' &&
                window.isSecureContext
            ) {
                navigator.clipboard.writeText(text).then(onSuccess).catch(fallback);
                return;
            }
            fallback();
        }

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
                copyText(code.textContent || '', function () {
                    setCopiedState(btn);
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
