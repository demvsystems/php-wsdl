<?php

namespace Dgame\Wsdl;

use Dgame\Wsdl\Elements\Element;

/**
 * Interface XsdInterface
 * @package Dgame\Wsdl
 */
interface XsdAdapterInterface
{
    /**
     * @param string $name
     *
     * @return Element|null
     */
    public function findElementByNameInDeep(string $name): ?Element;

    /**
     * @param string $prefix
     *
     * @return string
     */
    public function getUriByPrefix(string $prefix): string;
}
