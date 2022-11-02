<?php

namespace Dgame\Wsdl;

use Dgame\Wsdl\Elements\Restriction\RestrictionInterface;

/**
 * Class SoapElement
 * @package Dgame\Wsdl
 */
class SoapElement
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $uri;

    /**
     * @var int
     */
    private int $min;

    /**
     * @var int
     */
    private int $max;

    /**
     * @var RestrictionInterface[]
     */
    private array $restrictions;

    /**
     * Input constructor.
     *
     * @param string                 $name
     * @param int                    $min
     * @param int                    $max
     * @param RestrictionInterface[] $restrictions
     */
    public function __construct(string $name, int $min, int $max, array $restrictions)
    {
        $this->name         = $name;
        $this->min          = $min;
        $this->max          = $min;
        $this->restrictions = $restrictions;
    }

    /**
     * @param SoapNode|null $node
     *
     * @return bool
     */
    public function isSoapNode(SoapNode &$node = null): bool
    {
        $node = null;

        return false;
    }

    /**
     * @return string
     */
    final public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    final public function getUri(): string
    {
        return $this->uri ?? '';
    }

    /**
     * @return int
     */
    final public function getMin(): int
    {
        return $this->min;
    }

    /**
     * @return int
     */
    final public function getMax(): int
    {
        return $this->max;
    }

    /**
     * @return RestrictionInterface[]
     */
    final public function getRestrictions(): array
    {
        return $this->restrictions;
    }

    /**
     * @return bool
     */
    final public function isRequired(): bool
    {
        return !$this->isVoluntary();
    }

    /**
     * @return bool
     */
    final public function isVoluntary(): bool
    {
        return $this->min === 0;
    }

    /**
     * @param string $uri
     */
    final public function setUri(string $uri): void
    {
        $this->uri = $uri;
    }
}
