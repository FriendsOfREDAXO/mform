<?php

/**
 * MForm Dokumentation – Consent-Manager-Stil
 * Sidebar: Navigation + Suche + TOC | Main: gerendertes Markdown
 *
 * @var rex_addon $this
 * @psalm-scope-this rex_addon
 */

$addon = rex_addon::get('mform');

$backendLanguage = rex_i18n::getLanguage();
$readmeCandidates = [
    'README.' . $backendLanguage . '.md',
    'README.' . explode('_', $backendLanguage)[0] . '.md',
    'README.md',
];
$readmeFile = 'README.md';
foreach ($readmeCandidates as $candidate) {
    if (file_exists($addon->getPath($candidate))) {
        $readmeFile = $candidate;
        break;
    }
}

// ── Doc-Seiten definieren ─────────────────────────────────────────────────────
$mform_doc_pages = [
    'basics' => [
        'title' => rex_i18n::msg('mform_docs_basics'),
        'icon'  => 'rex-icon fa-file-text-o',
        'file'  => 'docs/01_basics.md',
    ],
    'redaxo' => [
        'title' => rex_i18n::msg('mform_docs_redaxo'),
        'icon'  => 'rex-icon fa-puzzle-piece',
        'file'  => 'docs/02_redaxo.md',
    ],
    'customlink' => [
        'title' => rex_i18n::msg('mform_docs_customlink'),
        'icon'  => 'rex-icon fa-link',
        'file'  => 'docs/03_customlink.md',
    ],
    'imagelist' => [
        'title' => rex_i18n::msg('mform_docs_imagelist'),
        'icon'  => 'rex-icon fa-picture-o',
        'file'  => 'docs/04_imagelist.md',
    ],
    'wrapper' => [
        'title' => rex_i18n::msg('mform_docs_wrapper'),
        'icon'  => 'rex-icon fa-object-group',
        'file'  => 'docs/05_wrapper.md',
    ],
    'advanced' => [
        'title' => rex_i18n::msg('mform_docs_advanced'),
        'icon'  => 'rex-icon fa-cog',
        'file'  => 'docs/06_advanced.md',
    ],
    'repeater' => [
        'title' => rex_i18n::msg('mform_docs_repeater'),
        'icon'  => 'rex-icon fa-repeat',
        'file'  => 'docs/07_repeater.md',
    ],
    'templates' => [
        'title' => rex_i18n::msg('mform_docs_templates'),
        'icon'  => 'rex-icon fa-clone',
        'file'  => 'docs/09_templates.md',
    ],
    'readme' => [
        'title' => rex_i18n::msg('mform_docs_readme'),
        'icon'  => 'rex-icon fa-book',
        'file'  => $readmeFile,
    ],
    'changelog' => [
        'title' => rex_i18n::msg('mform_changelog'),
        'icon'  => 'rex-icon fa-list',
        'file'  => 'CHANGELOG.md',
    ],
];

// ── File → Key-Map für Querlinks ──────────────────────────────────────────────
$mform_doc_file_map = [];
foreach ($mform_doc_pages as $key => $page) {
    $mform_doc_file_map[strtolower($page['file'])] = $key;
    $mform_doc_file_map[strtolower(basename($page['file']))] = $key;
}

// ── Request-Parameter ────────────────────────────────────────────────────────
$func = rex_request('func', 'string', '');
if ($func === '') {
    $func = rex_request('amp;func', 'string', 'basics');
}
$func = preg_replace('/^amp;/', '', $func);
if (!isset($mform_doc_pages[$func])) {
    $func = 'basics';
}
$q = rex_request('q', 'string', '');

// ── Volltext-Suche ────────────────────────────────────────────────────────────
$searchResults = [];
if ($q !== '') {
    foreach ($mform_doc_pages as $key => $p) {
        $contentRaw = rex_file::get($addon->getPath($p['file']));
        if (!$contentRaw) {
            continue;
        }
        $pos = stripos($contentRaw, $q);
        if ($pos !== false) {
            $start = max(0, $pos - 70);
            $length = strlen($q) + 140;
            $snippet = mb_substr($contentRaw, $start, $length);
            if ($start > 0) {
                $snippet = '...' . $snippet;
            }
            if (($start + $length) < strlen($contentRaw)) {
                $snippet .= '...';
            }
            $snippet = rex_escape($snippet);
            $snippet = preg_replace('/(' . preg_quote(rex_escape($q), '/') . ')/i', '<mark>$1</mark>', $snippet);

            $heading = '';
            $anchor = '';
            $preContent = substr($contentRaw, 0, $pos);
            if (preg_match_all('/^#{1,6}\s+(.+)$/m', $preContent, $matches)) {
                $lastHeader = end($matches[1]);
                $heading = trim((string) $lastHeader);
                $anchor = rex_string::normalize($heading, '-');
            }

            $searchResults[$key] = $p;
            $searchResults[$key]['snippet'] = $snippet;
            $searchResults[$key]['heading'] = $heading;
            $searchResults[$key]['anchor'] = $anchor;
        }
    }
}

