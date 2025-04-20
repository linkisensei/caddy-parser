<?php

namespace CaddyParser\CaddyConfig\Lexer;

/**
 * Processes the input Caddyfile text and converts it
 * into a sequence of tokens.
 */
class Lexer {
    private array $tokens = [];
    private int $pos = 0;

    public function __construct(string $text)
    {
        $text = preg_replace('/#.*$/m', '', $text); // Removing comments
        $pattern = '/
        ("(?:\\\\.|[^\\\\"])*")|              # double-quoted strings
        (`(?:\\\\.|[^\\\\`])*`)|              # backtick-quoted strings
        (<<([A-Za-z0-9_-]+)\n[\s\S]*?\n\4\n)| # heredocs
        (\{[^{}\s]+\})|                       # single-line placeholders like {path} or {$ENV}
        (\{|\})|                              # individual braces
        ([^\s]+)/x';
    
        preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);
        foreach ($matches[0] as $m) {
            [$token, $offset] = $m;
            $line = substr_count(substr($text, 0, $offset), "\n") + 1;

            $type = match ($token) {
                '{' => TokenType::BRACE_OPEN,
                '}' => TokenType::BRACE_CLOSE,
                default => TokenType::STRING,
            };

            $this->tokens[] = new Token($type, $token, $line);
        }
        $lastLine = substr_count($text, "\n") + 1;
        $this->tokens[] = new Token(TokenType::EOF, '', $lastLine);
    }

    public function peek(): Token
    {
        if ($this->pos < count($this->tokens)) {
            return $this->tokens[$this->pos];
        }
        return $this->tokens[count($this->tokens) - 1];
    }

    public function next(): Token
    {
        $tok = $this->peek();
        $this->pos++;
        return $tok;
    }

    public function eof(): bool
    {
        return $this->peek()->type === TokenType::EOF;
    }

    public function reset() : static {
        $this->pos = 0;
        return $this;
    }
}