<?php

namespace CaddyParser\CaddyConfig\Elements;

use CaddyParser\CaddyConfig\Lexer\Lexer;

/**
 * Matcher Element
 */
class Matcher extends AbstractElement
{
    private string $type;
    private array  $params;

    public function __construct(string $type, array $params = [])
    {
        $this->type   = $type;
        $this->params = $params;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public static function parse(Lexer $lexer): self
    {
        $token = $lexer->consume(); // Ex: "@post", "/index.html", "*"
        $text = $token->text;

        // Detect matcher type
        if ($text === '*') {
            return new self('wildcard', []);
        }

        if (str_starts_with($text, '@')) {
            return new self('named', ['name' => substr($text, 1)]);
        }

        if (str_starts_with($text, '/')) {
            return new self('path', ['path' => $text]);
        }

        // Fallback: raw 
        /** @todo check if this is okey */
        return new self('raw', ['raw' => $text]);
    }

    public function toCaddyfile(int $indentLevel = 0): string
    {
        return match ($this->type) {
            'wildcard' => '*',
            'named'    => '@' . ($this->params['name'] ?? ''),
            'path'     => $this->params['path'] ?? '',
            'raw'      => $this->params['raw'] ?? '',
            default    => '',
        };
    }
}