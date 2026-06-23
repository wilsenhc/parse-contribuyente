<?php

declare(strict_types=1);

namespace Wilsenhc\ParseContribuyente\HtmlParser;

use function strtolower;

/**
 * Minimal CSS selector engine supporting the tiny subset used by
 * ParseContribuyente:
 *
 *   - tag               e.g. "table"
 *   - tag[attr]         e.g. "td[align]"
 *   - tag[attr=value]   e.g. td[align="center"], td[align='center'], td[align=center]
 *
 * The engine performs a depth-first search over the descendants of the
 * node it is asked to query and returns the matches in document order.
 */
final class Selector
{
    private string $tagName;
    private ?string $attribute = null;
    private ?string $value = null;

    public function __construct(string $selector)
    {
        $selector = trim($selector);

        $bracketPos = strpos($selector, '[');
        if ($bracketPos === false) {
            $this->tagName = strtolower($selector);

            return;
        }

        $this->tagName = strtolower(substr($selector, 0, $bracketPos));
        $inside = substr($selector, $bracketPos + 1);
        $closePos = strrpos($inside, ']');
        if ($closePos !== false) {
            $inside = substr($inside, 0, $closePos);
        }

        $equalsPos = strpos($inside, '=');
        if ($equalsPos === false) {
            $this->attribute = strtolower(trim($inside));

            return;
        }

        $this->attribute = strtolower(trim(substr($inside, 0, $equalsPos)));
        $value = trim(substr($inside, $equalsPos + 1));
        $length = strlen($value);
        if ($length >= 2) {
            $first = $value[0];
            $last = $value[$length - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }
        $this->value = $value;
    }

    public function find(Node $context): Collection
    {
        $matches = [];
        self::search($context, $matches);

        return new Collection($matches);
    }

    private function matches(Node $node): bool
    {
        if ($node instanceof TextNode) {
            return false;
        }

        if ($node->tag() !== $this->tagName) {
            return false;
        }

        if ($this->attribute === null) {
            return true;
        }

        if (! $node->hasAttribute($this->attribute)) {
            return false;
        }

        if ($this->value === null) {
            return true;
        }

        return $node->getAttribute($this->attribute) === $this->value;
    }

    /**
     * @param array<int, Node> $matches
     */
    private function search(Node $node, array &$matches): void
    {
        foreach ($node->children() as $child) {
            if ($this->matches($child)) {
                $matches[] = $child;
            }
            self::search($child, $matches);
        }
    }
}