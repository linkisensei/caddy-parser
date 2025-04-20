<?php

namespace CaddyParser\CaddyConfig\Serialization\Traits;

trait CaddyfileIndentTrait
{
    protected function indent(string $text, int $level = 0): string
    {
        if ($level <= 0) {
            return trim($text);
        }

        $prefix = str_repeat('    ', $level);
        return implode("\n", array_map(
            fn($line) => $prefix . $line,
            explode("\n", trim($text))
        ));
    }
}