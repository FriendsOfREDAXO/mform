<?php

/**
 * @author Friends Of REDAXO
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Repeater;

use FriendsOfRedaxo\MForm\Output\MFormOutput;
use rex_article;
use rex_media;
use rex_url;
use rex_yform_manager_dataset;

use function array_key_exists;
use function is_array;
use function is_scalar;
use function is_string;

/**
 * Typisierter Zugriff auf ein Repeater-Item (#452): Werte der Medien-, Link- und
 * Datensatz-Felder werden zu REDAXO-Objekten aufgelöst, verschachtelte Repeater
 * zu MFormOutput. Das Item selbst bleibt das gespeicherte Array (`raw()`).
 *
 *   foreach (MFormOutput::from(1)->items() as $item) {
 *       $image = $item->media('image');          // rex_media|null
 *       $url   = $item->url('link');             // Artikel, Medium, URL, mailto, tel
 *       $rel   = $item->dataset('news');         // rex_yform_manager_dataset|null
 *       foreach ($item->items('tags')->items() as $tag) { ... }
 *   }
 *
 * Custom-Link-Werte: Artikel-Id oder `redaxo://ID`, Datensatz `rex-<tabelle>://ID`
 * (MForm) oder `yform://tabelle/ID` (Linkmap), Medien als Dateiname, sonst URL,
 * `mailto:`, `tel:` oder Anker.
 */
final class MFormRepeaterItem
{
    /** @param array<string, mixed> $data */
    public function __construct(private readonly array $data)
    {
    }

    /** @return array<string, mixed> */
    public function raw(): array
    {
        return $this->data;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function string(string $key, string $default = ''): string
    {
        $value = $this->data[$key] ?? null;

        return is_scalar($value) ? (string) $value : $default;
    }

    public function int(string $key, int $default = 0): int
    {
        $value = $this->data[$key] ?? null;

        return is_numeric($value) ? (int) $value : $default;
    }

    public function bool(string $key, bool $default = false): bool
    {
        $value = $this->data[$key] ?? null;
        if (null === $value || '' === $value) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    /** Verschachtelter Repeater als MFormOutput (nur aktive Items). */
    public function items(string $key): MFormOutput
    {
        $value = $this->data[$key] ?? null;
        if (is_string($value)) {
            return MFormOutput::from($value);
        }
        if (!is_array($value)) {
            return MFormOutput::empty();
        }

        return MFormOutput::from(MFormRepeaterHelper::prepareItemsForOutput(MFormRepeaterHelper::unwrap($value)));
    }

    /** Medium eines Media-/Custom-Link-Feldes. */
    public function media(string $key): ?rex_media
    {
        $value = $this->string($key);
        if ('' === $value || 'media' !== self::linkTypeOf($value)) {
            return null;
        }

        return rex_media::get($value);
    }

    /**
     * Medien eines Medialist-/Bildlisten-Feldes (kommasepariert), nur existierende.
     *
     * @return list<rex_media>
     */
    public function medialist(string $key): array
    {
        $list = [];
        foreach ($this->list($key) as $filename) {
            $media = rex_media::get($filename);
            if (null !== $media) {
                $list[] = $media;
            }
        }

        return $list;
    }

    /** Artikel eines Link-/Custom-Link-Feldes (Id oder redaxo://ID). */
    public function article(string $key, ?int $clang = null): ?rex_article
    {
        $id = self::articleIdOf($this->string($key));

        return $id > 0 ? rex_article::get($id, $clang) : null;
    }

    /**
     * Artikel eines Linklist-Feldes (kommaseparierte Ids), nur existierende.
     *
     * @return list<rex_article>
     */
    public function linklist(string $key, ?int $clang = null): array
    {
        $list = [];
        foreach ($this->list($key) as $value) {
            $id = self::articleIdOf($value);
            $article = $id > 0 ? rex_article::get($id, $clang) : null;
            if (null !== $article) {
                $list[] = $article;
            }
        }

        return $list;
    }

    /** YForm-Datensatz eines Custom-Link-Feldes (rex-<tabelle>://ID oder yform://tabelle/ID). */
    public function dataset(string $key): ?rex_yform_manager_dataset
    {
        $ref = self::datasetRefOf($this->string($key));
        if (null === $ref || !class_exists(rex_yform_manager_dataset::class)) {
            return null;
        }

        try {
            return rex_yform_manager_dataset::get($ref['id'], $ref['table']);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Art eines Link-Wertes: article, media, dataset, url, mailto, tel, anchor oder '' (leer).
     */
    public function linkType(string $key): string
    {
        return self::linkTypeOf($this->string($key));
    }

    /**
     * URL eines Link-/Custom-Link-Feldes: Artikel über rex_getUrl(), Medium als Medienpool-URL,
     * URL/mailto/tel/Anker unverändert. Datensätze liefern '' (kein allgemeines URL-Schema).
     */
    public function url(string $key, ?int $clang = null): string
    {
        $value = $this->string($key);

        return match (self::linkTypeOf($value)) {
            'article' => rex_getUrl(self::articleIdOf($value), $clang),
            'media' => rex_url::media($value),
            'url', 'mailto', 'tel', 'anchor' => $value,
            default => '',
        };
    }

    /**
     * Kommaseparierte Werte als Liste (getrimmt, ohne Leereintraege).
     *
     * @return list<string>
     */
    public function list(string $key): array
    {
        $value = $this->data[$key] ?? null;
        $parts = is_array($value) ? $value : explode(',', is_scalar($value) ? (string) $value : '');
        $list = [];
        foreach ($parts as $part) {
            $part = trim(is_scalar($part) ? (string) $part : '');
            if ('' !== $part) {
                $list[] = $part;
            }
        }

        return $list;
    }

    public static function linkTypeOf(string $value): string
    {
        $value = trim($value);
        if ('' === $value) {
            return '';
        }
        if (preg_match('/^\d+$/', $value) || preg_match('/^redaxo:\/\/\d+/', $value)) {
            return 'article';
        }
        if (null !== self::datasetRefOf($value)) {
            return 'dataset';
        }
        if (str_starts_with($value, 'mailto:')) {
            return 'mailto';
        }
        if (str_starts_with($value, 'tel:')) {
            return 'tel';
        }
        if (str_starts_with($value, '#')) {
            return 'anchor';
        }
        if (preg_match('#^(https?:)?//#i', $value)) {
            return 'url';
        }
        if (!str_contains($value, '/') && preg_match('/\.[a-z0-9]{2,5}$/i', $value)) {
            return 'media';
        }

        return 'url';
    }

    public static function articleIdOf(string $value): int
    {
        $value = trim($value);
        if (preg_match('/^\d+$/', $value)) {
            return (int) $value;
        }
        if (preg_match('/^redaxo:\/\/(\d+)/', $value, $m)) {
            return (int) $m[1];
        }

        return 0;
    }

    /**
     * @return array{table: string, id: int}|null
     */
    public static function datasetRefOf(string $value): ?array
    {
        $value = trim($value);
        if (preg_match('/^rex-([a-z0-9-]+):\/\/(\d+)$/i', $value, $m)) {
            return ['table' => str_replace('-', '_', $m[1]), 'id' => (int) $m[2]];
        }
        if (preg_match('/^yform:\/\/([a-z0-9_]+)\/(\d+)/i', $value, $m)) {
            return ['table' => $m[1], 'id' => (int) $m[2]];
        }

        return null;
    }
}
