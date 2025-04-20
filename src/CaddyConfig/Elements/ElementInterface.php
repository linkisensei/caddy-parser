<?php

namespace CaddyParser\CaddyConfig\Elements;

use CaddyParser\CaddyConfig\Lexer\Lexer;

interface ElementInterface
{
    public static function parse(Lexer $lexer): self;
}