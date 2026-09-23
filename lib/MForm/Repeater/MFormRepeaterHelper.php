<?php

/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Repeater;

use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MForm\DTO\MFormItem;
use FriendsOfRedaxo\MForm\Output\MFormOutput;
use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;

class MFormRepeaterHelper
{
    private const DISABLED_KEY = '__disabled';

    /** Versionsmarker des Speicherformats (#452): {"__v": 2, "items": [...]}. Ohne Marker = Version 1 (reine Liste). */
    public const DATA_VERSION_KEY = '__v';
    public const DATA_VERSION_LIST = 1;
    public const DATA_VERSION_ENVELOPE = 2;

    /**
     * @param array<int, MFormItem|MForm> $items
     * @return array<string, string>
     */
    public static function getRepeaterChildKeys(array $items, int|string $key): array
    {
        $next = false;
        $keys = [];

        foreach ($items as $k => $itm) {
            if ($next && $itm instanceof MForm) {
                // prepare obj array
                $keys = array_merge($keys, self::getChildKeys($itm));
                $next = false;
            } elseif ($k == $key) {
                $next = true;
            }
        }
        return $keys;
    }

    /** @return array<string, string> */
    public static function getChildKeys(MForm $mform): array
    {
        $keys = [];

        $items = $mform->getItems();
        foreach ($items as $key => $mformItem) {
            $ikey = (int) $key;
            if ($mformItem instanceof MFormItem) {
                $nameKey = self::getNameKey($mformItem);
                if ('' !== $nameKey && $mformItem->getType() === 'repeater') {
                    $keys[$nameKey] = $nameKey;
                    $nextItem = $items[$ikey + 1] ?? null;
                    if ($nextItem instanceof MForm) {
                        $keys = array_merge($keys, self::getChildKeys($nextItem));
                    }
                }
            } else {
                // $mformItem is MForm here (only other type in the array)
                $prevItem = $items[$ikey - 1] ?? null;
                if ($prevItem instanceof MFormItem && $prevItem->getType() !== 'repeater') {
                    $keys = array_merge($keys, self::getChildKeys($mformItem));
                }
            }
        }
        return $keys;
    }

    /**
     * @param array<MFormItem|MForm> $items
     * @return array<string, mixed>
     */
    public static function prepareChildMForms(array $items, string $key, string $repeaterId, string $group, string $groups, string|null $parentId): array
    {
        $next = false;
        $obj = [];

        foreach ($items as $k => $itm) {
            if ($next && $itm instanceof MForm) {
                // prepare obj array
                $obj = array_merge($obj, self::prepareChildItems($itm, $repeaterId, $group, $groups, $parentId));
                $next = false;
            } elseif ($k == $key) {
                $next = true;
            }
        }

        return $obj;
    }

