<?php

namespace CaddyParser\CaddyConfig\Blocks;

use CaddyParser\CaddyConfig\Lexer\Lexer;

interface BlockInterface
{
    /**
     * Consumes the entire block (including the opening '{' and closing '}')
     * and returns the instance of the respective block.
     *
     * @param Lexer $lexer positioned at the token that opened the block
     * @param mixed ...$args additional arguments (e.g., address for SiteBlock)
     * @return self
     */
    public static function parse(Lexer $lexer, ...$args): self;
}
