<?php

namespace CaddyParser\CaddyConfig\Elements;

use CaddyParser\CaddyConfig\Lexer\Lexer;

/**
 * Argument Element
 */
class Argument extends AbstractElement
{
    private string $text;
    private bool   $quoted;

    public function __construct(string $text, bool $quoted = false)
    {
        $this->text   = $text;
        $this->quoted = $quoted;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function isQuoted(): bool
    {
        return $this->quoted;
    }

    public static function parse(Lexer $lexer): self
    {
        $token = $lexer->consume();
        $quoted = preg_match('/^".*"$|^`.*`$/', $token->text) === 1;
        $text = trim($token->text, '"`');
        return new self($text, $quoted);
    }

    public function toCaddyfile(int $indentLevel = 0): string
    {
        if ($this->quoted) {
            // Escaping double quotes and backslashes inside content
            return '"' . addcslashes($this->text, "\\\"") . '"';
        }
        return $this->text;
    }
}