// ── Navigation ───────────────────────────────────────────────────────────────
$nav = '<ul class="nav nav-pills nav-stacked">';
foreach ($mform_doc_pages as $key => $p) {
    $active = ($key === $func && $q === '') ? ' class="active"' : '';
    $nav .= '<li' . $active . '><a href="' . rex_url::currentBackendPage(['func' => $key]) . '"><i class="' . $p['icon'] . '"></i> ' . rex_escape($p['title']) . '</a></li>';
}
$nav .= '</ul>';

// ── Content-Rendering ─────────────────────────────────────────────────────────
$content = '';
$tocHtml = '';

if ($q !== '') {
    $content = '<h2>' . rex_i18n::msg('mform_docs_search_results_for') . ' <em>' . rex_escape($q) . '</em></h2>';
    if (count($searchResults) > 0) {
        $content .= '<div class="list-group">';
        foreach ($searchResults as $key => $p) {
            $url = rex_url::currentBackendPage(['func' => $key]);
            $title = rex_escape($p['title']);
            if (!empty($p['anchor'])) {
                $url .= '#' . $p['anchor'];
                $title .= ' <small class="text-muted"><i class="rex-icon fa-angle-right"></i> ' . rex_escape($p['heading']) . '</small>';
            }
            $content .= '<a href="' . $url . '" class="list-group-item">';
            $content .= '<h4 class="list-group-item-heading"><i class="' . $p['icon'] . '"></i> ' . $title . '</h4>';
            $content .= '<p class="list-group-item-text" style="color:#666;font-size:13px;margin-top:5px">' . $p['snippet'] . '</p>';
            $content .= '</a>';
        }
        $content .= '</div>';
    } else {
        $content .= rex_view::warning(rex_i18n::msg('mform_docs_no_results'));
    }
} else {
    $file = $addon->getPath($mform_doc_pages[$func]['file']);
    if (file_exists($file)) {
        $md = rex_file::get($file);

        // Querlinks auflösen
        $md = preg_replace_callback('/\[([^\]]+)\]\(([^)#]+\.md)(#[^)]+)?\)/i', static function ($matches) use ($mform_doc_file_map) {
            $fileName = strtolower(basename(str_replace('\\', '/', (string) $matches[2])));
            if (!isset($mform_doc_file_map[$fileName])) {
                return $matches[0];
            }
            $url = rex_url::currentBackendPage(['func' => $mform_doc_file_map[$fileName]]);
            if (isset($matches[3])) {
                $url .= $matches[3];
            }
            return '[' . $matches[1] . '](' . $url . ')';
        }, $md);

        $parsed = rex_markdown::factory()->parse((string) $md, [
            rex_markdown::SOFT_LINE_BREAKS => false,
            rex_markdown::HIGHLIGHT_PHP    => true,
        ]);

        // Headings mit IDs versehen + TOC aufbauen
        $toc = [];
        $parsed = preg_replace_callback('/<h([1-6])>(.*?)<\/h\1>/s', static function ($m) use (&$toc) {
            $tag  = $m[1];
            $text = strip_tags($m[2]);
            $id   = rex_string::normalize($text, '-');
            $toc[] = ['level' => (int) $tag, 'text' => $text, 'id' => $id];
            return '<h' . $tag . ' id="' . $id . '">' . $m[2] . '</h' . $tag . '>';
        }, (string) $parsed);

        // TOC HTML
        if (!empty($toc)) {
            $tocHtml = '<div class="panel panel-default" style="margin-top:20px">'
                . '<div class="panel-heading"><b>' . rex_i18n::msg('mform_docs_toc') . '</b></div>'
                . '<div class="panel-body" style="padding:10px">'
                . '<input type="text" id="mform-toc-filter" class="form-control input-sm" placeholder="' . rex_i18n::msg('mform_docs_toc_filter') . '...">'
                . '</div>'
                . '<div class="list-group" id="mform-toc-list" style="max-height:500px;overflow-y:auto">';
            foreach ($toc as $item) {
                $pl = ($item['level'] - 1) * 15 + 15;
                $style = 'padding-left:' . $pl . 'px;';
                if ($item['level'] === 1) {
                    $style .= 'font-weight:700;text-transform:uppercase;font-size:11px;letter-spacing:.5px;';
                } elseif ($item['level'] === 2) {
                    $style .= 'font-weight:700;font-size:13px;margin-top:5px;';
                } else {
                    $style .= 'font-size:13px;';
                }
                $tocHtml .= '<a href="#' . $item['id'] . '" class="list-group-item" style="' . $style . '">' . rex_escape($item['text']) . '</a>';
            }
            $tocHtml .= '</div></div>';
        }

        $content = '<a id="mform-doc-top"></a><div class="rex-docs" style="display:block!important">' . $parsed . '</div>';
    }
}

