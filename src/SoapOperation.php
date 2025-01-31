<?php

declare(strict_types=1);

namespace Dgame\Wsdl;

use Dgame\Wsdl\Elements\ComplexType;
use Dgame\Wsdl\Elements\Element;
use Dgame\Wsdl\Elements\Extension;
use Dgame\Wsdl\Elements\Restriction\RestrictionInterface;
use Dgame\Wsdl\Elements\SimpleType;
use function Dgame\Ensurance\enforce;

/**
 * Class SoapOperation
 * @package Dgame\Wsdl
 */
final class SoapOperation
{
    private readonly ?Element $element;

    /**
     * @var SoapElement[]
     */
    private array $elements = [];

    /**
     * SoapOperation constructor.
     */
    public function __construct(private readonly XsdAdapterInterface $xsdAdapter, string $operation)
    {
        $this->element = $this->xsdAdapter->findElementByNameInDeep($operation);
    }

    public function getXsd(): XsdAdapterInterface
    {
        return $this->xsdAdapter;
    }

    public function getElement(): Element
    {
        return $this->element;
    }

    /**
     * @return SoapElement[]
     * @throws \Throwable
     */
    public function getSoapElements(): array
    {
        if ($this->elements !== []) {
            return $this->elements;
        }

        $this->elements = [];

        $complexType = $this->element->getComplexType();
        enforce($complexType instanceof \Dgame\Wsdl\Elements\ComplexType)->orThrow('Can only collect elements from a ComplexType');

        if ($complexType->hasExtensions()) {
            $extension      = $complexType->getFirstExtension();
            $this->elements = $this->loadParentElements($extension);
        }

        $this->elements = array_merge($this->collectElementsFrom($complexType), $this->elements);

        return $this->elements;
    }

    /**
     *
     * @return SoapElement[]
     * @throws \Throwable
     */
    private function loadParentElements(Extension $extension): array
    {
        $name    = $extension->getPrefixedName();
        $parent  = $this->xsdAdapter->findElementByNameInDeep($name);
        if ($parent === null) {
            return [];
        }

        $complex = $parent->getComplexType();
        enforce($complex !== null)->orThrow('Can only collect elements from a ComplexType');

        $elements = array_merge(
            $this->collectExtensionElements($extension),
            $this->collectElementsFrom($complex)
        );

        $prefix = $extension->getPrefix();
        $uri    = $this->xsdAdapter->getUriByPrefix($prefix);

        array_walk($elements, function (SoapElement $soapElement) use ($uri): void {
            $soapElement->setUri($uri);
        });

        return $elements;
    }

    /**
     * @return SoapElement[]
     */
    private function collectExtensionElements(Extension $extension): array
    {
        return $this->collectElements($extension->getElements());
    }

    /**
     *
     * @return SoapElement[]
     * @throws \Throwable
     */
    private function collectElementsFrom(ComplexType $complexType): array
    {
        $elements = [];
        if ($complexType->hasExtensions()) {
            $extension = $complexType->getFirstExtension();
            $elements  = array_merge($elements, $this->loadParentElements($extension));
        }

        $childElements = $complexType->getElements();

        return array_merge($this->collectElements($childElements), $elements);
    }

    /**
     * @param Element[] $childElements
     *
     * @return SoapElement[]
     */
    private function collectElements(array $childElements): array
    {
        $elements = [];
        foreach ($childElements as $childElement) {
            $name = $childElement->getAttribute('name');

            $elements[$name] = $this->createChildElement($childElement);
        }

        return $elements;
    }

    /**
     * @throws \Throwable
     */
    private function createChildElement(Element $child): SoapElement
    {
        $min  = $child->getAttribute('minOccurs');
        $max  = $child->getAttribute('maxOccurs');
        $name = $child->getAttribute('name');
        $type = $child->getAttribute('type');

        $restrictions = $this->loadRestrictions($type);
        $element      = $this->xsdAdapter->findElementByNameInDeep($type);
        if ($element !== null && $element->isComplexType($complex)) {
            $soapNode = new SoapNode($name, (int) $min, (int) $max, $restrictions);
            $soapNode->setChildElements($this->collectElementsFrom($complex));

            return $soapNode;
        }

        return new SoapElement($name, (int) $min, (int) $max, $restrictions);
    }

    /**
     * @return RestrictionInterface[]
     */
    private function loadRestrictions(string $type): array
    {
        if (!str_contains($type, ':')) {
            return [];
        }

        $element = $this->xsdAdapter->findElementByNameInDeep($type);

        /** @var SimpleType $simple */
        if ($element !== null && $element->isSimpleType($simple)) {
            return $simple->getRestrictions();
        }

        return [];
    }
}
