<?php

declare(strict_types=1);

namespace Dgame\Wsdl\Elements\Restriction;

/**
 * Class PatternRestriction
 * @package Dgame\Wsdl\Elements\Restriction
 */
final class PatternRestriction implements RestrictionInterface
{
    /**
     * PatternRestriction constructor.
     */
    public function __construct(private readonly string $pattern)
    {
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * @param $value
     */
    public function isValid($value): bool
    {
        return preg_match(sprintf('/%s/', $this->pattern), (string) $value) === 1;
    }

    public function getRejectionFormat(): string
    {
        return '"%s" does not match ' . $this->pattern;
    }
}
