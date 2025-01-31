<?php

declare(strict_types=1);

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
    private readonly string $name;

    /**
     * SimpleType constructor.
     */
    public function __construct(DOMElement $domElement)
    {
        parent::__construct($domElement);

        $this->name = $domElement->getAttribute('name');
    }

    /**
     * @param SimpleType|null $simple
     */
    public function isSimpleType(self &$simple = null): bool
    {
        $simple = $this;

        return true;
    }

    final public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return RestrictionInterface[]
     */
    final public function getRestrictions(): array
    {
        $domNodeList = $this->getDomElement()->getElementsByTagName('restriction');

        $restrictions = [];
        for ($i = 0, $c = $domNodeList->length; $i < $c; ++$i) {
            $restrictions[] = RestrictionFactory::createFrom($domNodeList->item($i));
        }

        return $restrictions;
    }
}
