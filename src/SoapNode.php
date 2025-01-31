<?php

declare(strict_types=1);

namespace Dgame\Wsdl;

/**
 * Class SoapNode
 * @package Dgame\Wsdl
 */
final class SoapNode extends SoapElement
{
    /**
     * @var SoapElement[]
     */
    private array $elements = [];

    /**
     * @param SoapNode|null $node
     */
    public function isSoapNode(self &$node = null): bool
    {
        $node = $this;

        return true;
    }

    /**
     * @param SoapElement[] $elements
     */
    public function setChildElements(array $elements): void
    {
        foreach ($elements as $element) {
            $this->appendChildElement($element);
        }
    }

    public function appendChildElement(SoapElement $soapElement): void
    {
        $this->elements[$soapElement->getName()] = $soapElement;
    }

    /**
     * @return SoapElement[]
     */
    public function getChildElements(): array
    {
        return $this->elements;
    }

    public function hasChildElementWithName(string $name): bool
    {
        return array_key_exists($name, $this->elements);
    }

    public function getChildElementByName(string $name): SoapElement
    {
        return $this->elements[$name];
    }
}
