<?php

declare(strict_types=1);

namespace Wilsenhc\ParseContribuyente\HtmlParser;

use function count;
use function mb_ereg_replace;
use function str_replace;

/**
 * Represents a piece of text in the DOM tree.
 *
 * Whitespace handling matches the behaviour of the original TextNode:
 * runs of whitespace are collapsed to a single space, while HTML
 * entities (such as &nbsp;) are kept verbatim and never decoded. The
 * numeric line-break entity &#10; is restored to a newline AFTER the
 * whitespace collapse, just like the original implementation.
 */
final class TextNode implements Node
{
    private string $text;
    private ?Node $parent = null;

    public function __construct(string $rawText)
    {
        $collapsed = mb_ereg_replace('\s+', ' ', $rawText);
        if ($collapsed === false) {
            $collapsed = preg_replace('/\s+/u', ' ', $rawText) ?? $rawText;
        }
        $collapsed = str_replace('&#10;', "\n", $collapsed);

        $this->text = $collapsed;
    }

    public function tag(): string
    {
        return 'text';
    }

    public function text(): string
    {
        return $this->text;
    }

    public function innerText(): string
    {
        return $this->text;
    }

    /**
     * Text nodes have no markup, so their HTML representation is the
     * (whitespace-collapsed) text itself.
     */
    public function innerHtml(): string
    {
        return $this->text;
    }

    public function outerHtml(): string
    {
        return $this->text;
    }

    public function __toString(): string
    {
        return $this->text;
    }

    public function firstChild(): ?Node
    {
        return null;
    }

    public function children(): array
    {
        return [];
    }

    public function getAttribute(string $name): ?string
    {
        return null;
    }

    public function hasAttribute(string $name): bool
    {
        return false;
    }

    public function find(string $selector): Collection
    {
        return new Collection();
    }

    public function getParent(): ?Node
    {
        return $this->parent;
    }

    public function setParent(?Node $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * The number of children of this text node.
     */
    public function countChildren(): int
    {
        return 0;
    }

    /**
     * Whether this node is a text node.
     */
    public function isTextNode(): bool
    {
        return true;
    }
}