    /** @return array<string, mixed> */
    public static function prepareChildItems(MForm $mform, string $repeaterId, string $group, string $groups, string|null $parentId): array
    {
        $obj = [];
        $items = $mform->getItems();
        //        dump($items);die;
        foreach ($items as $key => $mformItem) {
            $ikey = (int) $key;
            if ($mformItem instanceof MFormItem) {
                $nameKey = self::getNameKey($mformItem);
                // prepare mform items
                if ('' !== $nameKey) {
                    // dump($mformItem->getType());
                    switch ($mformItem->getType()) {
                        case 'repeater':
                            $nextItem = $items[$ikey + 1] ?? null;
                            if ($nextItem instanceof MForm) {
                                $mformItem->addAttribute('parent_id', $parentId);
                                // complete the repeater child tree
                                if (isset($mformItem->getAttributes()['open']) && $mformItem->getAttributes()['open'] === true) {
                                    $obj[$nameKey] = [self::prepareChildItems($nextItem, $repeaterId, $group, $groups, $parentId)];
                                } else {
                                    $obj[$nameKey] = [];
                                }
                            } else {
                                $obj[$nameKey] = [];
                            }
                            // no break
                        case 'close-repeater':
                            $mformItem->addAttribute('group', 'field')
                                ->addAttribute('groups', 'group.' . $nameKey)
                                ->addAttribute('parent_id', $parentId);
                            break;
                        case 'media':
                            self::addWidgetAttributes($mformItem, $repeaterId, $group, $groups, $parentId);
                            $mformItem->addAttribute('repeater_link', true);
                            $obj[$nameKey] = ('' !== $mformItem->getDefaultValue()) ? $mformItem->getDefaultValue() : '';
                            break;
                        case 'link':
                        case 'custom-link':
                            self::addWidgetAttributes($mformItem, $repeaterId, $group, $groups, $parentId);
                            $mformItem->addAttribute('repeater_link', true);
                            // Default-Value fuer Link-Widgets ist immer leer; explizite Defaults sind nicht vorgesehen.
                            $obj[$nameKey] = ['name' => '', 'id' => ''];
                            break;
                        case 'medialist':
                        case 'linklist':
                            self::addWidgetAttributes($mformItem, $repeaterId, $group, $groups, $parentId);
                            // Default-Value fuer Listen-Widgets ist immer leer; explizite Defaults sind nicht vorgesehen.
                            $obj[$nameKey] = ['list' => []];
                            break;
                        case 'textarea':
                            if (str_contains($mformItem->getClass(), 'cke5-editor')) {
                                $mformItem->addAttribute('repeater_cke', 1);
                                $mformItem->setClass($mformItem->getClass() . ' hidden');
                            }
                            // no break
                        case 'checkbox':
                            foreach ($mformItem->getOptions() as $k => $option) {
                                $mformItem->addAttribute('data-value', (string) $k);
                            }
                            // no break
                        case 'multiselect':
                        case 'select':
                        default:
                            self::addWidgetAttributes($mformItem, $repeaterId, $group, $groups, $parentId);
                            $mformItem->addAttribute('x-on:change', 'updateValues()');
                            $obj[$nameKey] = ('' !== $mformItem->getDefaultValue()) ? $mformItem->getDefaultValue() : '';
                            break;
                    }
                }
            } else {
                // $mformItem is MForm here
                $prevItem = $items[$ikey - 1] ?? null;
                if ($prevItem === null || ($prevItem instanceof MFormItem && $prevItem->getType() !== 'repeater')) {
                    $obj = array_merge($obj, self::prepareChildItems($mformItem, $repeaterId, $group, $groups, $parentId));
                    self::addWidgetAttributesByMFormObj($mformItem, $repeaterId, $group, $groups, $parentId);
                }
            }
            if ($mformItem instanceof MForm) {
                foreach ($mformItem->getItems() as $item) {
                    if ($item instanceof MFormItem && $item->getType() === 'collapse') {
                        self::addWidgetAttributes($item, $repeaterId, $group, $groups, $parentId);
                    }
                }
            }
        }

        return $obj;
    }

    private static function getNameKey(MFormItem $mformItem): string
    {
        return ((is_array($mformItem->getVarId())) ? implode('.', $mformItem->getVarId()) : $mformItem->getVarId());
    }

    private static function addWidgetAttributesByMFormObj(MForm $mform, string $repeaterId, string $group, string $groups, string|null $parentId): void
    {
        foreach ($mform->getItems() as $item) {
            if ($item instanceof MFormItem && $item->getType() === 'collapse') {
                self::addWidgetAttributes($item, $repeaterId, $group, $groups, $parentId);
            }
        }
    }

