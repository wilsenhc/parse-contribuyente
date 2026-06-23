<?php

declare(strict_types=1);

namespace Wilsenhc\ParseContribuyente\HtmlParser;

/**
 * Single-pass HTML tokenizer producing a flat list of tokens that the
 * TreeBuilder consumes.
 *
 * The tokenizer is intentionally lenient: it tolerates unquoted
 * attribute values, attributes without values, malformed nesting and
 * stray closing tags, all of which appear in the SENIAT HTML responses.
 */
final class Tokenizer
{
    private const VOID_TAGS = [
        'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input',
        'link', 'meta', 'param', 'source', 'track', 'wbr',
    ];

    private const SELF_CLOSING_TAGS = self::VOID_TAGS;

    private string $html;
    private int $length;
    private int $position = 0;

    public function __construct(string $html)
    {
        $this->html = $html;
        $this->length = strlen($html);
    }

    /**
     * @return array<Token>
     */
    public function tokenize(): array
    {
        $tokens = [];
        $textBuffer = '';

        while ($this->position < $this->length) {
            $char = $this->html[$this->position];

            if ($char === '<') {
                if ($textBuffer !== '') {
                    $tokens[] = $this->textToken($textBuffer);
                    $textBuffer = '';
                }

                $tag = $this->readTag();
                if ($tag !== null) {
                    $tokens[] = $tag;
                }

                continue;
            }

            $textBuffer .= $char;
            $this->position++;
        }

        if ($textBuffer !== '') {
            $tokens[] = $this->textToken($textBuffer);
        }

        return $tokens;
    }

    private function textToken(string $text): Token
    {
        return new Token(Token::TEXT, '', [], $text);
    }

    private function readTag(): ?Token
    {
        $remaining = $this->length - $this->position;

        if ($remaining >= 4 && substr($this->html, $this->position, 4) === '<!--') {
            $this->position += 4;
            $this->skipComment();

            return null;
        }

        if ($remaining >= 9 && strtolower(substr($this->html, $this->position, 9)) === '<!doctype') {
            $this->skipUntil('>');

            return null;
        }

        $close = $this->html[$this->position + 1] ?? '';

        if ($close === '/') {
            return $this->readClosingTag();
        }

        return $this->readOpeningTag();
    }

    private function readClosingTag(): ?Token
    {
        $this->position += 2;
        $name = $this->readName();
        $this->skipUntil('>');

        if ($name === '') {
            return null;
        }

        return new Token(Token::TAG_CLOSE, strtolower($name), [], '');
    }

    private function readOpeningTag(): ?Token
    {
        $this->position++;
        $name = $this->readName();

        if ($name === '') {
            return null;
        }

        /** @var array<string, string> $attributes */
        $attributes = [];
        $selfClosing = $this->readAttributes($attributes);

        $lowerName = strtolower($name);
        if (! $selfClosing) {
            $selfClosing = in_array($lowerName, self::SELF_CLOSING_TAGS, true);
        }

        return new Token(
            Token::TAG_OPEN,
            $lowerName,
            $attributes,
            '',
            $selfClosing,
        );
    }

    /**
     * @param array<string, string> $attributes
     */
    private function readAttributes(array &$attributes): bool
    {
        while ($this->position < $this->length) {
            $this->skipWhitespace();

            if ($this->position >= $this->length) {
                return false;
            }

            $char = $this->html[$this->position];

            if ($char === '>') {
                $this->position++;

                return false;
            }

            if ($char === '/' && ($this->html[$this->position + 1] ?? '') === '>') {
                $this->position += 2;

                return true;
            }

            $attrName = $this->readName();
            if ($attrName === '') {
                $this->position++;

                continue;
            }

            $this->skipWhitespace();
            $next = $this->html[$this->position] ?? '';

            if ($next !== '=') {
                $attributes[strtolower($attrName)] = strtolower($attrName);

                continue;
            }

            $this->position++;
            $this->skipWhitespace();

            $value = $this->readAttributeValue();
            $attributes[strtolower($attrName)] = $value;
        }

        return false;
    }

    private function readAttributeValue(): string
    {
        if ($this->position >= $this->length) {
            return '';
        }

        $quote = $this->html[$this->position];

        if ($quote === '"' || $quote === "'") {
            $this->position++;
            $start = $this->position;
            $end = strpos($this->html, $quote, $start);

            if ($end === false) {
                $this->position = $this->length;

                return substr($this->html, $start);
            }

            $value = substr($this->html, $start, $end - $start);
            $this->position = $end + 1;

            return $value;
        }

        $start = $this->position;
        while ($this->position < $this->length) {
            $char = $this->html[$this->position];
            if ($char === '>' || $char === ' ' || $char === "\t" || $char === "\n" || $char === "\r") {
                break;
            }
            $this->position++;
        }

        return substr($this->html, $start, $this->position - $start);
    }

    private function readName(): string
    {
        $start = $this->position;

        while ($this->position < $this->length) {
            $char = $this->html[$this->position];
            $isNameChar = ctype_alnum($char)
                || $char === '-'
                || $char === '_'
                || $char === ':';

            if (! $isNameChar) {
                break;
            }
            $this->position++;
        }

        return substr($this->html, $start, $this->position - $start);
    }

    private function skipWhitespace(): void
    {
        while ($this->position < $this->length) {
            $char = $this->html[$this->position];
            if ($char !== ' ' && $char !== "\t" && $char !== "\n" && $char !== "\r") {
                return;
            }
            $this->position++;
        }
    }

    private function skipUntil(string $terminator): void
    {
        $pos = strpos($this->html, $terminator, $this->position);

        if ($pos === false) {
            $this->position = $this->length;

            return;
        }

        $this->position = $pos + 1;
    }

    private function skipComment(): void
    {
        $marker = '-->';
        $pos = strpos($this->html, $marker, $this->position);

        if ($pos === false) {
            $this->position = $this->length;

            return;
        }

        $this->position = $pos + strlen($marker);
    }
}