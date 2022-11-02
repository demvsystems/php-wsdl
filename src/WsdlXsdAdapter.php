<?php

namespace Dgame\Wsdl;

use Dgame\Wsdl\Elements\Element;

/**
 * Class WsdlXsdFacade
 * @package Dgame\Wsdl
 */
final class WsdlXsdAdapter implements XsdAdapterInterface
{
    /**
     * @var Wsdl
     */
    private Wsdl $wsdl;

    /**
     * WsdlXsdAdapter constructor.
     *
     * @param Wsdl $wsdl
     */
    public function __construct(Wsdl $wsdl)
    {
        $this->wsdl = $wsdl;
    }

    /**
     * @param string $name
     *
     * @return Element|null
     */
    public function findElementByNameInDeep(string $name): ?Element
    {
        return $this->wsdl->findOneElementInSchemas($name);
    }

    /**
     * @param string $prefix
     *
     * @return string
     */
    public function getUriByPrefix(string $prefix): string
    {
        return $this->wsdl->getLocation();
    }
}
