<?php

declare(strict_types=1);

namespace Wilsenhc\ParseContribuyente\HtmlParser;

/**
 * A single token produced by the Tokenizer.
 */
final class Token
{
    public const TAG_OPEN = 'tag_open';
    public const TAG_CLOSE = 'tag_close';
    public const TEXT = 'text';

    private string $type;
    private string $name;
    /** @var array<string, string> */
    private array $attributes;
    private string $text;
    private bool $selfClosing;

    /**
     * @param array<string, string> $attributes
     */
    public function __construct(
        string $type,
        string $name,
        array $attributes,
        string $text,
        bool $selfClosing = false,
    ) {
        $this->type = $type;
        $this->name = $name;
        $this->attributes = $attributes;
        $this->text = $text;
        $this->selfClosing = $selfClosing;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return $this->attributes;
    }

    public function text(): string
    {
        return $this->text;
    }

    public function isSelfClosing(): bool
    {
        return $this->selfClosing;
    }
}