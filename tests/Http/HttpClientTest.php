<?php

namespace Dgame\Wsdl\Test\Http;

use Dgame\Wsdl\Http\HttpClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use PHPUnit\Framework\TestCase;

final class HttpClientTest extends TestCase
{
    public function testLoadsDocumentThroughGivenClient(): void
    {
        $history = [];
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, [], '<definitions/>'),
        ]));
        $handler->push(Middleware::history($history));
        $httpClient = new HttpClient(new Client([
            'handler'                       => $handler,
            RequestOptions::CONNECT_TIMEOUT => 3,
            RequestOptions::TIMEOUT         => 7,
        ]));

        $document = $httpClient->loadDocument('http://example.test/service?wsdl');

        $this->assertNotNull($document);
        $this->assertSame('definitions', $document->documentElement->tagName);
        $this->assertCount(1, $history);
        $this->assertSame('GET', $history[0]['request']->getMethod());
        $this->assertSame('http://example.test/service?wsdl', (string) $history[0]['request']->getUri());
        $this->assertSame(3, $history[0]['options'][RequestOptions::CONNECT_TIMEOUT]);
        $this->assertSame(7, $history[0]['options'][RequestOptions::TIMEOUT]);
    }

    public function testNetworkErrorsArePropagated(): void
    {
        $request    = new Request('GET', 'http://example.test/service?wsdl');
        $httpClient = new HttpClient(new Client([
            'handler' => HandlerStack::create(new MockHandler([
                new ConnectException('cURL error 28: Operation timed out', $request),
            ])),
        ]));

        $this->expectException(ConnectException::class);

        $httpClient->loadDocument('http://example.test/service?wsdl');
    }

    public function testNonSuccessfulResponsesYieldNoDocument(): void
    {
        $httpClient = new HttpClient(new Client([
            'handler' => HandlerStack::create(new MockHandler([
                new Response(500, [], '<definitions/>'),
            ])),
        ]));

        $this->assertNull($httpClient->loadDocument('http://example.test/service?wsdl'));
    }
}
