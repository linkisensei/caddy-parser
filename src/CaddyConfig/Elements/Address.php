<?php

namespace CaddyParser\CaddyConfig\Elements;

use CaddyParser\CaddyConfig\Lexer\Lexer;

/**
 * Address Element
 */
class Address extends AbstractElement
{
    private string $scheme;
    private string $host;
    private ?int   $port;

    public function __construct(string $scheme, string $host, ?int $port = null)
    {
        $this->scheme = $scheme;
        $this->host   = $host;
        $this->port   = $port;
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public static function parse(Lexer $lexer): self
    {
        $tok = $lexer->next()->text;
        $scheme = 'https';
        if (strpos($tok, '://') !== false) {
            [$scheme, $rest] = explode('://', $tok, 2);
        } else {
            $rest = $tok;
        }

        $port = null;
        if (strpos($rest, ':') !== false) {
            [$host, $p] = explode(':', $rest, 2);
            if (is_numeric($p)) $port = (int)$p;
        } else {
            $host = $rest;
        }

        return new self($scheme, $host, $port);
    }

    public function toCaddyfile(int $indentLevel = 0): string
    {
        $out = $this->host;

        if ($this->port !== null) {
            $out .= ':' . $this->port;
        }

        if ($this->scheme !== 'https') {
            $out = "{$this->scheme}://{$out}";
        }

        return $out;
    }
}