<?php

namespace CaddyParser\CaddyConfig\Parser;

use CaddyParser\CaddyConfig\Blocks\GlobalOptionsBlock;
use CaddyParser\CaddyConfig\Blocks\NamedRouteBlock;
use CaddyParser\CaddyConfig\Blocks\SiteBlock;
use CaddyParser\CaddyConfig\Blocks\SnippetBlock;
use CaddyParser\CaddyConfig\Caddyfile;
use CaddyParser\CaddyConfig\Elements\Address;
use CaddyParser\CaddyConfig\Lexer\Lexer;
use CaddyParser\CaddyConfig\Lexer\TokenType;

class CaddyfileParser
{
    private Lexer $lexer;

    public function __construct(Lexer $lexer)
    {
        $this->lexer = $lexer;
    }

    public static function parseFile(string $filePath): Caddyfile
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("File not found: $filePath");
        }

        $content = file_get_contents($filePath);
        return self::parseString($content);
    }

    public static function parseString(string $text): Caddyfile
    {
        $parser = new self(new Lexer($text));
        return $parser->parse();
    }

    private function parse(): Caddyfile
    {
        $caddy = new Caddyfile();

        // 1) Global options?
        if ($this->lexer->peek()->type === TokenType::BRACE_OPEN) {
            $this->lexer->next();
            $caddy->setGlobalOptions(GlobalOptionsBlock::parse($this->lexer));
        }

        // 2) Top-level blocks
        while (!$this->lexer->eof()) {
            $peek = $this->lexer->peek();

            // Snippet Blocks
            if ($peek->type === TokenType::STRING && str_starts_with($peek->text, '(')) {
                $caddy->addSnippet(SnippetBlock::parse($this->lexer));
                continue;
            }

            // Named Route Blocks
            if ($peek->type === TokenType::STRING && str_starts_with($peek->text, '&(')) {
                $caddy->addNamedRoute(NamedRouteBlock::parse($this->lexer));
                continue;
            }

            // Site blocks (only collecting addresses)
            $addresses = [];
            while ($this->lexer->peek()->type === TokenType::STRING) {
                $addresses[] = Address::parse($this->lexer);
            }

            // Site Blocks (if really opened)
            if ($this->lexer->peek()->type === TokenType::BRACE_OPEN) {
                $caddy->addSiteBlock(SiteBlock::parse($this->lexer, $addresses));
                continue;
            }

            // Any other stray token
            $this->lexer->next();
        }

        return $caddy;
    }
}