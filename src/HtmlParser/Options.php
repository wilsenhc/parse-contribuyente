<?php

declare(strict_types=1);

namespace Wilsenhc\ParseContribuyente\HtmlParser;

/**
 * Minimal options bag kept for API parity with the former Options
 * object. None of the options affect the parsing behaviour of the
 * custom parser, they are only stored.
 */
final class Options
{
    private bool $removeStyles = false;
    private bool $removeScripts = false;

    public function setRemoveStyles(bool $remove): self
    {
        $this->removeStyles = $remove;

        return $this;
    }

    public function getRemoveStyles(): bool
    {
        return $this->removeStyles;
    }

    public function setRemoveScripts(bool $remove): self
    {
        $this->removeScripts = $remove;

        return $this;
    }

    public function getRemoveScripts(): bool
    {
        return $this->removeScripts;
    }
}