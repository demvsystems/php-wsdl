<?php

declare(strict_types=1);

namespace Dgame\Wsdl\Elements;

use DOMElement;

/**
 * Class Extension
 * @package Dgame\Wsdl\Elements
 */
final class Extension
{
    private string $base;

    private string $prefix;

    /**
     * Extension constructor.
     */
    public function __construct(private readonly DOMElement $domElement, string $extension)
    {
        if (str_contains($extension, ':')) {
            [$this->prefix, $this->base] = explode(':', $extension);
        } else {
            $this->base = $extension;
        }
    }

    public function getPrefixedName(): string
    {
        return sprintf('%s:%s', $this->prefix, $this->base);
    }

    public function getDomElement(): DOMElement
    {
        return $this->domElement;
    }

    public function getBase(): string
    {
        return $this->base;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @return Element[]
     */
    public function getElements(): array
    {
        $elements = [];

        $nodes = $this->domElement->getElementsByTagName('element');
        for ($i = 0, $c = $nodes->length; $i < $c; ++$i) {
            $node = $nodes->item($i);

            $elements[] = new Element($node);
        }

        return $elements;
    }
}
