<?php

namespace CaddyParser\CaddyConfig\Serialization;

interface CaddyfileSerializableInterface
{
    /**
     * Generates the element's representation in Caddyfile format.
     *
     * @param int $indentLevel indentation level (0 for top-level line)
     * @return string
     */
    public function toCaddyfile(int $indentLevel = 0): string;
}