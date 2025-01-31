<?php

declare(strict_types=1);

namespace Dgame\Wsdl\Elements;

use DOMElement;
use function Dgame\Ensurance\enforce;

/**
 * Class Element
 * @package Dgame\Wsdl\Elements
 */
class Element
{
    /**
     * Element constructor.
     */
    public function __construct(private readonly DOMElement $domElement)
    {
    }

    /**
     * @param SimpleType|null $simpleType
     */
    public function isSimpleType(SimpleType &$simpleType = null): bool
    {
        $simpleType = null;

        return false;
    }

    /**
     * @param ComplexType|null $complexType
     */
    public function isComplexType(ComplexType &$complexType = null): bool
    {
        $complexType = null;

        return false;
    }

    final public function hasAttribute(string $name): bool
    {
        return $this->domElement->hasAttribute($name);
    }

    final public function getAttribute(string $name): string
    {
        return $this->domElement->getAttribute($name);
    }

    final public function getDomElement(): DOMElement
    {
        return $this->domElement;
    }

    /**
     * @throws \Throwable
     */
    final public function getSimpleType(): SimpleType
    {
        if ($this->isSimpleType($simple)) {
            return $simple;
        }

        $domNodeList = $this->getDomElement()->getElementsByTagName('simpleType');
        enforce($domNodeList->length !== 0)->orThrow('There are no nodes with name Simple-Types');
        enforce($domNodeList->length === 1)->orThrow('There are multiple nodes with name Simple-Types');

        return new SimpleType($domNodeList->item(0));
    }

    /**
     * @throws \Throwable
     */
    final public function getComplexType(): ComplexType
    {
        if ($this->isComplexType($complex)) {
            return $complex;
        }

        $domNodeList = $this->getDomElement()->getElementsByTagName('complexType');
        enforce($domNodeList->length !== 0)->orThrow('There are no nodes with name Complex-Types');
        enforce($domNodeList->length === 1)->orThrow('There are multiple nodes with name Complex-Types');

        return new ComplexType($domNodeList->item(0));
    }
}
