<?php

namespace Dgame\Wsdl\Elements\Restriction;

/**
 * Class LengthRestriction
 * @package Dgame\Wsdl\Elements\Restriction
 */
final class LengthRestriction implements RestrictionInterface
{
    /**
     * @var int|null
     */
    private ?int $min;

    /**
     * @var int|null
     */
    private ?int $max;

    /**
     * @var int|null
     */
    private ?int $length;

    /**
     * @param int $length
     *
     * @return LengthRestriction
     */
    public static function exact(int $length): self
    {
        $restriction         = new self();
        $restriction->length = $length;

        return $restriction;
    }

    /**
     * @param int $min
     * @param int $max
     *
     * @return LengthRestriction
     */
    public static function within(int $min, int $max): self
    {
        $restriction      = new self();
        $restriction->min = $min;
        $restriction->max = $max;

        return $restriction;
    }

    /**
     * @return int|null
     */
    public function getMin(): ?int
    {
        return $this->min;
    }

    /**
     * @return int|null
     */
    public function getMax(): ?int
    {
        return $this->max;
    }

    /**
     * @return int|null
     */
    public function getLength(): ?int
    {
        return $this->length;
    }

    /**
     * @param $value
     *
     * @return bool
     */
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
