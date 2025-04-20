<?php

namespace CaddyParser\CaddyConfig\Lexer;

class Token {
    public string $type;
    public string $text;
    public int    $line;

    public function __construct(string $type, string $text, int $line)
    {
        $this->type = $type;
        $this->text = $text;
        $this->line = $line;
    }
}