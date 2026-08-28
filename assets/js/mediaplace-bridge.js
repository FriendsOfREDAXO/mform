/**
 * Gemeinsame Weiche zwischen dem klassischen Medienpool-Popup und dem
 * MediaPlace-Overlay (FriendsOfREDAXO/mediaplace), falls installiert und
 * aktiv. Wird von allen MForm-Widgets genutzt, die Medien picken
 * (imglist.js, list-widget.js, customlink.js) -- an einer Stelle
 * definiert statt pro Widget dupliziert, siehe boot.php (laedt diese
 * Datei vor den drei genannten). Guard (window.X = window.X || {...})
 * sorgt dafuer, dass eine bereits von einem anderen AddOn (z.B. tinymce,
 * cke5) definierte Bridge nicht ueberschrieben wird -- keine harte
 * Abhaengigkeit von MediaPlace in irgendeine Richtung.
 */
window.rex5MediaplaceBridge = window.rex5MediaplaceBridge || {
    isActive: function () {
        return typeof MP3 !== 'undefined' && typeof MP3.open === 'function';
    },
    // onSelect(filename) wie beim klassischen Popup (Single-Select).
    // onSelect(filenames[]) bei options.multiple (Array von Dateinamen).
    // options.filter waehlt optional den Start-Typ-Tab vor (z.B. 'images').
    pick: function (onSelect, options) {
        MP3.open(onSelect, options || {});
    },
    // Oeffnet den Overlay direkt im Detail-Panel einer Datei (Browse-only).
    show: function (filename) {
        if (typeof MP3.openFile === 'function') {
            MP3.openFile(filename);
        }
    }
};

/**
 * Best-effort-Ableitung eines MediaPlace-Start-Tabs (options.filter) aus
 * einer Endungsliste (z.B. aus MForm-Widget-Konfigurationen "jpg,png").
 * Nur eine Rueckmeldung, wenn die Liste eindeutig EINER Kategorie zuzuordnen
 * ist -- bei gemischten oder unbekannten Endungen lieber gar keinen Filter
 * setzen (Overlay startet bei "Alle Medien") als falsch zu raten. filter ist
 * in MediaPlace ohnehin nur ein Startwert, keine harte Einschraenkung (siehe
 * mediaplace/assets/mediapool3.js, open()).
 */
function mformMediaplaceFilterForTypes(rawTypes) {
    var IMAGE_EXT = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg', 'bmp', 'tif', 'tiff'];
    var VIDEO_EXT = ['mp4', 'webm', 'ogv', 'mov', 'm4v'];
    var AUDIO_EXT = ['mp3', 'wav', 'flac', 'aac', 'm4a'];
    var DOC_EXT = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv'];

    var types = Array.isArray(rawTypes) ? rawTypes : String(rawTypes || '').split(',');
    types = types.map(function (t) {
        return String(t || '').trim().toLowerCase();
    }).filter(Boolean);
    if (!types.length) return null;

    var buckets = { images: 0, videos: 0, audio: 0, documents: 0, other: 0 };
    types.forEach(function (ext) {
        if (IMAGE_EXT.indexOf(ext) !== -1) buckets.images++;
        else if (VIDEO_EXT.indexOf(ext) !== -1) buckets.videos++;
        else if (AUDIO_EXT.indexOf(ext) !== -1) buckets.audio++;
        else if (DOC_EXT.indexOf(ext) !== -1) buckets.documents++;
        else buckets.other++;
    });

    var nonZero = Object.keys(buckets).filter(function (key) {
        return buckets[key] > 0;
    });
    return 1 === nonZero.length ? nonZero[0] : null;
}

/**
 * Extrahiert args[types] als rohe Endungsliste aus dem serverseitig
 * vorgebauten Popup-Query-String (z.B. "&args[types]=jpg%2Cpng&rex_file_category=5",
 * siehe var_custom_medialist.php). Leere Liste, wenn kein types-Parameter da ist.
 */
function mformMediaplaceExtensionsFromParams(paramsStr) {
    var match = /[?&]args\[types\]=([^&]*)/.exec(String(paramsStr || ''));
    if (!match) return [];
    var raw = decodeURIComponent(match[1].replace(/\+/g, ' '));
    return raw.split(',').map(function (t) {
        return String(t || '').trim().toLowerCase();
    }).filter(Boolean);
}

/**
 * Leitet per mformMediaplaceFilterForTypes() einen Start-Tab (options.filter)
 * aus demselben args[types]-Parameter ab -- nur ein Komfort-Hinweis fuer den
 * initial aktiven Tab, die eigentliche Durchsetzung uebernimmt options.allowedExtensions.
 */
function mformMediaplaceFilterFromParams(paramsStr) {
    var types = mformMediaplaceExtensionsFromParams(paramsStr);
    return types.length ? mformMediaplaceFilterForTypes(types) : null;
}
