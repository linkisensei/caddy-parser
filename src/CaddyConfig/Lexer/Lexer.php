<?php

namespace CaddyParser\CaddyConfig\Lexer;

/**
 * Processes the input Caddyfile text and converts it
 * into a sequence of tokens.
 */
class Lexer implements \Iterator
{
    private array $tokens = [];
    private int $current = 0;

    public function __construct(string $text)
    {
        // Removing comments
        $text = preg_replace('/#.*$/m', '', $text);

        // Tokenization pattern
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

            // Determine token type
            $type = match ($token) {
                '{' => TokenType::BRACE_OPEN,
                '}' => TokenType::BRACE_CLOSE,
                default => TokenType::STRING,
            };

            $this->tokens[] = new Token($type, $token, $line);
        }

        // Append EOF token
        $lastLine = substr_count($text, "\n") + 1;
        $this->tokens[] = new Token(TokenType::EOF, '', $lastLine);
    }

    /**
     * Peek at the current token without advancing.
     */
    public function peek(): Token
    {
        return $this->tokens[min($this->current, count($this->tokens) - 1)];
    }

    public function current(): Token
    {
        return $this->peek();
    }

    public function key(): int
    {
        return $this->current;
    }

    public function next(): void
    {
        if (!$this->eof()) {
            $this->current++;
        }
    }

    public function rewind(): void
    {
        $this->current = 0;
    }

    public function valid(): bool
    {
        return $this->peek()->type !== TokenType::EOF;
    }

    /**
     * Check if we've reached the end.
     */
    public function eof(): bool
    {
        return $this->peek()->type === TokenType::EOF;
    }

    /**
     * Advances to the next() and returns its value
     *
     * @return Token
     */
    public function consume(): Token
    {
        $token = $this->current();
        $this->next();
        return $token;
    }
}
