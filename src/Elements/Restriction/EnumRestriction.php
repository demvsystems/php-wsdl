<?php

declare(strict_types=1);

namespace Dgame\Wsdl\Elements\Restriction;

/**
 * Class EnumRestriction
 * @package Dgame\Wsdl\Elements\Restriction
 */
final class EnumRestriction implements RestrictionInterface
{
    /**
     * EnumRestriction constructor.
     */
    public function __construct(private readonly array $values)
    {
    }

    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @param $value
     */
    public function isValid($value): bool
    {
        return in_array($value, $this->values, false);
    }

    public function getRejectionFormat(): string
    {
        return '"%s" is not in range of ' . print_r($this->values, true);
    }
}
