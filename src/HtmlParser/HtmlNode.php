<?php

declare(strict_types=1);

namespace Wilsenhc\ParseContribuyente\HtmlParser;

use function count;
use function htmlspecialchars;
use function strtolower;

/**
 * Represents an HTML element node.
 *
 * The behaviour of {@see innerText()} and {@see text()} mirrors the
 * respective methods of the original HtmlNode:
 *  - text() returns the concatenation of the text of the node's direct
 *    TextNode children (the equivalent of text(false)).
 *  - innerText() returns strip_tags(innerHtml()), which is equivalent to
 *    concatenating the text of every descendant TextNode.
 */
final class HtmlNode implements Node
{
    /**
     * Elements that never have a closing tag nor children.
     */
    private const VOID_TAGS = [
        'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input',
        'link', 'meta', 'param', 'source', 'track', 'wbr',
    ];
    private string $tag;
    /** @var array<string, string> */
    private array $attributes;
    /** @var array<Node> */
    private array $children = [];
    private ?Node $parent = null;

    /**
     * @param array<string, string> $attributes
     */
    public function __construct(string $tag, array $attributes = [])
    {
        $this->tag = strtolower($tag);
        $this->attributes = $attributes;
    }

    public function tag(): string
    {
        return $this->tag;
    }

    /**
     * Serializes the node back to HTML, including the opening tag,
     * its children and the closing tag (mirrors the original
     * HtmlNode::outerHtml() so that callers can re-parse the result).
     */
    public function outerHtml(): string
    {
        $html = '<' . $this->tag;
        foreach ($this->attributes as $name => $value) {
            $html .= ' ' . $name . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
        }

        if (in_array($this->tag, self::VOID_TAGS, true)) {
            return $html . '>';
        }

        return $html . '>' . $this->innerHtml() . '</' . $this->tag . '>';
    }

    /**
     * The HTML markup of the node's children.
     */
    public function innerHtml(): string
    {
        $html = '';
        foreach ($this->children as $child) {
            $html .= $child->outerHtml();
        }

        return $html;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->outerHtml();
    }

    /**
     * Returns the attribute value, or null when the attribute is absent.
     */
    public function getAttribute(string $name): ?string
    {
        return $this->attributes[strtolower($name)] ?? null;
    }

    public function hasAttribute(string $name): bool
    {
        return isset($this->attributes[strtolower($name)]);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return $this->attributes;
    }

    public function addChild(Node $child): void
    {
        $child->setParent($this);
        $this->children[] = $child;
    }

    public function firstChild(): ?Node
    {
        return $this->children[0] ?? null;
    }

    public function children(): array
    {
        return $this->children;
    }

    public function text(): string
    {
        $text = '';
        foreach ($this->children as $child) {
            if ($child instanceof TextNode) {
                $text .= $child->text();
            }
        }

        return $text;
    }

    public function innerText(): string
    {
        return self::collectInnerText($this);
    }

    public function find(string $selector): Collection
    {
        return (new Selector($selector))->find($this);
    }

    public function getParent(): ?Node
    {
        return $this->parent;
    }

    public function setParent(?Node $parent): void
    {
        $this->parent = $parent;
    }

    public function isTextNode(): bool
    {
        return false;
    }

    public function countChildren(): int
    {
        return count($this->children);
    }

    private static function collectInnerText(Node $node): string
    {
        if ($node instanceof TextNode) {
            return $node->text();
        }

        $text = '';
        foreach ($node->children() as $child) {
            $text .= self::collectInnerText($child);
        }

        return $text;
    }
}