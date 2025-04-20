<?php

namespace CaddyParser\CaddyConfig;

use CaddyParser\CaddyConfig\Blocks\GlobalOptionsBlock;
use CaddyParser\CaddyConfig\Blocks\NamedRouteBlock;
use CaddyParser\CaddyConfig\Blocks\SiteBlock;
use CaddyParser\CaddyConfig\Blocks\SnippetBlock;
use CaddyParser\CaddyConfig\Serialization\CaddyfileSerializableInterface;
use CaddyParser\CaddyConfig\Serialization\Traits\CaddyfileIndentTrait;

class Caddyfile implements CaddyfileSerializableInterface
{
    use CaddyfileIndentTrait;

    /** @var GlobalOptionsBlock|null */
    protected ?GlobalOptionsBlock $globalOptions = null;

    /** @var SnippetBlock[] */
    protected array $snippets = [];

    /** @var NamedRouteBlock[] */
    protected array $namedRoutes = [];

    /** @var SiteBlock[] */
    protected array $siteBlocks = [];

    public function setGlobalOptions(GlobalOptionsBlock $opts): void
    {
        $this->globalOptions = $opts;
    }

    public function getGlobalOptions(): ?GlobalOptionsBlock
    {
        return $this->globalOptions;
    }

    public function addSnippet(SnippetBlock $snippet): void
    {
        $this->snippets[$snippet->getName()] = $snippet;
    }

    /** @return SnippetBlock[] */
    public function getSnippets(): array
    {
        return array_values($this->snippets);
    }

    public function addNamedRoute(NamedRouteBlock $route): void
    {
        $this->namedRoutes[$route->getName()] = $route;
    }

    /** @return NamedRouteBlock[] */
    public function getNamedRoutes(): array
    {
        return array_values($this->namedRoutes);
    }

    public function addSiteBlock(SiteBlock $block): void
    {
        $this->siteBlocks[] = $block;
    }

    /** @return SiteBlock[] */
    public function getSiteBlocks(): array
    {
        return $this->siteBlocks;
    }

    public function toCaddyfile(int $indentLevel = 0): string
    {
        $lines = [];

        if ($this->globalOptions) {
            $lines[] = $this->indent("{", $indentLevel);
            $lines[] = $this->globalOptions->toCaddyfile($indentLevel + 1);
            $lines[] = $this->indent("}", $indentLevel);
        }

        foreach ($this->snippets as $snippet) {
            $lines[] = $snippet->toCaddyfile($indentLevel);
        }

        foreach ($this->namedRoutes as $route) {
            $lines[] = $route->toCaddyfile($indentLevel);
        }

        foreach ($this->siteBlocks as $site) {
            $lines[] = $site->toCaddyfile($indentLevel);
        }

        return implode("\n\n", array_filter($lines));
    }
}