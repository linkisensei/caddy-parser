<?php

namespace CaddyParser\CaddyConfig\Elements;

use CaddyParser\CaddyConfig\Serialization\CaddyfileSerializableInterface;
use CaddyParser\CaddyConfig\Serialization\Traits\CaddyfileIndentTrait;

abstract class AbstractElement implements ElementInterface, CaddyfileSerializableInterface
{
    use CaddyfileIndentTrait;

    abstract public function toCaddyfile(int $indentLevel = 0): string;
}
