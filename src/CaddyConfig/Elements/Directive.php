<?php

namespace CaddyParser\CaddyConfig\Elements;

use CaddyParser\CaddyConfig\Lexer\Lexer;
use CaddyParser\CaddyConfig\Lexer\TokenType;

/**
 * Directive Element
 */
class Directive extends AbstractElement
{
    private string $name;
    /** @var Argument[] */
    private array $arguments = [];
    /** @var Matcher[] */
    private array $matchers = [];
    /** @var Directive[] */
    private array $subdirectives = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addArgument(Argument $arg): void
    {
        $this->arguments[] = $arg;
    }

    /** @return Argument[] */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function addMatcher(Matcher $m): void
    {
        $this->matchers[] = $m;
    }

    /** @return Matcher[] */
    public function getMatchers(): array
    {
        return $this->matchers;
    }

    public function addSubdirective(Directive $dir): void
    {
        $this->subdirectives[] = $dir;
    }

    /** @return Directive[] */
    public function getSubdirectives(): array
    {
        return $this->subdirectives;
    }

    /**
     * Parses a simple directive or one with sub-blocks,
     * consuming all tokens until the end of the line or the matching '}'.
     */
    public static function parse(Lexer $lexer): self
    {
        $nameToken = $lexer->consume();
        $dir = new self($nameToken->text);
        $currentLine = $nameToken->line;

        while (!$lexer->eof() && ($peek = $lexer->peek())->line === $currentLine) {
            if ($peek->type === TokenType::BRACE_OPEN) {
                // sub-block of subdirectives
                $lexer->next(); // skipping '{'
                while (!$lexer->eof() && $lexer->peek()->type !== TokenType::BRACE_CLOSE) {
                    $dir->addSubdirective(self::parse($lexer));
                }
                $lexer->next(); // skipping '}'
                break;
            }

            if ($peek->type === TokenType::STRING) {
                $token = $lexer->consume();
                $quoted = preg_match('/^".*"$|^`.*`$/', $token->text) === 1;
                $text = trim($token->text, '"`');
                $dir->addArgument(new Argument($text, $quoted));
            } else {
                $lexer->next();
            }
        }

        return $dir;
    }

    public function toCaddyfile(int $indentLevel = 0): string
    {
        $indent = str_repeat('    ', $indentLevel);
        $line = $indent . $this->name;

        // Arguments (same line)
        foreach ($this->arguments as $arg) {
            $line .= ' ' . $arg->toCaddyfile(0); // We should never indent arguments
        }

        // Subdirectives block (one level down)
        if (!empty($this->subdirectives)) {
            $line .= " {\n";
            foreach ($this->subdirectives as $sub) {
                $line .= $sub->toCaddyfile($indentLevel + 1) . "\n";
            }
            $line .= $indent . '}';
        }

        return $line;
    }
}