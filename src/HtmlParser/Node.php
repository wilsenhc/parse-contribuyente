<?php

declare(strict_types=1);

namespace Wilsenhc\ParseContribuyente\HtmlParser;

/**
 * @property-read string $outerhtml
 * @property-read string $innerhtml
 * @property-read string $innerText
 * @property-read string $text
 * @property-read string $tag
 */
interface Node
{
    /**
     * The lowercased tag name of the node ("text" for text nodes).
     */
    public function tag(): string;

    /**
     * Returns the text content of this node.
     *
     * For a TextNode this is the whitespace-collapsed raw text.
     * For an HtmlNode this is the concatenation of the text of its
     * direct TextNode children (mirrors the original text(false)).
     */
    public function text(): string;

    /**
     * Returns the inner text of this node, i.e. the concatenation of
     * the text of every descendant TextNode. Equivalent to
     * strip_tags(innerHtml()) but implemented directly.
     */
    public function innerText(): string;

    /**
     * The HTML markup of the node's children.
     */
    public function innerHtml(): string;

    /**
     * The HTML markup of the node, including its own opening and closing
     * tags.
     */
    public function outerHtml(): string;

    /**
     * Serializes the node back to HTML.
     */
    public function __toString(): string;

    /**
     * The first child node, or null when the node has no children.
     */
    public function firstChild(): ?Node;

    /**
     * All child nodes.
     *
     * @return array<Node>
     */
    public function children(): array;

    /**
     * Attribute value, or null if absent.
     */
    public function getAttribute(string $name): ?string;

    /**
     * Whether the node has the given attribute.
     */
    public function hasAttribute(string $name): bool;

    /**
     * Find descendant nodes matching the given selector.
     */
    public function find(string $selector): Collection;

    /**
     * The parent node, or null for the root.
     */
    public function getParent(): ?Node;

    public function setParent(?Node $parent): void;
}