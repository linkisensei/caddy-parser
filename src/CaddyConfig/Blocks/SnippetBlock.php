<?php

namespace CaddyParser\CaddyConfig\Blocks;

use CaddyParser\CaddyConfig\Elements\Directive;
use CaddyParser\CaddyConfig\Lexer\Lexer;
use CaddyParser\CaddyConfig\Lexer\TokenType;

use CaddyParser\CaddyConfig\Serialization\CaddyfileSerializableInterface;
use CaddyParser\CaddyConfig\Serialization\Traits\CaddyfileIndentTrait;

/**
 * Snippet Block
 */
class SnippetBlock implements BlockInterface, CaddyfileSerializableInterface
{
    use CaddyfileIndentTrait;

    private string $name;
    /** @var Directive[] */
    private array $directives = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addDirective(Directive $dir): void
    {
        $this->directives[] = $dir;
    }

    /** @return Directive[] */
    public function getDirectives(): array
    {
        return $this->directives;
    }

    public static function parse(Lexer $lexer, ...$args): self
    {
        // Current token is "(nome)"
        $m = [];
        preg_match('/^\(([^)]+)\)$/', $lexer->next()->text, $m);
        $name = $m[1];
        $lexer->next(); // consuming '{'
        $block = new self($name);

        while (!$lexer->eof() && $lexer->peek()->type !== TokenType::BRACE_CLOSE) {
            $block->addDirective(Directive::parse($lexer));
        }
        $lexer->next(); // consuming '}'
        return $block;
    }

    public function toCaddyfile(int $indentLevel = 0): string
    {
        $lines = [$this->indent("({$this->name}) {", $indentLevel)];

        foreach ($this->directives as $dir) {
            $lines[] = $dir->toCaddyfile($indentLevel + 1);
        }

        $lines[] = $this->indent("}", $indentLevel);

        return implode("\n", $lines);
    }
}