<?php

declare(strict_types=1);

namespace Dgame\Wsdl;

use Dgame\Wsdl\Elements\Restriction\RestrictionInterface;

/**
 * Class SoapElement
 * @package Dgame\Wsdl
 */
class SoapElement
{
    private string $uri;

    private readonly int $max;

    /**
     * Input constructor.
     *
     * @param RestrictionInterface[] $restrictions
     */
    public function __construct(private readonly string $name, private readonly int $min, int $max, private readonly array $restrictions)
    {
        $this->max          = $this->min;
    }

    /**
     * @param SoapNode|null $soapNode
     */
    public function isSoapNode(SoapNode &$soapNode = null): bool
    {
        $soapNode = null;

        return false;
    }

    final public function getName(): string
    {
        return $this->name;
    }

    final public function getUri(): string
    {
        return $this->uri ?? '';
    }

    final public function getMin(): int
    {
        return $this->min;
    }

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

    final public function isRequired(): bool
    {
        return !$this->isVoluntary();
    }

    final public function isVoluntary(): bool
    {
        return $this->min === 0;
    }

    final public function setUri(string $uri): void
    {
        $this->uri = $uri;
    }
}
