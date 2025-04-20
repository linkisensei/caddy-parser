<?php

namespace Linkisensei\CaddyParser\Tests;

use CaddyParser\CaddyConfig\Blocks\GlobalOptionsBlock;
use CaddyParser\CaddyConfig\Caddyfile;
use CaddyParser\CaddyConfig\Elements\Argument;
use CaddyParser\CaddyConfig\Lexer\Lexer;
use CaddyParser\CaddyConfig\Lexer\Token;
use CaddyParser\CaddyConfig\Lexer\TokenType;
use CaddyParser\CaddyConfig\Parser\CaddyfileParser;
use PHPUnit\Framework\TestCase;

class CaddyfileParserTest extends TestCase
{
    public function testLexerSimpleTokens()
    {
        $text = "foo bar baz";
        $lexer = new Lexer($text);
        $tokens = [];
        while (!$lexer->eof()) {
            $tok = $lexer->next();
            if ($tok->type !== TokenType::EOF) {
                $tokens[] = $tok;
            }
        }
        $texts = array_map(fn(Token $t) => $t->text, $tokens);
        $this->assertEquals(['foo', 'bar', 'baz'], $texts);
    }

    public function testLexerQuotesAndBackticks()
    {
        $text = "a \"quoted value\" b `backtick`";
        $lexer = new Lexer($text);
        $texts = [];
        while (!$lexer->eof()) {
            $tok = $lexer->next();
            if ($tok->type !== TokenType::EOF) {
                $texts[] = $tok->text;
            }
        }
        $this->assertEquals(['a', '"quoted value"', 'b', '`backtick`'], $texts);
    }

    public function testLexerBracesAndPlaceholders()
    {
        $text = "{\$ENV} literal {path} { path } {}";
        $lexer = new Lexer($text);
        $texts = [];
        while (!$lexer->eof()) {
            $tok = $lexer->next();
            if ($tok->type !== TokenType::EOF) {
                $texts[] = $tok->text;
            }
        }
        $this->assertContains('{path}', $texts);
        $this->assertContains('{$ENV}', $texts);
    }

    public function testLexerHeredoc()
    {
        $text = "<<EOD
line1
line2
EOD
";
        $lexer = new Lexer($text);
        $tok = $lexer->next();
        $this->assertEquals(TokenType::STRING, $tok->type);
        $this->assertStringContainsString("line1", $tok->text);
    }

    public function testParseGlobalOptions()
    {
        $caddy = CaddyfileParser::parseString("{ http_port 9090 https_port 9091 }");
        $this->assertInstanceOf(Caddyfile::class, $caddy);
        $global = $caddy->getGlobalOptions();
        $this->assertInstanceOf(GlobalOptionsBlock::class, $global);
        $dirs = $global->getDirectives();
        
        // only one directive per line/block
        $this->assertCount(1, $dirs);
        $this->assertEquals('http_port', $dirs[0]->getName());
        $args = array_map(fn(Argument $a) => $a->getText(), $dirs[0]->getArguments());
        $this->assertEquals(['9090', 'https_port', '9091'], $args);
    }

    public function testParseSnippetWithoutPlaceholderAsSubdirective()
    {
        $text = <<<CADDY
(base_server) {
    handle /path* {
        try_files {path} index.php{query}
    }
}
CADDY;
        $caddy = CaddyfileParser::parseString($text);
        $snips = $caddy->getSnippets();
        $this->assertCount(1, $snips);

        $snip = $snips[0];
        $dirs = $snip->getDirectives();
        $this->assertEquals('handle', $dirs[0]->getName());
        $subs = $dirs[0]->getSubdirectives();
        $this->assertCount(1, $subs);

        $try = $subs[0];
        $this->assertEquals('try_files', $try->getName());
        $args = array_map(fn(Argument $a) => $a->getText(), $try->getArguments());
        $this->assertEquals(['{path}', 'index.php{query}'], $args);
    }

    public function testParseSiteBlock()
    {
        $text = "example.com { root * /var/www file_server }\n";
        $caddy = CaddyfileParser::parseString($text);
        $sites = $caddy->getSiteBlocks();
        $this->assertCount(1, $sites);

        $site = $sites[0];
        $addr = $site->getAddresses()[0];
        $this->assertEquals('https://example.com', $addr->getScheme() . '://' . $addr->getHost());
        $dirs = $site->getDirectives();

        // only one directive per line/block
        $this->assertCount(1, $dirs);
        $this->assertEquals('root', $dirs[0]->getName());
        $args = array_map(fn(Argument $a) => $a->getText(), $dirs[0]->getArguments());
        $this->assertEquals(['*', '/var/www', 'file_server'], $args);
    }

    public function testArgumentQuotedAndRaw()
    {
        $text = "example.com { echo \"hello world\" }";
        $caddy = CaddyfileParser::parseString($text);
        $dir = $caddy->getSiteBlocks()[0]->getDirectives()[0];
        $arg = $dir->getArguments()[0];
        $this->assertTrue($arg->isQuoted());
        $this->assertEquals('hello world', $arg->getText());
    }

    public function testParseFromFile()
    {
        $filePath = __DIR__ . '/fixtures/TestCaddyfile';

        $caddy = CaddyfileParser::parseFile($filePath);

        $this->assertInstanceOf(Caddyfile::class, $caddy);
        $this->assertNotNull($caddy->getGlobalOptions());
        $this->assertCount(2, $caddy->getSnippets());
        $this->assertCount(2, $caddy->getSiteBlocks());

        $snippets = $caddy->getSnippets();
        $this->assertEquals(['base_server', 'logging'], array_map(fn($s) => $s->getName(), $snippets));

        $sites = $caddy->getSiteBlocks();
        $this->assertEquals(['example.test', 'another-example.test'], array_map(fn($s) => $s->getAddresses()[0]->getHost(), $sites));
    }
}
