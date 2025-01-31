<?php

declare(strict_types=1);

namespace Dgame\Wsdl;

use Dgame\Wsdl\Elements\Element;

/**
 * Class WsdlXsdFacade
 * @package Dgame\Wsdl
 */
final class WsdlXsdAdapter implements XsdAdapterInterface
{
    /**
     * WsdlXsdAdapter constructor.
     */
    public function __construct(private readonly Wsdl $wsdl)
    {
    }

    public function findElementByNameInDeep(string $name): ?Element
    {
        return $this->wsdl->findOneElementInSchemas($name);
    }

    public function getUriByPrefix(string $prefix): string
    {
        return $this->wsdl->getLocation();
    }
}
