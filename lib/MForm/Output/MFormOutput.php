<?php

/**
 * @author MForm
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Output;

use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;

/**
 * Fluent output helper for MForm repeater data.
 *
 * Wraps an array of repeater rows and provides a chainable API to
 * filter, sort, group, paginate and render them.
 *
 * Typical usage in module output:
 *
 *   echo MFormOutput::from('REX_VALUE[1]')
 *       ->filter('active', '1')
 *       ->sort('position')
 *       ->limit(6)
 *       ->renderGrid(3, fn($item) => '<h3>' . rex_escape($item['title']) . '</h3>');
 *
 * All terminal render*() methods return a string. Intermediate
 * methods (filter, where, sort, limit, map) return a new immutable
 * MFormOutput instance, so the original data stays untouched.
 *
 * @phpstan-type Item array<string, mixed>
 */
final class MFormOutput
{
    /** @var array<int, array<string, mixed>> */
    private array $items;

    private string $emptyOutput = '';

    /** @param array<int, array<string, mixed>> $items */
    private function __construct(array $items)
    {
        $this->items = array_values($items);
    }

    /**
     * Creates a new output from a REX_VALUE string or already decoded array.
     *
     * Strings are passed through MFormRepeaterHelper::decode() which
     * filters disabled items and unwraps nested repeaters.
     *
     * @param string|array<int, array<string, mixed>> $source
     */
    public static function from(string|array $source): self
    {
        if (is_string($source)) {
            return new self(MFormRepeaterHelper::decode($source));
        }

        return new self($source);
    }

    /** Returns an empty output instance. */
    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * Filters items by a field value.
     *
     * @param mixed $value
     */
    public function filter(string $field, mixed $value, bool $strict = false): self
    {
        return new self(MFormRepeaterHelper::filterByField($this->items, $field, $value, $strict));
    }

    /**
     * Filters items via a callback.
     *
     * @param callable(array<string, mixed>, int): bool $callback
     */
    public function where(callable $callback): self
    {
        $filtered = [];
        foreach ($this->items as $i => $item) {
            if ($callback($item, $i)) {
                $filtered[] = $item;
            }
        }

        return new self($filtered);
    }

    /** Sorts items by a field. */
    public function sort(string $field, string $direction = 'asc'): self
    {
        return new self(MFormRepeaterHelper::sortByField($this->items, $field, $direction));
    }

    /** Reverses the current item order. */
    public function reverse(): self
    {
        return new self(array_reverse($this->items));
    }

    /**
     * Limits the output to N items, optionally with offset.
     */
    public function limit(int $limit, int $offset = 0): self
    {
        return new self(MFormRepeaterHelper::limitItems($this->items, $limit, $offset));
    }

    /** Skips the first N items. */
    public function skip(int $offset): self
    {
        return new self(array_slice($this->items, $offset));
    }

