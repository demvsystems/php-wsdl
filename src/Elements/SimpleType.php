<?php

namespace Dgame\Wsdl\Elements;

use Dgame\Wsdl\Elements\Restriction\RestrictionFactory;
use Dgame\Wsdl\Elements\Restriction\RestrictionInterface;
use DOMElement;

/**
 * Class SimpleType
 * @package Dgame\Wsdl\Elements
 */
class SimpleType extends Element
{
    /**
     * @var string
     */
    private string $name;

    /**
     * SimpleType constructor.
     *
     * @param DOMElement $element
     */
    public function __construct(DOMElement $element)
    {
        parent::__construct($element);

        $this->name = $element->getAttribute('name');
    }

    /**
     * @param SimpleType|null $simple
     *
     * @return bool
     */
    public function isSimpleType(self &$simple = null): bool
    {
        $simple = $this;

        return true;
    }

    /**
     * @return string
     */
    final public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return RestrictionInterface[]
     */
    final public function getRestrictions(): array
    {
        $nodes = $this->getDomElement()->getElementsByTagName('restriction');

        $restrictions = [];
        for ($i = 0, $c = $nodes->length; $i < $c; $i++) {
            $restrictions[] = RestrictionFactory::createFrom($nodes->item($i));
        }

        return $restrictions;
    }
}
