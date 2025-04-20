<?php

namespace CaddyParser\CaddyConfig\Blocks;

use CaddyParser\CaddyConfig\Elements\Directive;
use CaddyParser\CaddyConfig\Lexer\Lexer;
use CaddyParser\CaddyConfig\Lexer\TokenType;
use CaddyParser\CaddyConfig\Serialization\CaddyfileSerializableInterface;
use CaddyParser\CaddyConfig\Serialization\Traits\CaddyfileIndentTrait;

/**
 * Global Options Block
 */
class GlobalOptionsBlock implements BlockInterface, CaddyfileSerializableInterface
{
    use CaddyfileIndentTrait;

    /** @var Directive[] */
    private array $directives = [];

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
        // Lexer already consumed '{'
        $block = new self();
        while (!$lexer->eof() && $lexer->peek()->type !== TokenType::BRACE_CLOSE) {
            $block->addDirective(Directive::parse($lexer));
        }
        $lexer->next(); // consuming the next '}'
        return $block;
    }

    public function toCaddyfile(int $indentLevel = 0): string
    {
        if (empty($this->directives)) {
            return '{}';
        }

        $lines = ['{'];
        foreach ($this->directives as $dir) {
            $lines[] = $this->indent($dir->toCaddyfile($indentLevel + 1), $indentLevel + 1);
        }
        $lines[] = str_repeat('    ', $indentLevel) . '}';
        return implode("\n", $lines);
    }

}