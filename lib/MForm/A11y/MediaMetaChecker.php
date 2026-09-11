<?php

/**
 * @author Friends Of REDAXO
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\A11y;

use FriendsOfRedaxo\MForm\DTO\MFormItem;
use rex;
use rex_addon;
use rex_clang;
use rex_i18n;
use rex_media;
use rex_sql;
use rex_url;

use function array_key_exists;
use function in_array;
use function is_array;
use function is_string;

/**
 * Opt-in-Prüfung von Barrierefreiheits-Metadaten für Medien (#397).
 *
 * Ein Feld bekommt die Option `a11y`, z. B.
 *
 *   ->addMediaField(1, ['label' => 'Bild', 'a11y' => ['med_alt', 'med_title']])
 *   ->addMediaField(1, ['a11y' => ['required_media_meta' => [
 *         ['field' => 'med_alt', 'message' => 'Bitte ALT-Text im Medienpool ergänzen.'],
 *     ], 'strict' => true]])
 *
 * Geprüft wird zur Laufzeit gegen den Medienpool (API `mform_a11y_check`), das
 * Widget zeigt die Hinweise direkt am Feld. Gespeicherte Daten bleiben unverändert.
 *
 * Prüfbare Felder:
 * - `med_alt`: nur bei Bildern. Mit MediaPlace zählt dessen ALT-Status (eigenes ALT-Feld
 *   oder `med_alt`, jeweils mit "dekorativ, kein ALT nötig"); ohne MediaPlace gilt ein
 *   gesetztes `med_alt_decorative` ebenfalls als erfüllt.
 * - jedes andere `med_*`-Metainfo-Feld. Mehrsprachige Felder aus metainfo_lang_fields
 *   (`lang_text`, `lang_textarea`, `*_all`) werden je Online-Sprache geprüft.
 * - `mediaplace:<key>`: eigene MediaPlace-Metadaten (JSON), übersetzbare je Sprache.
 *
 * @phpstan-type Rule array{field: string, message: string}
 * @phpstan-type Rules array{required_media_meta: list<Rule>, strict: bool}
 * @phpstan-type Issue array{field: string, message: string, languages: list<string>, code: string}
 * @phpstan-type FileResult array{filename: string, exists: bool, is_image: bool, edit_url: string, issues: list<Issue>}
 */
final class MediaMetaChecker
{
    public const ATTRIBUTE = 'a11y';
    public const DATA_ATTRIBUTE = 'data-mform-a11y';
    public const DATA_STRICT = 'data-mform-a11y-strict';
    public const FIELD_ALT = 'med_alt';
    public const CLASSIC_DECORATIVE_FIELD = 'med_alt_decorative';
    public const MEDIAPLACE_PREFIX = 'mediaplace:';

    /** @var list<string> Feldtypen, die Medien auswählen und die Option kennen */
    public const MEDIA_ITEM_TYPES = ['media', 'medialist', 'mform-media', 'mform-medialist', 'imglist', 'imagelist', 'custom-link', 'custom-link-multi'];

    /** @var array<string, string>|null Metainfo-Feldname => Typ-Label (lang_text, text, ...) */
    private static ?array $fieldTypes = null;

    /**
     * Normalisiert die `a11y`-Option in eine feste Struktur. null = keine gültige Regel.
     *
     * Akzeptiert: 'med_alt' | ['med_alt', 'med_title'] | ['required_media_meta' => [...], 'strict' => bool]
     * mit Einträgen als Feldname oder ['field' => ..., 'message' => ...].
     *
     * @return Rules|null
     */
    public static function normalizeRules(mixed $a11y): ?array
    {
        if (is_string($a11y)) {
            $a11y = [$a11y];
        }
        if (!is_array($a11y)) {
            return null;
        }

        $entries = $a11y['required_media_meta'] ?? $a11y;
        if (!is_array($entries)) {
            return null;
        }

        $rules = [];
        foreach ($entries as $key => $entry) {
            if ('required_media_meta' === $key || 'strict' === $key) {
                continue;
            }
            if (is_string($entry)) {
                $field = trim($entry);
                $message = '';
            } elseif (is_array($entry) && isset($entry['field']) && is_string($entry['field'])) {
                $field = trim($entry['field']);
                $message = isset($entry['message']) && is_string($entry['message']) ? trim($entry['message']) : '';
            } else {
                continue;
            }
            if ('' === $field || !preg_match('/^(med_\w+|mediaplace:[\w.-]+)$/', $field)) {
                continue;
            }
            $rules[] = ['field' => $field, 'message' => $message];
        }

        if ([] === $rules) {
            return null;
        }

        return ['required_media_meta' => $rules, 'strict' => (bool) ($a11y['strict'] ?? false)];
    }

