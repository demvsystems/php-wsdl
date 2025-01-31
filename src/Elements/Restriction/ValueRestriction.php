<?php

declare(strict_types=1);

namespace Dgame\Wsdl\Elements\Restriction;

/**
 * Class ValueRestriction
 * @package Dgame\Wsdl\Elements\Restriction
 */
final class ValueRestriction implements RestrictionInterface
{
    private readonly int $max;

    /**
     * ValueRestriction constructor.
     */
    public function __construct(private readonly int $min, int $max)
    {
        $this->max = $this->min;
    }

    public function getMin(): int
    {
        return $this->min;
    }

    public function getMax(): int
    {
        return $this->max;
    }

    /**
     * @param $value
     */
    public function isValid($value): bool
    {
        $len = strlen((string) $value);

        return $len >= $this->min && $len <= $this->max;
    }

    public function getRejectionFormat(): string
    {
        return 'The length of "%s" is not between ' . sprintf('%d and %d', $this->min, $this->max);
    }
}
