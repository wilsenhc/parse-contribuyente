<?php

declare(strict_types=1);

namespace Wilsenhc\ParseContribuyente\HtmlParser;

/**
 * Entry point of the custom HTML parser.
 *
 * Usage mirrors the small subset of the former DOM API
 * used by ParseContribuyente:
 *
 *   $dom = new Dom();
 *   $dom->setOptions((new Options())->setRemoveStyles(true)...);
 *   $dom->loadStr($body);
 *   $dom->find('table')->count();
 *   $dom->find('table')[1];
 */
final class Dom
{
    private Options $options;
    private HtmlNode $root;

    public function __construct()
    {
        $this->options = new Options();
        $this->root = new HtmlNode('root');
    }

    public function setOptions(Options $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions(): Options
    {
        return $this->options;
    }

    public function loadStr(string $html): self
    {
        $tokens = (new Tokenizer($html))->tokenize();
        $this->root = (new TreeBuilder())->build($tokens);

        return $this;
    }

    public function find(string $selector): Collection
    {
        return (new Selector($selector))->find($this->root);
    }

    /**
     * The root node, useful for advanced traversal.
     */
    public function getRoot(): HtmlNode
    {
        return $this->root;
    }
}