    private static function addWidgetAttributes(MFormItem $mformItem, string $repeaterId, string $group, string $groups, string|null $parentId, string|null $nameKey = null): void
    {
        if (null === $nameKey || '' === $nameKey) {
            $nameKey = self::getNameKey($mformItem);
        }
        $mformItem->addAttribute('x-model', $group . '[\'' . $nameKey . '\']')
            ->addAttribute(':id', "'".$nameKey.'-'.$repeaterId."-'+".$repeaterId."Index".((null !== $parentId && '' !== $parentId && $parentId != $repeaterId) ? "+'-".$parentId."-'+".$parentId.'Index' : ''))
            ->addAttribute('group', $group)
            ->addAttribute('groups', $groups)
            ->addAttribute('repeaterId', $repeaterId)
            ->addAttribute('parent_id', $parentId)
            ->addAttribute('item_name_key', $nameKey);

        if (count($mformItem->getToggleOptions()) > 0) {
            $toggleOptions = $mformItem->getToggleOptions();
            foreach ($toggleOptions as $key => $toggleOption) {
                if (!is_array($toggleOption)) {
                    $toggleOptions[$key] = [$toggleOption, "'" . $toggleOption . "-'+" . $repeaterId . "Index" . ((null !== $parentId && '' !== $parentId && $parentId != $repeaterId) ? "+'-'+" . $parentId . 'Index' : '')];
                }
            }
            $mformItem->setToggleOptions($toggleOptions);
        }

        //        if($nameKey == '') {
        //            dump($mformItem);
        //        }

        //        dump([$repeaterId, $parentId]);

        if (isset($mformItem->getAttributes()['data-toggle-item'])) {
            $mformItem->addAttribute(':data-toggle-item', "'" . $mformItem->getAttributes()['data-toggle-item'] . "-'+" . $repeaterId . "Index" . ((null !== $parentId && '' !== $parentId && $parentId != $repeaterId) ? "+'-'+" . $parentId . 'Index' : ''));
        }
        if (isset($mformItem->getAttributes()['data-group-collapse-id'])) {
            $mformItem->addAttribute(':data-group-collapse-id', "'" . $mformItem->getAttributes()['data-group-collapse-id'] . "-'+" . $repeaterId . "Index" . ((null !== $parentId && '' !== $parentId && $parentId != $repeaterId) ? "+'-'+" . $parentId . 'Index' : ''));
        }
    }

    /** @param array<string, mixed> $item */
    public static function isItemEnabled(array $item): bool
    {
        if (!array_key_exists(self::DISABLED_KEY, $item)) {
            return true;
        }

        $value = $item[self::DISABLED_KEY];
        if (is_bool($value)) {
            return !$value;
        }

        if (is_int($value) || is_float($value)) {
            return (int) $value !== 1;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            return !in_array($normalized, ['1', 'true', 'yes', 'on'], true);
        }

        return !$value;
    }

    /**
     * Filtert deaktivierte Items heraus.
     *
     * Nimmt bewusst auch gemischte Arrays entgegen: Wird versehentlich ein
     * Nicht-Repeater-Wert hereingereicht, fallen die Nicht-Item-Eintraege raus,
     * statt einen TypeError auszuloesen.
     *
     * @param array<int|string, mixed> $items
     * @return array<int, array<string, mixed>>
     */
    public static function filterEnabledItems(array $items): array
    {
        /** @var array<int, array<string, mixed>> $filtered */
        $filtered = array_values(array_filter($items, static function (mixed $item): bool {
            return is_array($item) && self::isItemEnabled($item);
        }));

        return $filtered;
    }

    /**
     * Decodes repeater payload and returns only enabled items.
     *
     * Use this in module output code instead of rex_var::toArray() to automatically
     * filter out disabled (offline) items and strip the internal __disabled key.
     *
     * Example:
     *   $rows = MFormRepeaterHelper::decode(1);
     *
     * @param int|string $source Either a value-slot id (e.g. 1) or a raw JSON payload
     * @return array<int, array<string, mixed>> Filtered and cleaned items
     */
    public static function decode(int|string $source): array
    {
        // Roh-Aufbereitung (Slot-Id, Entities, <br>-Fallback) teilt sich decode() mit values().
        $decoded = MFormOutputHelper::decodeRaw($source);

        if (null === $decoded) {
            return [];
        }

        $decoded = self::unwrap($decoded);

        // Nur echte Repeater-Listen weiterreichen. Punkt-Notation ("{\"1\":\"...\"}") und
        // skalare Slots sind keine Item-Listen; fuer die ist value()/values() zustaendig.
        if (!self::isRepeaterPayload($decoded)) {
            return [];
        }

        return self::prepareItemsForOutput($decoded);
    }

