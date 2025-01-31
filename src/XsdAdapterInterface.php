<?php

declare(strict_types=1);

namespace Dgame\Wsdl;

use Dgame\Wsdl\Elements\Element;

/**
 * Interface XsdInterface
 * @package Dgame\Wsdl
 */
interface XsdAdapterInterface
{
    public function findElementByNameInDeep(string $name): ?Element;

    public function getUriByPrefix(string $prefix): string;
}
