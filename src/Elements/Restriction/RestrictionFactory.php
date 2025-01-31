<?php

declare(strict_types=1);

namespace Dgame\Wsdl\Elements\Restriction;

use DOMElement;
use function Dgame\Ensurance\enforce;

/**
 * Class Restriction
 * @package Dgame\Wsdl\Elements
 */
final class RestrictionFactory
{
    /**
     * @throws \Throwable
     */
    public static function createFrom(DOMElement $domElement): RestrictionInterface
    {
        $restriction = self::createValueRestriction($domElement) ??
                       self::createEnumRestriction($domElement) ??
                       self::createLengthRestriction($domElement) ??
                       self::createPatternRestriction($domElement);

        enforce($restriction instanceof \Dgame\Wsdl\Elements\Restriction\RestrictionInterface)->orThrow('Could not detect Restriction');

        return $restriction;
    }

    public static function createLengthRestriction(DOMElement $domElement): ?RestrictionInterface
    {
        $domNodeList = $domElement->getElementsByTagName('length');
        if ($domNodeList->length !== 0) {
            $len = (int) $domNodeList->item(0)->getAttribute('value');

            return LengthRestriction::exact($len);
        }

        $minLength = $domElement->getElementsByTagName('minLength');
        $maxLength = $domElement->getElementsByTagName('maxLength');
        if ($minLength->length !== 0 || $maxLength->length !== 0) {
            $min = $minLength->length !== 0 ? (int) $minLength->item(0)->getAttribute('value') : 0;
            $max = $maxLength->length !== 0 ? (int) $maxLength->item(0)->getAttribute('value') : PHP_INT_MAX;

            return LengthRestriction::within($min, $max);
        }

        return null;
    }

    public static function createPatternRestriction(DOMElement $domElement): ?RestrictionInterface
    {
        $domNodeList = $domElement->getElementsByTagName('pattern');
        if ($domNodeList->length === 0) {
            return null;
        }

        return new PatternRestriction($domNodeList->item(0)->getAttribute('value'));
    }

    public static function createEnumRestriction(DOMElement $domElement): ?RestrictionInterface
    {
        $domNodeList = $domElement->getElementsByTagName('enumeration');
        if ($domNodeList->length === 0) {
            return null;
        }

        $values = [];
        for ($i = 0, $c = $domNodeList->length; $i < $c; ++$i) {
            $values[] = $domNodeList->item($i)->getAttribute('value');
        }

        return new EnumRestriction($values);
    }

    public static function createValueRestriction(DOMElement $domElement): ?RestrictionInterface
    {
        $domNodeList = $domElement->getElementsByTagName('minInclusive');
        $maxValue = $domElement->getElementsByTagName('maxInclusive');
        if ($domNodeList->length !== 0 || $maxValue->length !== 0) {
            $min = $domNodeList->length !== 0 ? (int) $domNodeList->item(0)->getAttribute('value') : 0;
            $max = $maxValue->length !== 0 ? (int) $maxValue->item(0)->getAttribute('value') : PHP_INT_MAX;

            return new ValueRestriction($min, $max);
        }

        return null;
    }
}
