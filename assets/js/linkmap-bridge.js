/**
 * Weiche zwischen der klassischen Linkmap (Popup) und dem Linkmap-Overlay
 * (FriendsOfREDAXO/linkmap), falls installiert. Gleiches Muster wie
 * mediaplace-bridge.js: window.rex5LinkmapBridge wird nur definiert, wenn es
 * noch niemand getan hat -- Linkmap selbst liefert das Objekt bereits mit,
 * sobald "Klassische Linkmap ersetzen" aktiv ist (linkmap_classic.js). Ist
 * Linkmap installiert, aber diese Einstellung aus, greift die Definition hier
 * auf window.LM zu. Ohne Linkmap ist isActive() false und die Widgets nutzen
 * wie bisher openLinkMap()/openREXLinklist() bzw. das YForm-Popup.
 *
 * API (Auszug, siehe linkmap_classic.js):
 *   isActive()                                 -> bool
 *   pick(onSelect(link, name, article), opts)  -> Artikel waehlen, link = "redaxo://ID"
 *   pickCategory(onSelect, opts)               -> Kategorie waehlen
 *   pickDataset(table, onSelect, opts)         -> YForm-Datensatz, link = "yform://tabelle/id"
 *   browse(opts), resolve(ids, clang)
 */
window.rex5LinkmapBridge = window.rex5LinkmapBridge || {
    isActive: function () {
        return !!(window.LM && typeof window.LM.open === 'function');
    },
    pick: function (onSelect, options) {
        window.LM.open(onSelect, options || {});
    },
    browse: function (options) {
        window.LM.open(null, options || {});
    },
    pickCategory: function (onSelect, options) {
        options = options || {};
        options.categoriesOnly = true;
        window.LM.open(onSelect, options);
    },
    pickDataset: function (table, onSelect, options) {
        options = options || {};
        options.sources = options.sources || ['yform'];
        options.container = { source: 'yform', id: table };
        window.LM.open(onSelect, options);
    },
    resolve: function (ids, clang) {
        return window.LM.resolve(ids, clang);
    }
};

/**
 * Artikel-Id aus einem Linkmap-Ergebnis (article-Objekt oder "redaxo://ID"),
 * damit REX_LINK-artige Felder weiterhin die numerische Id speichern.
 */
function mformLinkmapArticleId(link, article) {
    if (article && article.id) return String(article.id);
    var m = /^redaxo:\/\/(\d+)/.exec(String(link || ''));
    return m ? m[1] : String(link || '');
}

/**
 * Datensatz-Id aus "yform://tabelle/id[?...]".
 */
function mformLinkmapDatasetId(link) {
    var m = /^yform:\/\/[a-z0-9_]+\/(\d+)/i.exec(String(link || ''));
    return m ? m[1] : '';
}