// ── Suchformular ──────────────────────────────────────────────────────────────
$searchForm = '<form action="' . rex_url::currentBackendPage() . '" method="get" style="margin-bottom:20px">'
    . '<input type="hidden" name="page" value="mform/docs">'
    . '<div class="input-group">'
    . '<input type="text" class="form-control" name="q" value="' . rex_escape($q) . '" placeholder="' . rex_i18n::msg('mform_docs_search_placeholder') . '...">'
    . '<span class="input-group-btn"><button class="btn btn-default" type="submit"><i class="rex-icon fa-search"></i></button></span>'
    . '</div></form>';

// ── Sidebar ───────────────────────────────────────────────────────────────────
$sidebarContent = $searchForm . $nav;
if ($tocHtml !== '') {
    $sidebarContent .= $tocHtml;
}

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('mform_docs'), false);
$fragment->setVar('body', $sidebarContent, false);
$sidebar = $fragment->parse('core/page/section.php');

// ── Haupt-Content ────────────────────────────────────────────────────────────
$fragment = new rex_fragment();
$fragment->setVar('title', $q !== '' ? rex_i18n::msg('mform_docs_search_results') : $mform_doc_pages[$func]['title'], false);
$fragment->setVar('body', $content, false);
$mainContent = $fragment->parse('core/page/section.php');

// ── Layout ────────────────────────────────────────────────────────────────────
echo '<div class="row"><div class="col-md-3">' . $sidebar . '</div><div class="col-md-9">' . $mainContent . '</div></div>';

echo '
<style>
mark { background:#ffe066; color:#000; padding:0 2px; border-radius:2px; }
.rex-docs pre { position:relative; }
.rex-docs .btn-copy-code {
    position:absolute; top:5px; right:5px; padding:3px 8px; font-size:12px;
    opacity:.5; transition:opacity .2s; cursor:pointer; background:#fff;
    border:1px solid #ddd; border-radius:3px; color:#333; z-index:10;
}
.rex-docs pre:hover .btn-copy-code { opacity:1; }
.rex-docs .btn-copy-code.copied { background:#5bb75b; color:#fff; border-color:#5bb75b; opacity:1; }
</style>
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Copy-Buttons an Code-Blöcken
    document.querySelectorAll(".rex-docs pre").forEach(function (pre) {
        var btn = document.createElement("button");
        btn.className = "btn-copy-code";
        btn.textContent = "Kopieren";
        btn.addEventListener("click", function () {
            var code = pre.querySelector("code");
            if (!code) return;
            navigator.clipboard.writeText(code.textContent || "").then(function () {
                btn.textContent = "✓ Kopiert";
                btn.classList.add("copied");
                setTimeout(function () { btn.textContent = "Kopieren"; btn.classList.remove("copied"); }, 2000);
            });
        });
        pre.appendChild(btn);
    });

    // TOC-Filter
    var filter = document.getElementById("mform-toc-filter");
    if (filter) {
        filter.addEventListener("input", function () {
            var val = this.value.toLowerCase();
            document.querySelectorAll("#mform-toc-list .list-group-item").forEach(function (item) {
                item.style.display = val === "" || item.textContent.toLowerCase().includes(val) ? "" : "none";
            });
        });
    }
});
</script>
';