    /**
     * Returns a single page from the items.
     *
     * @param int $page    1-based page number
     * @param int $perPage Items per page
     */
    public function page(int $page, int $perPage): self
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);

        return new self(array_slice($this->items, ($page - 1) * $perPage, $perPage));
    }

    /**
     * Maps each item via a callback.
     *
     * @param callable(array<string, mixed>, int): array<string, mixed> $callback
     */
    public function map(callable $callback): self
    {
        $mapped = [];
        foreach ($this->items as $i => $item) {
            $mapped[] = $callback($item, $i);
        }

        return new self($mapped);
    }

    /**
     * Groups items by a field value (terminal).
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function group(string $field): array
    {
        return MFormRepeaterHelper::groupByField($this->items, $field);
    }

    /**
     * Extracts a single column from the items (terminal).
     *
     * @return array<int, mixed>
     */
    public function pluck(string $field): array
    {
        return array_column($this->items, $field);
    }

    /**
     * Returns the first item or null.
     *
     * @return array<string, mixed>|null
     */
    public function first(): ?array
    {
        return $this->items[0] ?? null;
    }

    /**
     * Returns the last item or null.
     *
     * @return array<string, mixed>|null
     */
    public function last(): ?array
    {
        return [] === $this->items ? null : $this->items[array_key_last($this->items)];
    }

    /**
     * Returns all items as a plain array (terminal).
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function isEmpty(): bool
    {
        return [] === $this->items;
    }

    /**
     * Sets the fallback output rendered when the collection is empty.
     *
     * Accepts a plain string or a callback `fn(): string`.
     *
     * @param string|callable(): string $fallback
     */
    public function whenEmpty(string|callable $fallback): self
    {
        $clone = clone $this;
        $clone->emptyOutput = is_callable($fallback) ? (string) $fallback() : $fallback;

        return $clone;
    }

    /**
     * Renders each item using a template callback and concatenates the result.
     *
     * @param callable(array<string, mixed>, int): string $template
     */
    public function render(callable $template): string
    {
        if ([] === $this->items) {
            return $this->emptyOutput;
        }

        $out = '';
        foreach ($this->items as $i => $item) {
            $out .= (string) $template($item, $i);
        }

        return $out;
    }

    /**
     * Renders items as a Bootstrap-style grid.
     *
     * Wraps every $cols items in a row container.
     *
     * @param callable(array<string, mixed>, int): string $template
     */
    public function renderGrid(
        int $cols,
        callable $template,
        string $rowClass = 'row',
        string $colClass = '',
        string $rowTag = 'div',
        string $colTag = 'div',
    ): string {
        if ([] === $this->items) {
            return $this->emptyOutput;
        }

        $cols = max(1, $cols);
        // Bootstrap default if no col class given
        if ('' === $colClass) {
            $bsCol = (int) floor(12 / $cols);
            $colClass = 'col-md-' . max(1, min(12, $bsCol));
        }

        $out = '';
        $chunks = array_chunk($this->items, $cols);
        $offset = 0;
        foreach ($chunks as $chunk) {
            $out .= '<' . $rowTag . ' class="' . rex_escape($rowClass) . '">';
            foreach ($chunk as $j => $item) {
                $out .= '<' . $colTag . ' class="' . rex_escape($colClass) . '">';
                $out .= (string) $template($item, $offset + $j);
                $out .= '</' . $colTag . '>';
            }
            $out .= '</' . $rowTag . '>';
            $offset += count($chunk);
        }

        return $out;
    }

    /**
     * Renders items as a `<ul>` / `<ol>` list.
     *
     * @param callable(array<string, mixed>, int): string $template
     * @param array<string, string> $listAttrs Attributes for the list tag (e.g. ['class' => 'nav'])
     */
    public function renderList(
        callable $template,
        string $listTag = 'ul',
        string $itemTag = 'li',
        array $listAttrs = [],
        string $itemClass = '',
    ): string {
        if ([] === $this->items) {
            return $this->emptyOutput;
        }

        $attrs = '';
        foreach ($listAttrs as $name => $val) {
            $attrs .= ' ' . $name . '="' . rex_escape($val) . '"';
        }

        $out = '<' . $listTag . $attrs . '>';
        foreach ($this->items as $i => $item) {
            $itemAttr = '' !== $itemClass ? ' class="' . rex_escape($itemClass) . '"' : '';
            $out .= '<' . $itemTag . $itemAttr . '>';
            $out .= (string) $template($item, $i);
            $out .= '</' . $itemTag . '>';
        }
        $out .= '</' . $listTag . '>';

        return $out;
    }

    /**
     * Splits items into N chunks and renders each chunk via its own template.
     *
     * @param callable(array<int, array<string, mixed>>, int): string $template
     */
    public function renderChunks(int $size, callable $template): string
    {
        if ([] === $this->items) {
            return $this->emptyOutput;
        }

        $size = max(1, $size);
        $out = '';
        foreach (array_chunk($this->items, $size) as $i => $chunk) {
            $out .= (string) $template($chunk, $i);
        }

        return $out;
    }

    /**
     * Renders items via a REDAXO fragment.
     *
     * The fragment receives `$items` and `$data`; an optional fragments-path
     * can be added via `rex_fragment::addDirectory()` before calling.
     *
     * @param array<string, mixed> $data Additional vars passed to the fragment as `$data`
     */
    public function renderFragment(string $fragmentName, array $data = []): string
    {
        if ([] === $this->items) {
            return $this->emptyOutput;
        }

        $fragment = new \rex_fragment();
        $fragment->setVar('items', $this->items, false);
        $fragment->setVar('data', $data, false);

        return $fragment->parse($fragmentName);
    }
}
