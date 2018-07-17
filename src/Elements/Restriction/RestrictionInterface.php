<?php

namespace Dgame\Wsdl\Elements\Restriction;

/**
 * Interface RestrictionInterface
 * @package Dgame\Wsdl\Elements\Restriction
 */
interface RestrictionInterface
{
    /**
     * @param $value
     *
     * @return bool
     */
    public function isValid($value): bool;

    /**
     * @return string
     */
    public function getRejectionFormat(): string;
}