    /**
     * Alias auf MFormOutputHelper::values().
     *
     * Nicht-Repeater-Slots gehoeren fachlich zu MFormOutputHelper; der Alias steht hier,
     * weil decode() der uebliche Einstieg ist und der Wechsel so nicht auffaellt.
     *
     * @param int|string $source Slot-Id oder Roh-Wert
     * @return array<string, mixed>
     */
    public static function values(int|string $source): array
    {
        return MFormOutputHelper::values($source);
    }

    /**
     * Alias auf MFormOutputHelper::value().
     *
     * @param int|string  $source  Slot-Id oder Roh-Wert
     * @param string|null $field   Feldschluessel bzw. Punkt-Pfad
     * @param mixed       $default Rueckgabe, wenn nichts gefunden wurde
     */
    public static function value(int|string $source, ?string $field = null, mixed $default = null): mixed
    {
        return MFormOutputHelper::value($source, $field, $default);
    }

    /**
     * Alias auf MFormOutputHelper::isRepeater().
     *
     * @param int|string $source Slot-Id oder Roh-Wert
     */
    public static function isRepeater(int|string $source): bool
    {
        return MFormOutputHelper::isRepeater($source);
    }

    /**
     * Entfernt den Versions-Umschlag {"__v": n, "items": [...]}; Listen (Version 1) bleiben wie sie sind.
     *
     * @param array<mixed> $decoded
     * @return array<int, mixed>
     */
    public static function unwrap(array $decoded): array
    {
        if (isset($decoded[self::DATA_VERSION_KEY]) && isset($decoded['items']) && is_array($decoded['items'])) {
            return array_values($decoded['items']);
        }

        return $decoded;
    }

    /**
     * Speicherformat-Version eines Wertes: 1 = Liste, 2 = Umschlag mit __v.
     *
     * @param string|array<mixed> $source Roh-JSON oder dekodiertes Array
     */
    public static function dataVersion(string|array $source): int
    {
        $decoded = is_string($source) ? json_decode(html_entity_decode($source, ENT_QUOTES | ENT_HTML5, 'UTF-8'), true) : $source;
        if (is_array($decoded) && isset($decoded[self::DATA_VERSION_KEY]) && is_numeric($decoded[self::DATA_VERSION_KEY])) {
            return (int) $decoded[self::DATA_VERSION_KEY];
        }

        return self::DATA_VERSION_LIST;
    }

