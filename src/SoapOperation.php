<?php

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
    /**
     * @var XsdAdapterInterface
     */
    private XsdAdapterInterface $xsd;
    /**
     * @var Element|null
     */
    private ?Element $element;
    /**
     * @var SoapElement[]
     */
    private array $elements = [];

    /**
     * SoapOperation constructor.
     *
     * @param XsdAdapterInterface $xsd
     * @param string              $operation
     */
    public function __construct(XsdAdapterInterface $xsd, string $operation)
    {
        $this->xsd     = $xsd;
        $this->element = $xsd->findElementByNameInDeep($operation);
    }

    /**
     * @return XsdAdapterInterface
     */
    public function getXsd(): XsdAdapterInterface
    {
        return $this->xsd;
    }

    /**
     * @return Element
     */
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
        if (!empty($this->elements)) {
            return $this->elements;
        }

        $this->elements = [];

        $complex = $this->getElement()->getComplexType();
        enforce($complex !== null)->orThrow('Can only collect elements from a ComplexType');

        if ($complex->hasExtensions()) {
            $extension      = $complex->getFirstExtension();
            $this->elements = $this->loadParentElements($extension);
        }

        $this->elements = array_merge($this->collectElementsFrom($complex), $this->elements);

        return $this->elements;
    }

    /**
     * @param Extension $extension
     *
     * @return SoapElement[]
     * @throws \Throwable
     */
    private function loadParentElements(Extension $extension): array
    {
        $name    = $extension->getPrefixedName();
        $parent  = $this->getXsd()->findElementByNameInDeep($name);
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
        $uri    = $this->getXsd()->getUriByPrefix($prefix);

        array_walk($elements, function (SoapElement $element) use ($uri): void {
            $element->setUri($uri);
        });

        return $elements;
    }

    /**
     * @param Extension $extension
     *
     * @return SoapElement[]
     */
    private function collectExtensionElements(Extension $extension): array
    {
        return $this->collectElements($extension->getElements());
    }

    /**
     * @param ComplexType $complex
     *
     * @return SoapElement[]
     * @throws \Throwable
     */
    private function collectElementsFrom(ComplexType $complex): array
    {
        $elements = [];
        if ($complex->hasExtensions()) {
            $extension = $complex->getFirstExtension();
            $elements  = array_merge($elements, $this->loadParentElements($extension));
        }

        $childElements = $complex->getElements();

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
        foreach ($childElements as $child) {
            $name = $child->getAttribute('name');

            $elements[$name] = $this->createChildElement($child);
        }

        return $elements;
    }

    /**
     * @param Element $child
     *
     * @return SoapElement
     * @throws \Throwable
     */
    private function createChildElement(Element $child): SoapElement
    {
        $min  = $child->getAttribute('minOccurs');
        $max  = $child->getAttribute('maxOccurs');
        $name = $child->getAttribute('name');
        $type = $child->getAttribute('type');

        $restrictions = $this->loadRestrictions($type);
        $element      = $this->getXsd()->findElementByNameInDeep($type);
        if ($element !== null && $element->isComplexType($complex)) {
            $node = new SoapNode($name, (int) $min, (int) $max, $restrictions);
            $node->setChildElements($this->collectElementsFrom($complex));

            return $node;
        }

        return new SoapElement($name, (int) $min, (int) $max, $restrictions);
    }

    /**
     * @param string $type
     *
     * @return RestrictionInterface[]
     */
    private function loadRestrictions(string $type): array
    {
        if (!str_contains($type, ':')) {
            return [];
        }

        $element = $this->getXsd()->findElementByNameInDeep($type);

        /** @var SimpleType $simple */
        if ($element !== null && $element->isSimpleType($simple)) {
            return $simple->getRestrictions();
        }

        return [];
    }
}
