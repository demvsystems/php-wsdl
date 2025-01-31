<?php

declare(strict_types=1);

namespace Dgame\Wsdl\Http;

use DOMDocument;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Client
 * @package Dgame\Wsdl\Http
 */
final class HttpClient
{
    private static ?HttpClient $httpClient = null;

    private readonly Client $client;

    private function __construct()
    {
        $this->client = new Client(['defaults' => [
            'verify' => false
        ]]);
    }

    public static function instance(): self
    {
        if (self::$httpClient === null) {
            self::$httpClient = new self();
        }

        return self::$httpClient;
    }

    /**
     * @throws GuzzleException
     */
    public function get(string $uri): ResponseInterface
    {
        try {
            return $this->client->get($uri);
        } catch (RequestException $requestException) {
            if ($requestException->hasResponse()) {
                return $requestException->getResponse();
            }

            throw $requestException;
        }
    }

    /**
     * @throws GuzzleException
     */
    public function loadDocument(string $uri): ?DOMDocument
    {
        $response  = $this->get($uri);
        $code      = $response->getStatusCode();
        if ($code < 200 || $code >= 300) {
            return null;
        }

        $content = $response->getBody()->getContents();
        $content = trim($content);
        if ($content === '' || $content === '0') {
            return null;
        }

        $domDocument = new DOMDocument('1.0', 'utf-8');
        if ($domDocument->loadXML($content)) {
            return $domDocument;
        }

        return null;
    }
}
