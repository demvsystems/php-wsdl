<?php

declare(strict_types=1);

namespace Dgame\Wsdl\Elements;

use DOMElement;
use function Dgame\Ensurance\enforce;
use function Dgame\Ensurance\ensure;

/**
 * Class ComplexType
 * @package Dgame\Wsdl\Elements
 */
final class ComplexType extends SimpleType
{
    /**
     * @var Extension[]
     */
    private array $extensions = [];

    /**
     * @param ComplexType|null $complex
     */
    public function isComplexType(self &$complex = null): bool
    {
        $complex = $this;

        return true;
    }

    public function isAbstract(): bool
    {
        return $this->hasAttribute('abstract')
               && filter_var($this->getAttribute('abstract'), FILTER_VALIDATE_BOOLEAN);
    }

    public function hasExtensions(): bool
    {
        $extensions = $this->getExtensions();

        return $extensions !== [];
    }

    /**
     * @throws \Throwable
     */
    public function getFirstExtension(): Extension
    {
        $extensions = $this->getExtensions();
        ensure($extensions)->isNotEmpty()->orThrow('No Extensions found');
        ensure($extensions)->isArray()->hasLengthOf(1)->orThrow('Found multiple Extensions');

        return reset($extensions);
    }

    public function hasExtensionWithName(string $name): bool
    {
        return array_key_exists($name, $this->extensions);
    }

    public function getExtensionByName(string $name): Extension
    {
        return $this->extensions[$name];
    }

    /**
     * @return Extension[]
     */
    public function getExtensions(): array
    {
        if ($this->extensions !== []) {
            return $this->extensions;
        }

        $domNodeList = $this->getDomElement()->getElementsByTagName('extension');
        for ($i = 0, $c = $domNodeList->length; $i < $c; ++$i) {
            $node      = $domNodeList->item($i);
            $extension = new Extension($node, $node->getAttribute('base'));

            $this->extensions[$extension->getBase()] = $extension;
        }

        return $this->extensions;
    }

    /**
     * @throws \Throwable
     */
    public function getElementByName(string $name): Element
    {
        $elements = $this->getElementsByName($name);

        enforce($elements !== [])->orThrow('There are no nodes with name %s', $name);
        enforce(count($elements) === 1)->orThrow('There are multiple nodes with name %s', $name);

        return array_pop($elements);
    }

    /**
     * @return Element[]
     */
    public function getElementsByName(string $name): array
    {
        $elements = [];

        $domNodeList = $this->getDomElement()->getElementsByTagName($name);
        for ($i = 0, $c = $domNodeList->length; $i < $c; ++$i) {
            $node = $domNodeList->item($i);

            $elements[$node->getAttribute('name')] = new self($node);
        }

        return $elements;
    }

    /**
     * @return Element[]
     */
    public function getElements(): array
    {
        $domNodeList = $this->getDomElement()->getElementsByTagName('element');

        $elements = [];
        for ($i = 0, $c = $domNodeList->length; $i < $c; ++$i) {
            $node = $domNodeList->item($i);

            $elements[] = new Element($node);
        }

        return $elements;
    }
}
