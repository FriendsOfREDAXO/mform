/**
 * MForm Docs – Copy-Buttons fuer Code-Bloecke + TOC-Filter
 *
 * Re-Init bei jeder PJAX-Navigation via jQuery `rex:ready`.
 */
(function ($) {
    'use strict';

    function getCodeBlocks(root) {
        return root.querySelectorAll('.rex-docs pre, pre.rex-code');
    }

    function setCopyButtonState(button, label, isSuccess) {
        button.textContent = label;
        button.classList.toggle('copied', !!isSuccess);
        button.classList.toggle('copy-failed', isSuccess === false);

        window.setTimeout(function () {
            button.textContent = 'Kopieren';
            button.classList.remove('copied');
            button.classList.remove('copy-failed');
        }, isSuccess === false ? 2500 : 2000);
    }

    function initCodeBlocks(root) {
        getCodeBlocks(root).forEach(function (pre) {
            if (pre.dataset.mformCodeInit === '1') {
                return;
            }

            pre.dataset.mformCodeInit = '1';

            var wrapper = document.createElement('div');
            wrapper.className = 'mform-docs-code';
            pre.parentNode.insertBefore(wrapper, pre);
            wrapper.appendChild(pre);

            var btn = document.createElement('button');
            btn.className = 'btn-copy-code';
            btn.type = 'button';
            btn.textContent = 'Kopieren';
            btn.addEventListener('click', function () {
                if (!window.mformUi || typeof window.mformUi.getCodeText !== 'function') {
                    return;
                }

                var codeText = window.mformUi.getCodeText(pre);
                if (!codeText) {
                    return;
                }

                window.mformUi.copyTextToClipboard(codeText).then(function () {
                    setCopyButtonState(btn, '\u2713 Kopiert', true);
                }).catch(function () {
                    setCopyButtonState(btn, 'Fehlgeschlagen', false);
                });
            });
            wrapper.appendChild(btn);
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
        initCodeBlocks(document);
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