    /**
     * Kodiert Items im gewuenschten Speicherformat (Version 1 = Liste, 2 = Umschlag mit Versionsmarker).
     *
     * @param array<int, array<string, mixed>> $items
     */
    public static function encode(array $items, int $version = self::DATA_VERSION_LIST): string
    {
        $items = array_values($items);
        $payload = $version >= self::DATA_VERSION_ENVELOPE ? [self::DATA_VERSION_KEY => $version, 'items' => $items] : $items;

        return (string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Typisierter Zugriff: Items als MFormOutput mit MFormRepeaterItem-Objekten (siehe MFormOutput::items()).
     *
     * @param int|string|array<int, array<string, mixed>> $source Slot-Id, Roh-JSON oder dekodierte Items
     */
    public static function items(int|string|array $source): MFormOutput
    {
        return MFormOutput::from($source);
    }

    /**
     * Decodes repeater data from the current slice by value slot id.
     *
        * @param int $valueId Value slot id to decode repeater data from.
     * @return array<int, array<string, mixed>>
     */
    public static function decodeById(int $valueId): array
    {
        $raw = MFormOutputHelper::rawValue($valueId);

        return '' === $raw ? [] : self::decode($raw);
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    public static function prepareItemsForOutput(array $items): array
    {
        $result = [];

        foreach (self::filterEnabledItems($items) as $item) {
            unset($item[self::DISABLED_KEY]);

            foreach ($item as $key => $value) {
                if (is_array($value)) {
                    $value = self::unwrap($value);
                    if (self::isRepeaterItemList($value)) {
                        $item[$key] = self::prepareItemsForOutput($value);
                    }
                }
            }

            $result[] = $item;
        }

        return $result;
    }

    /**
     * Prueft, ob ein dekodierter Slot-Wert wirklich eine Repeater-Liste ist.
     *
     * Repeater speichern eine Liste ([0,1,2,...]) von Feld-Arrays. Punkt-Notation
     * ("{\"1\":\"Text\"}") und Multiselect-Gruppen ("{\"key\":[...]}") haben dagegen
     * String-Keys auf oberster Ebene und gehoeren nicht hierher.
     *
     * @param array<mixed> $value
     */
    private static function isRepeaterPayload(array $value): bool
    {
        if ([] === $value) {
            return true;
        }

        if (!array_is_list($value)) {
            return false;
        }

        return self::isRepeaterItemList($value);
    }

    /** @param array<mixed> $value */
    private static function isRepeaterItemList(array $value): bool
    {
        if ([] === $value) {
            return true;
        }

        foreach ($value as $item) {
            if (!is_array($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Filtert Repeater-Items nach einem Feldwert.
     *
     * @param array<int, array<string, mixed>> $items
     * @param string $field  Feldname
     * @param mixed  $value  Gesuchter Wert
     * @param bool   $strict Strikte Vergleichung (===)
     * @return array<int, array<string, mixed>>
     */
    public static function filterByField(array $items, string $field, mixed $value, bool $strict = false): array
    {
        return array_values(array_filter($items, static function (array $item) use ($field, $value, $strict): bool {
            if (!array_key_exists($field, $item)) {
                return false;
            }
            return $strict ? $item[$field] === $value : $item[$field] == $value;
        }));
    }

    /**
     * Sortiert Repeater-Items nach einem Feldwert.
     *
     * @param array<int, array<string, mixed>> $items
     * @param string $field     Feldname
     * @param string $direction 'asc' oder 'desc'
     * @return array<int, array<string, mixed>>
     */
    public static function sortByField(array $items, string $field, string $direction = 'asc'): array
    {
        $direction = strtolower($direction);

        usort($items, static function (array $a, array $b) use ($field, $direction): int {
            $va = $a[$field] ?? '';
            $vb = $b[$field] ?? '';

            $result = is_numeric($va) && is_numeric($vb)
                ? $va <=> $vb
                : strcasecmp((string) $va, (string) $vb);

            return $direction === 'desc' ? -$result : $result;
        });

        return $items;
    }

    /**
     * Gruppiert Repeater-Items nach einem Feldwert.
     *
     * @param array<int, array<string, mixed>> $items
     * @param string $field Feldname
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function groupByField(array $items, string $field): array
    {
        $groups = [];

        foreach ($items as $item) {
            $key = isset($item[$field]) ? (string) $item[$field] : 'undefined';
            $groups[$key][] = $item;
        }

        return $groups;
    }

    /**
     * Begrenzt Repeater-Items (z. B. für Pagination).
     *
     * @param array<int, array<string, mixed>> $items
     * @param int $limit  Maximale Anzahl Items
     * @param int $offset Start-Position (default: 0)
     * @return array<int, array<string, mixed>>
     */
    public static function limitItems(array $items, int $limit, int $offset = 0): array
    {
        return array_slice($items, $offset, $limit);
    }
}
