<?php

namespace CaddyParser\CaddyConfig\Lexer;

abstract class TokenType {
    const STRING      = 'STRING';
    const BRACE_OPEN  = 'BRACE_OPEN';
    const BRACE_CLOSE = 'BRACE_CLOSE';
    const EOF         = 'EOF';
}