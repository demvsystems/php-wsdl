<?php

declare(strict_types=1);

namespace Dgame\Wsdl;

use Dgame\Wsdl\Elements\Element;
use Dgame\Wsdl\Http\HttpClient;
use DOMDocument;
use DOMElement;
use function Dgame\Ensurance\enforce;
use function Dgame\Ensurance\ensure;

/**
 * Class Wsdl
 * @package Dgame\Wsdl
 */
final class Wsdl
{
    private const WSDL_SOAP_SCHEMA = 'http://schemas.xmlsoap.org/wsdl/soap/';

    private const WSDL_SCHEMA      = 'http://schemas.xmlsoap.org/wsdl/';

    private readonly ?DOMDocument $domDocument;

    private ?DOMElement $domElement = null;

    private array $operations = [];

    private array $actions = [];

    /**
     * @var Xsd[]
     */
    private array $schemas = [];

    /**
     * Wsdl constructor.
     */
    public function __construct(private readonly string $location)
    {
        $this->domDocument = HttpClient::instance()->loadDocument($this->location);
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getDocument(): DOMDocument
    {
        return $this->domDocument;
    }

    public function isValid(): bool
    {
        return $this->domDocument !== null;
    }

    public function getOperationsByPattern(string $pattern): array
    {
        $operations = [];
        foreach ($this->getOperations() as $operation) {
            if (preg_match($pattern, (string) $operation) === 1) {
                $operations[] = $operation;
            }
        }

        return $operations;
    }

    /**
     * @throws \Throwable
     */
    public function getSoapActionOfOperation(string $operation): string
    {
        $actions = $this->getOperationsWithSoapActions();
        ensure($actions)->isArray()
                        ->hasKey($operation)
                        ->orThrow('No action for operation "%s"', $operation);

        return $actions[$operation];
    }

    /**
     * @throws \Throwable
     */
    public function getOperationByPattern(string $pattern): string
    {
        $operations = $this->getOperationsByPattern($pattern);

        ensure($operations)->isNotEmpty()
                           ->orThrow('No operation found by pattern %s', $pattern);
        ensure($operations)->isArray()
                           ->hasLengthOf(1)
                           ->orThrow('Ambiguous operation pattern %s. Found multiple occurrences', $pattern);

        return array_pop($operations);
    }

    public function getOperationsWithSoapActions(): array
    {
        return array_combine($this->getOperations(), $this->getSoapActions());
    }

    /**
     * @throws \Throwable
     */
    private function getBinding(): DOMElement
    {
        if ($this->domElement === null) {
            $bindings = $this->domDocument->getElementsByTagNameNS(self::WSDL_SCHEMA, 'binding');
            enforce($bindings->length !== 0)->orThrow('There are no bindings');
            //            enforce($bindings->length === 1)->orThrow('There are %d bindings', $bindings->length);

            $this->domElement = $bindings->item(0);
        }

        return $this->domElement;
    }

    /**
     * @throws \Throwable
     */
    public function getOperations(): array
    {
        if ($this->operations !== []) {
            return $this->operations;
        }

        $domElement    = $this->getBinding();
        $domNodeList = $domElement->getElementsByTagNameNS(self::WSDL_SCHEMA, 'operation');
        for ($i = 0, $c = $domNodeList->length; $i < $c; ++$i) {
            $operation = $domNodeList->item($i);

            $this->operations[] = $operation->getAttribute('name');
        }

        return $this->operations;
    }

    /**
     * @throws \Throwable
     */
    public function getSoapActions(): array
    {
        if ($this->actions !== []) {
            return $this->actions;
        }

        $domElement = $this->getBinding();
        $domNodeList = $domElement->getElementsByTagNameNS(self::WSDL_SOAP_SCHEMA, 'operation');
        for ($i = 0, $c = $domNodeList->length; $i < $c; ++$i) {
            $action = $domNodeList->item($i);

            $this->actions[] = $action->getAttribute('soapAction');
        }

        return $this->actions;
    }

    /**
     * @return Xsd[]
     */
    public function getSchemas(): array
    {
        if ($this->schemas !== []) {
            return $this->schemas;
        }

        $this->schemas = Xsd::load($this);

        return $this->schemas;
    }

    /**
     * @throws \Throwable
     */
    public function getFirstSchema(): Xsd
    {
        $schemas = $this->getSchemas();
        ensure($schemas)->isNotEmpty()->orThrow('No Schemas found');

        return reset($schemas);
    }

    public function hasSchemaWithUri(string $uri): bool
    {
        $schemas = $this->getSchemas();
        if (array_key_exists($uri, $schemas)) {
            return true;
        }

        foreach ($schemas as $schema) {
            if ($schema->hasImportLocation($uri)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws \Exception
     */
    public function getSchemaByUri(string $uri): Xsd
    {
        $schemas = $this->getSchemas();
        if (array_key_exists($uri, $schemas)) {
            return $schemas[$uri];
        }

        foreach ($schemas as $schema) {
            if ($schema->hasImportLocation($uri)) {
                return $schema->loadXsdByUri($uri);
            }
        }

        throw new \Exception('No Schema found with location ' . $uri);
    }

    /**
     * @return Element[]
     */
    public function findAllElementInSchemas(string $name): array
    {
        $elements = [];
        foreach ($this->getSchemas() as $xsd) {
            $elements = array_merge($elements, self::findElementsByNameInDeep($xsd, $name));
        }

        return $elements;
    }

    private static function findElementsByNameInDeep(Xsd $xsd, string $name): array
    {
        $elements = [];

        $element = $xsd->findElementByNameInDeep($name);
        if ($element instanceof \Dgame\Wsdl\Elements\Element) {
            $elements[] = $element;
        }

        foreach ($xsd->loadImportedSchemas() as $xsd) {
            $elements = array_merge($elements, self::findElementsByNameInDeep($xsd, $name));
        }

        return $elements;
    }

    public function findOneElementInSchemas(string $name): ?Element
    {
        $elements = $this->findAllElementInSchemas($name);

        return $elements === [] ? null : reset($elements);
    }
}
