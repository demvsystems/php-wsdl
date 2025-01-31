<?php

declare(strict_types=1);

namespace Dgame\Wsdl;

use Dgame\Wsdl\Elements\ComplexType;
use Dgame\Wsdl\Elements\Element;
use Dgame\Wsdl\Elements\SimpleType;
use Dgame\Wsdl\Http\HttpClient;
use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use function Dgame\Ensurance\enforce;
use function Dgame\Ensurance\ensure;

/**
 * Class Xsd
 * @package Dgame\Wsdl
 */
final class Xsd implements XsdAdapterInterface
{
    private const W3_SCHEMA       = 'http://www.w3.org/2001/XMLSchema';

    private const SCHEMA_LOCATION = 'schemaLocation';

    private readonly DOMElement $domElement;

    private ?\DOMXPath $domxPath = null;

    private array $imports = [];

    private readonly string $namespace;

    /**
     * @var self[]
     */
    private array $schemas = [];

    /**
     * Xsd constructor.
     */
    public function __construct(DOMElement $domElement, private readonly ?string $location = null)
    {
        $this->namespace = $domElement->getAttribute('targetNamespace');
        $this->domElement   = $domElement;

        $this->resolveIncludes();
    }

    /**
     * @return self[]
     */
    public static function load(Wsdl $wsdl): array
    {
        enforce($wsdl->isValid())->orThrow('Invalid WSDL "%s" given', $wsdl->getLocation());

        $domNodeList = $wsdl->getDocument()->getElementsByTagNameNS(self::W3_SCHEMA, 'schema');
        enforce($domNodeList->length !== 0)->orThrow('There is no Schema');

        $schemas = [];
        for ($i = 0, $c = $domNodeList->length; $i < $c; ++$i) {
            $node     = $domNodeList->item($i);
            $location = self::getSchemaLocation($node);

            $schema = new self($node, $wsdl->getLocation());
            $schema = $schema->tryLoadXsdByUri($location);

            ensure($schema)->isNotNull()->orThrow('Could not load "%s"', $location);

            $schemas[$schema->getNamespace()] = $schema;
        }

        return $schemas;
    }

    private static function getSchemaLocation(DOMElement $domElement): ?string
    {
        $domNodeList = $domElement->getElementsByTagNameNS(self::W3_SCHEMA, 'include');
        $location = self::findSchemaLocationIn($domNodeList);
        if ($location !== null && $location !== '' && $location !== '0') {
            return $location;
        }

        $imports  = $domElement->getElementsByTagNameNS(self::W3_SCHEMA, 'import');

        return self::findSchemaLocationIn($imports);
    }

    private static function findSchemaLocationIn(DOMNodeList $domNodeList): ?string
    {
        for ($i = 0, $c = $domNodeList->length; $i < $c; ++$i) {
            $node = $domNodeList->item($i);
            if ($node->hasAttribute(self::SCHEMA_LOCATION)) {
                return $node->getAttribute(self::SCHEMA_LOCATION);
            }
        }

        return null;
    }

    /**
     *
     */
    private function resolveIncludes(): void
    {
        $nodes = $this->domElement->getElementsByTagNameNS(self::W3_SCHEMA, 'include');
        for ($i = 0, $c = $nodes->length; $i < $c; ++$i) {
            $include = $nodes->item($i);

            $location = $include->getAttribute('schemaLocation');

            $xsd = $this->loadXsdByUri($location);
            foreach ($xsd->getChildNodes() as $child) {
                $node = $this->domElement->ownerDocument->importNode($child, true);
                $include->parentNode->appendChild($node);
            }
        }

        foreach ($nodes as $node) {
            $node->parentNode->removeChild($node);
        }
    }

    public function getDocument(): DOMDocument
    {
        return $this->domElement->ownerDocument;
    }

    public function hasLocation(): bool
    {
        return $this->location !== null && $this->location !== '' && $this->location !== '0';
    }

