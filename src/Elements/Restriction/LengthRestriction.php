<?php

declare(strict_types=1);

namespace Dgame\Wsdl\Elements\Restriction;

/**
 * Class LengthRestriction
 * @package Dgame\Wsdl\Elements\Restriction
 */
final class LengthRestriction implements RestrictionInterface
{
    private ?int $min = null;

    private ?int $max = null;

    private ?int $length = null;

    public static function exact(int $length): self
    {
        $restriction         = new self();
        $restriction->length = $length;

        return $restriction;
    }

    public static function within(int $min, int $max): self
    {
        $restriction      = new self();
        $restriction->min = $min;
        $restriction->max = $max;

        return $restriction;
    }

    public function getMin(): ?int
    {
        return $this->min;
    }

    public function getMax(): ?int
    {
        return $this->max;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function isValid($value): bool
    {
        $value = (int) $value;

        return $value >= $this->min && $value <= $this->max;
    }

    public function getRejectionFormat(): string
    {
        return '"%d" is not between ' . sprintf('%d and %d', $this->min, $this->max);
    }
}
