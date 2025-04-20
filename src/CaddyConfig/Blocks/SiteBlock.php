<?php

namespace CaddyParser\CaddyConfig\Blocks;

use CaddyParser\CaddyConfig\Elements\Address;
use CaddyParser\CaddyConfig\Elements\Directive;
use CaddyParser\CaddyConfig\Lexer\Lexer;
use CaddyParser\CaddyConfig\Lexer\TokenType;

use CaddyParser\CaddyConfig\Serialization\CaddyfileSerializableInterface;
use CaddyParser\CaddyConfig\Serialization\Traits\CaddyfileIndentTrait;

/**
 * Site Blokc
 */
class SiteBlock implements BlockInterface, CaddyfileSerializableInterface
{
    use CaddyfileIndentTrait;

    /** @var Address[] */
    private array $addresses = [];
    /** @var Directive[] */
    private array $directives = [];

    public function __construct(array $addresses = [])
    {
        $this->addresses = $addresses;
    }

    public function addAddress(Address $addr): void
    {
        $this->addresses[] = $addr;
    }

    /** @return Address[] */
    public function getAddresses(): array
    {
        return $this->addresses;
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
        // the first argument in $args is always the array of addresses
        $addresses = $args[0] ?? []; /** @todo Raise some Exception in here */

        // skipping the opening brace '{'
        $lexer->next();

        $block = new self($addresses);

        // ...as long as we haven't reached the '}'
        while (!$lexer->eof() && $lexer->peek()->type !== TokenType::BRACE_CLOSE) {
            // Directive::parse() consumes until the end of the line or an inner block
            $block->addDirective(Directive::parse($lexer));
        }

        // skipping the closing brace '}'
        $lexer->next();

        return $block;
    }

    public function toCaddyfile(int $indentLevel = 0): string
    {
        $addressLine = implode(" ", array_map(fn(Address $a) => $a->toCaddyfile(0), $this->addresses));
        $lines = [$this->indent($addressLine . " {", $indentLevel)];

        foreach ($this->directives as $dir) {
            $lines[] = $dir->toCaddyfile($indentLevel + 1);
        }

        $lines[] = $this->indent("}", $indentLevel);

        return implode("\n", $lines);
    }
}