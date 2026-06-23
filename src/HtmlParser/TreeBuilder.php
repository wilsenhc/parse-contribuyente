<?php

declare(strict_types=1);

namespace Wilsenhc\ParseContribuyente\HtmlParser;

use function in_array;
use function strtolower;

/**
 * Consumes a flat stream of tokens from the Tokenizer and assembles a
 * node tree.
 *
 * The builder handles self-closing/void tags and supports minimal
 * implicit closing of the table-related optional-end-tag elements
 * (td, th, tr, thead, tbody, tfoot), which is enough to parse the
 * SENIAT HTML responses that the parser needs to handle.
 */
final class TreeBuilder
{
    /**
     * Elements whose end tag is optional and that close the previously
     * open sibling of the same name when a new one starts.
     */
    private const OPTIONAL_CLOSE_SIBLINGS = ['td', 'th', 'tr', 'thead', 'tbody', 'tfoot', 'option'];

    /**
     * When one of these tags starts, any open "td" or "th" is closed,
     * and when "tr" starts they are also closed.
     */
    private const TD_TH = ['td', 'th'];

    private HtmlNode $root;
    /** @var array<int, HtmlNode> */
    private array $stack;

    public function __construct()
    {
        $this->root = new HtmlNode('root');
        $this->stack = [$this->root];
    }

    /**
     * @param array<int, Token> $tokens
     */
    public function build(array $tokens): HtmlNode
    {
        foreach ($tokens as $token) {
            $this->handle($token);
        }

        return $this->root;
    }

    private function handle(Token $token): void
    {
        if ($token->type() === Token::TEXT) {
            $this->current()->addChild(new TextNode($token->text()));

            return;
        }

        if ($token->type() === Token::TAG_OPEN) {
            $this->openTag($token);

            return;
        }

        if ($token->type() === Token::TAG_CLOSE) {
            $this->closeTag($token->name());
        }
    }

    private function openTag(Token $token): void
    {
        $name = $token->name();

        $this->autoCloseOptional($name);

        $node = new HtmlNode($name, $token->attributes());

        if (! $token->isSelfClosing()) {
            $this->current()->addChild($node);
            $this->stack[] = $node;

            return;
        }

        $this->current()->addChild($node);
    }

    /**
     * Closes optional-end-tag siblings (and the td/th in a new tr)
     * before opening a new element, mirroring the HTML5 tree building
     * steps just enough for the SENIAT markup.
     */
    private function autoCloseOptional(string $name): void
    {
        if (in_array($name, self::TD_TH, true)) {
            $this->closeIfOpen(self::TD_TH);
        }

        if ($name === 'tr') {
            $this->closeIfOpen(self::TD_TH);
        }

        if (in_array($name, self::OPTIONAL_CLOSE_SIBLINGS, true)) {
            $this->closeIfOpen([$name]);
        }
    }

    /**
     * @param array<int, string> $names
     */
    private function closeIfOpen(array $names): void
    {
        while (count($this->stack) > 1) {
            $top = $this->current();
            if (! $top instanceof HtmlNode) {
                break;
            }

            if (in_array($top->tag(), $names, true)) {
                array_pop($this->stack);

                continue;
            }

            break;
        }
    }

    private function closeTag(string $name): void
    {
        $name = strtolower($name);

        for ($i = count($this->stack) - 1; $i >= 1; $i--) {
            $node = $this->stack[$i];
            if (! $node instanceof HtmlNode) {
                continue;
            }

            if ($node->tag() === $name) {
                $this->stack = array_slice($this->stack, 0, $i);

                return;
            }
        }

        // Unmatched closing tag: ignore it, matching the behaviour of
        // the original parser for the stray </table> in the fixtures.
    }

    private function current(): HtmlNode
    {
        return $this->stack[count($this->stack) - 1];
    }
}