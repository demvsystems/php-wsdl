<?php

namespace Dgame\Wsdl\Test;

use Dgame\Wsdl\Http\HttpClient;
use Dgame\Wsdl\Wsdl;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class WsdlTest extends TestCase
{
    private const WSDL = <<<'XML'
<definitions xmlns="http://schemas.xmlsoap.org/wsdl/"
             xmlns:xsd="http://www.w3.org/2001/XMLSchema"
             targetNamespace="http://example.test/service">
    <types>
        <xsd:schema targetNamespace="http://example.test/service">
            <xsd:import namespace="http://example.test/types" schemaLocation="types.xsd"/>
        </xsd:schema>
    </types>
</definitions>
XML;

    private const XSD = <<<'XML'
<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema" targetNamespace="http://example.test/types">
    <xsd:element name="Foo" type="xsd:string"/>
</xsd:schema>
XML;

    public function testGivenClientIsUsedForWsdlAndImportedSchemas(): void
    {
        $history = [];
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, [], self::WSDL),
            new Response(200, [], self::XSD),
        ]));
        $handler->push(Middleware::history($history));
        $httpClient = new HttpClient(new Client(['handler' => $handler]));

        $wsdl    = new Wsdl('http://example.test/service?wsdl', $httpClient);
        $schemas = $wsdl->getSchemas();

        $this->assertTrue($wsdl->isValid());
        $this->assertSame($httpClient, $wsdl->getHttpClient());
        $this->assertArrayHasKey('http://example.test/types', $schemas);
        $this->assertCount(2, $history);
        $this->assertSame('http://example.test/service?wsdl', (string) $history[0]['request']->getUri());
        $this->assertSame('http://example.test/types.xsd', (string) $history[1]['request']->getUri());
    }

    public function testNetworkErrorWhileLoadingWsdlIsPropagated(): void
    {
        $request    = new Request('GET', 'http://example.test/service?wsdl');
        $httpClient = new HttpClient(new Client([
            'handler' => HandlerStack::create(new MockHandler([
                new ConnectException('cURL error 28: Operation timed out', $request),
            ])),
        ]));

        $this->expectException(ConnectException::class);

        new Wsdl('http://example.test/service?wsdl', $httpClient);
    }
}
