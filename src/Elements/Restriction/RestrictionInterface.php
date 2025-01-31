<?php

declare(strict_types=1);

namespace Dgame\Wsdl\Elements\Restriction;

/**
 * Interface RestrictionInterface
 * @package Dgame\Wsdl\Elements\Restriction
 */
interface RestrictionInterface
{
    /**
     * @param $value
     */
    public function isValid($value): bool;

    public function getRejectionFormat(): string;
}