    /**
     * Verschiebt die `a11y`-Option eines Items in die form-group-Attribute
     * (`data-mform-a11y` mit den Regeln als JSON, `data-mform-a11y-strict`), damit
     * das JS sie am Feld findet. Idempotent, beide Renderpfade rufen das vor dem Rendern.
     */
    public static function applyToItem(MFormItem $item): void
    {
        // Medien-Felder bekommen die Option ueber das Parameter-Array (2. Argument von addMediaField()),
        // Custom-Link ueber die Attribute. Beide Quellen lesen und leeren, damit nichts ans Widget geht.
        $attributes = $item->getAttributes();
        $parameter = $item->getParameter();
        $isMediaType = in_array($item->getType(), self::MEDIA_ITEM_TYPES, true);
        $hasOption = array_key_exists(self::ATTRIBUTE, $attributes) || array_key_exists(self::ATTRIBUTE, $parameter);
        if (!$hasOption && !($isMediaType && self::isDefaultEnabled())) {
            return;
        }
        $option = $attributes[self::ATTRIBUTE] ?? $parameter[self::ATTRIBUTE] ?? null;
        unset($attributes[self::ATTRIBUTE], $parameter[self::ATTRIBUTE]);
        $item->setParameter($parameter);

        // Global aus, oder je Feld mit 'a11y' => false abgeschaltet: keine Pruefung.
        if (!self::isEnabled() || false === $option) {
            $item->setAttributes($attributes);

            return;
        }
        $rules = null !== $option ? self::normalizeRules($option) : ['required_media_meta' => [['field' => self::FIELD_ALT, 'message' => '']], 'strict' => false];

        if (null !== $rules && $isMediaType) {
            $formGroup = isset($attributes['form-group-attributes']) && is_array($attributes['form-group-attributes']) ? $attributes['form-group-attributes'] : [];
            $formGroup[self::DATA_ATTRIBUTE] = (string) json_encode($rules['required_media_meta'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($rules['strict']) {
                $formGroup[self::DATA_STRICT] = '1';
            }
            $attributes['form-group-attributes'] = $formGroup;
        }

        $item->setAttributes($attributes);
    }

    /**
     * Prüft mehrere Dateien gegen dieselben Regeln.
     *
     * @param list<string> $filenames
     * @param list<Rule> $rules
     * @return list<FileResult>
     */
    public function checkMany(array $filenames, array $rules): array
    {
        $results = [];
        foreach (array_values(array_unique($filenames)) as $filename) {
            $results[] = $this->check($filename, $rules);
        }

        return $results;
    }

    /**
     * Prüft eine Datei gegen die Regeln.
     *
     * @param list<Rule> $rules
     * @return FileResult
     */
    public function check(string $filename, array $rules): array
    {
        $filename = trim($filename);
        $media = '' !== $filename ? rex_media::get($filename) : null;

        if (null === $media) {
            return [
                'filename' => $filename,
                'exists' => false,
                'is_image' => false,
                'edit_url' => '',
                'issues' => [['field' => '', 'message' => rex_i18n::msg('mform_a11y_file_missing'), 'languages' => [], 'code' => 'file_missing']],
            ];
        }

        $issues = [];
        foreach ($rules as $rule) {
            $field = $rule['field'];
            $state = $this->fieldState($media, $field);
            if ($state['ok']) {
                continue;
            }
            // Konfigurationsfehler (Feld gibt es nicht) immer als solcher melden, nicht mit der Modul-Meldung überdecken.
            $message = '' !== $rule['message'] && 'unknown_field' !== $state['code'] ? $rule['message'] : $this->defaultMessage($field, $state['code']);
            $issues[] = ['field' => $field, 'message' => $message, 'languages' => $state['languages'], 'code' => $state['code']];
        }

        return [
            'filename' => $filename,
            'exists' => true,
            'is_image' => $media->isImage(),
            'edit_url' => rex_url::backendPage('mediapool/media', ['file_id' => $media->getId()], false),
            'issues' => $issues,
        ];
    }

    /**
     * @return array{ok: bool, languages: list<string>, code: string}
     */
    private function fieldState(rex_media $media, string $field): array
    {
        if (self::FIELD_ALT === $field) {
            return $this->altState($media);
        }
        if (str_starts_with($field, self::MEDIAPLACE_PREFIX)) {
            return $this->mediaplaceFieldState($media, substr($field, strlen(self::MEDIAPLACE_PREFIX)));
        }

        $types = self::fieldTypes();
        if (!array_key_exists($field, $types)) {
            return ['ok' => false, 'languages' => [], 'code' => 'unknown_field'];
        }

        $value = $media->getValue($field);
        if (str_starts_with($types[$field], 'lang_')) {
            return $this->languageState($value);
        }

        return ['ok' => '' !== trim((string) $value), 'languages' => [], 'code' => 'missing'];
    }

    /**
     * ALT-Text: nur Bilder; MediaPlace-Status hat Vorrang (kennt eigenes ALT-Feld und "dekorativ").
     *
     * @return array{ok: bool, languages: list<string>, code: string}
     */
    private function altState(rex_media $media): array
    {
        if (!$media->isImage()) {
            return ['ok' => true, 'languages' => [], 'code' => ''];
        }

        // MediaPlace mit eigenem ALT-Feld: wie MediaPlace selbst (AltTextStatus) zaehlt dann nur dieses Feld,
        // je Online-Sprache, "dekorativ" gilt fuer alle Sprachen. Das klassische med_alt bleibt aussen vor.
        if (self::mediaplaceAvailable() && class_exists(\FriendsOfRedaxo\Mediaplace\AltTextStatus::class)) {
            $ownField = \FriendsOfRedaxo\Mediaplace\AltTextStatus::resolveOwnAltField();
            if (null !== $ownField) {
                return $this->mediaplaceFieldState($media, $ownField->getKey());
            } else {
                $ownData = class_exists(\FriendsOfRedaxo\Mediaplace\MetainfoJsonStorage::class) ? \FriendsOfRedaxo\Mediaplace\MetainfoJsonStorage::loadFromMedia($media) : [];
                if (!\FriendsOfRedaxo\Mediaplace\AltTextStatus::isMissing($media, $ownData)) {
                    return ['ok' => true, 'languages' => [], 'code' => ''];
                }
            }
        }

        $types = self::fieldTypes();
        if (array_key_exists(self::CLASSIC_DECORATIVE_FIELD, $types) && (bool) $media->getValue(self::CLASSIC_DECORATIVE_FIELD)) {
            return ['ok' => true, 'languages' => [], 'code' => ''];
        }
        if (!array_key_exists(self::FIELD_ALT, $types)) {
            return ['ok' => false, 'languages' => [], 'code' => self::mediaplaceAvailable() ? 'missing_alt' : 'unknown_field'];
        }

        $value = $media->getValue(self::FIELD_ALT);
        if (str_starts_with($types[self::FIELD_ALT], 'lang_')) {
            $state = $this->languageState($value);
            $state['code'] = 'missing_alt';

            return $state;
        }

        return ['ok' => '' !== trim((string) $value), 'languages' => [], 'code' => 'missing_alt'];
    }

    /**
     * Eigene MediaPlace-Metadaten (med_json_data): übersetzbare Felder je Online-Sprache,
     * ALT-Widget mit "dekorativ".
     *
     * @return array{ok: bool, languages: list<string>, code: string}
     */
    private function mediaplaceFieldState(rex_media $media, string $key): array
    {
        if (!self::mediaplaceAvailable() || !class_exists(\FriendsOfRedaxo\Mediaplace\MetainfoJsonStorage::class) || !class_exists(\FriendsOfRedaxo\Mediaplace\MetainfoFieldGroup::class)) {
            return ['ok' => false, 'languages' => [], 'code' => 'unknown_field'];
        }

        $definition = null;
        foreach (\FriendsOfRedaxo\Mediaplace\MetainfoFieldGroup::getFields() as $fieldDefinition) {
            if ($fieldDefinition->getKey() === $key) {
                $definition = $fieldDefinition;
                break;
            }
        }
        if (null === $definition) {
            return ['ok' => false, 'languages' => [], 'code' => 'unknown_field'];
        }
        if ($definition->isImageOnly() && !$media->isImage()) {
            return ['ok' => true, 'languages' => [], 'code' => ''];
        }

        $data = \FriendsOfRedaxo\Mediaplace\MetainfoJsonStorage::loadFromMedia($media);
        $value = $data[$key] ?? null;

        if ('alt' === $definition->getWidgetType()) {
            if (is_array($value) && !empty($value['decorative'])) {
                return ['ok' => true, 'languages' => [], 'code' => ''];
            }
            $state = $this->languageState(is_array($value) ? ($value['text'] ?? null) : $value);
            $state['code'] = 'missing_alt';

            return $state;
        }

        if ($definition->isTranslatable()) {
            return $this->languageState($value);
        }

        $ok = is_array($value) ? [] !== array_filter($value, static fn (mixed $v): bool => '' !== trim((string) (is_scalar($v) ? $v : ''))) : '' !== trim((string) $value);

        return ['ok' => $ok, 'languages' => [], 'code' => 'missing'];
    }

    /**
     * Prüft einen mehrsprachigen Wert je Online-Sprache. Formate:
     * metainfo_lang_fields (JSON-Liste [{clang_id, value}] oder Objekt {clangId: value}) und
     * MediaPlace ({clangId: value}).
     *
     * @return array{ok: bool, languages: list<string>, code: string}
     */
    private function languageState(mixed $value): array
    {
        $missing = [];
        foreach (rex_clang::getAll(true) as $clang) {
            $clangId = (int) $clang->getId();
            if (!self::hasLanguageValue($value, $clangId)) {
                $missing[] = (string) $clang->getName();
            }
        }

        return ['ok' => [] === $missing, 'languages' => $missing, 'code' => 'missing_language'];
    }

    private static function hasLanguageValue(mixed $value, int $clangId): bool
    {
        if (rex_addon::get('metainfo_lang_fields')->isAvailable() && class_exists(\MetainfoLangHelper::class)) {
            return \MetainfoLangHelper::hasTranslationForLanguage($value, $clangId);
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (!is_array($decoded)) {
                return '' !== trim($value);
            }
            $value = $decoded;
        }
        if (!is_array($value)) {
            return false;
        }
        if (array_key_exists((string) $clangId, $value) || array_key_exists($clangId, $value)) {
            return '' !== trim((string) ($value[(string) $clangId] ?? $value[$clangId] ?? ''));
        }
        foreach ($value as $entry) {
            if (is_array($entry) && (int) ($entry['clang_id'] ?? -1) === $clangId) {
                return '' !== trim((string) ($entry['value'] ?? ''));
            }
        }

        return false;
    }

    /**
     * Unmaskierter Text: die Ausgabe (a11y-check.js, Backend-Seiten) maskiert selbst.
     */
    private function defaultMessage(string $field, string $code): string
    {
        return match ($code) {
            'missing_alt' => rex_i18n::rawMsg('mform_a11y_missing_alt'),
            'missing_language' => rex_i18n::rawMsg('mform_a11y_missing_language', $field),
            'unknown_field' => rex_i18n::rawMsg('mform_a11y_unknown_field', $field),
            default => rex_i18n::rawMsg('mform_a11y_missing_field', $field),
        };
    }

    /** Globaler Schalter (Einstellungen), Standard an. */
    public static function isEnabled(): bool
    {
        return class_exists(\rex_config::class) ? (bool) \rex_config::get('mform', 'a11y_check', true) : true;
    }

    /** Standardpruefung (ALT-Text) fuer alle Medien-Felder ohne eigene Option, Standard aus. */
    public static function isDefaultEnabled(): bool
    {
        return class_exists(\rex_config::class) && (bool) \rex_config::get('mform', 'a11y_default', false);
    }

    private static function mediaplaceAvailable(): bool
    {
        return rex_addon::get('mediaplace')->isAvailable();
    }

    /**
     * Metainfo-Felder des Medienpools mit Typ-Label (gecacht).
     *
     * @return array<string, string>
     */
    private static function fieldTypes(): array
    {
        if (null !== self::$fieldTypes) {
            return self::$fieldTypes;
        }
        self::$fieldTypes = [];
        if (rex_addon::get('metainfo')->isAvailable()) {
            $rows = rex_sql::factory()->getArray(
                'SELECT f.name, t.label FROM ' . rex::getTable('metainfo_field') . ' f LEFT JOIN ' . rex::getTable('metainfo_type') . ' t ON t.id = f.type_id WHERE f.name LIKE :prefix',
                ['prefix' => 'med\\_%'],
            );
            foreach ($rows as $row) {
                self::$fieldTypes[(string) $row['name']] = (string) ($row['label'] ?? '');
            }
        }

        return self::$fieldTypes;
    }

    /** Nur für Tests: Cache der Feldtypen leeren. */
    public static function resetCache(): void
    {
        self::$fieldTypes = null;
    }
}
