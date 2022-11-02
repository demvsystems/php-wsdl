<?php

namespace Dgame\Wsdl\Elements\Restriction;

/**
 * Class EnumRestriction
 * @package Dgame\Wsdl\Elements\Restriction
 */
final class EnumRestriction implements RestrictionInterface
{
    /**
     * @var array
     */
    private array $values ;

    /**
     * EnumRestriction constructor.
     *
     * @param array $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public function isValid($value): bool
    {
        return in_array($value, $this->values, false);
    }

    /**
     * @return string
     */
    public function getRejectionFormat(): string
    {
        return '"%s" is not in range of ' . print_r($this->values, true);
    }
}