    public function getLocation(): string
    {
        return $this->location ?? '';
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getXPath(): DOMXPath
    {
        if ($this->domxPath === null) {
            $this->domxPath = new DOMXPath($this->domElement->ownerDocument);
        }

        return $this->domxPath;
    }

    public function getChildNodes(): DOMNodeList
    {
        return $this->domElement->childNodes;
    }

    /**
     *
     */
    private function loadImports(): void
    {
        $nodes = $this->domElement->getElementsByTagName('import');
        for ($i = 0, $c = $nodes->length; $i < $c; ++$i) {
            $node = $nodes->item($i);
            $uri  = $node->getAttribute('namespace');
            if ($node->hasAttribute('schemaLocation')) {
                $this->imports[$uri] = $node->getAttribute('schemaLocation');
            }
        }
    }

    public function getImports(): array
    {
        if ($this->imports === []) {
            $this->loadImports();
        }

        return $this->imports;
    }

    public function hasImportLocation(string $uri): bool
    {
        return array_key_exists($uri, $this->getImports());
    }

    public function getLocalImportLocationByUri(string $uri): string
    {
        enforce($this->hasImportLocation($uri))->orThrow('No Import found with URI %s', $uri);

        return $this->imports[$uri];
    }

    public function getImportLocationByUri(string $uri): string
    {
        return $this->getLocalImportLocationByUri($uri);
    }

    public function hasUriWithPrefix(string $prefix, string $namespace = 'xmlns'): bool
    {
        $attr = sprintf('%s:%s', $namespace, $prefix);

        return $this->domElement->hasAttribute($attr);
    }

    public function getUriByPrefix(string $prefix, string $namespace = 'xmlns'): string
    {
        $attr = sprintf('%s:%s', $namespace, $prefix);

        return $this->domElement->hasAttribute($attr) ? $this->domElement->getAttribute($attr) : '';
    }

    public function loadXsdByUri(string $uri): self
    {
        $xsd = $this->tryLoadXsdByUri($uri);
        enforce($xsd instanceof \Dgame\Wsdl\Xsd)->orThrow('Could not load XSD by Uri %s', $uri);

        return $xsd;
    }

    /**
     * @return Xsd|null
     */
    public function tryLoadXsdByUri(string $location): ?self
    {
        if ($this->hasImportLocation($location)) {
            $location = $this->getImportLocationByUri($location);
        }

        foreach ($this->getPossibleLocations($location) as $location) {
            $document = HttpClient::instance()->loadDocument($location);
            if ($document instanceof \DOMDocument) {
                return new self($document->documentElement, $location);
            }
        }

        return null;
    }

    /**
     * @return string[]
     */
    private function getPossibleLocations(string $location): array
    {
        if (str_starts_with($location, '//')) {
            return [$location];
        }

        $location     = ltrim($location, '/');
        $baseLocation = sprintf(
            '%s://%s/%s',
            parse_url((string) $this->location, PHP_URL_SCHEME),
            parse_url((string) $this->location, PHP_URL_HOST),
            $location
        );

        if (!$this->hasLocation()) {
            return [$baseLocation];
        }

        return [
            sprintf('%s/%s', pathinfo((string) $this->location, PATHINFO_DIRNAME), $location),
            $baseLocation
        ];
    }

    /**
     * @throws \Throwable
     */
    public function loadXsdByPrefix(string $prefix): self
    {
        $uri = $this->getUriByPrefix($prefix);
        ensure($uri)->isNotEmpty()->orThrow('Empty Xsd-Uri');

        if (!array_key_exists($uri, $this->schemas)) {
            $xsd = $this->tryLoadXsdByUri($uri);
            if ($xsd instanceof \Dgame\Wsdl\Xsd) {
                $this->schemas[$uri] = $xsd;
            }
        }

        enforce(array_key_exists($uri, $this->schemas))->orThrow('No XSD with Uri %s was found', $uri);

        return $this->schemas[$uri];
    }

    /**
     * @return self[]
     */
    public function loadImportedSchemas(): array
    {
        foreach ($this->getImports() as $uri => $location) {
            if (array_key_exists($uri, $this->schemas)) {
                continue;
            }

            $xsd = $this->tryLoadXsdByUri($location);
            if ($xsd instanceof \Dgame\Wsdl\Xsd) {
                $this->schemas[$uri] = $xsd;
            }
        }

        return $this->schemas;
    }

    /**
     * @return Element[]
     */
    public function getAllElementsByName(string $name): array
    {
        $elements = [];

        if (!$this->domElement->hasAttributeNS('http://www.w3.org/2001/XMLSchema', 'xsd')) {
            $this->getXPath()->registerNamespace('xsd', 'http://www.w3.org/2001/XMLSchema');
        }

        $nodes = $this->getXPath()->query(sprintf('//xsd:element[@name="%s"]', $name));
        for ($i = 0, $c = $nodes->length; $i < $c; ++$i) {
            $node = $nodes->item($i);
            $name = $node->getAttribute('name');

            $elements[$name] = new Element($node);
        }

        $nodes = $this->getXPath()->query(sprintf('//xsd:simpleType[@name="%s"]', $name));
        for ($i = 0, $c = $nodes->length; $i < $c; ++$i) {
            $node = $nodes->item($i);
            $name = $node->getAttribute('name');

            $elements[$name] = new SimpleType($node);
        }

        $nodes = $this->getXPath()->query(sprintf('//xsd:complexType[@name="%s"]', $name));
        for ($i = 0, $c = $nodes->length; $i < $c; ++$i) {
            $node = $nodes->item($i);
            $name = $node->getAttribute('name');

            $elements[$name] = new ComplexType($node);
        }

        return $elements;
    }

    public function getOneElementByName(string $name): ?Element
    {
        $elements = $this->getAllElementsByName($name);

        return $elements === [] ? null : reset($elements);
    }

    public function findElementByNameInDeep(string $name): ?Element
    {
        if (!str_contains($name, ':')) {
            return $this->getOneElementByName($name);
        }

        [$prefix, $name] = explode(':', $name);

        $element = $this->getOneElementByName($name);
        if ($element instanceof \Dgame\Wsdl\Elements\Element) {
            return $element;
        }

        if (!$this->hasUriWithPrefix($prefix)) {
            return null;
        }

        $xsd = $this->loadXsdByPrefix($prefix);

        return $xsd->getOneElementByName($name);
    }
}
