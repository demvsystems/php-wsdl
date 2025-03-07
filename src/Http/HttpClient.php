<?php

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
    /**
     * @var self
     */
    private static ?HttpClient $instance = null;

    /**
     * @var Client
     */
    private Client $client;

    /**
     * HttpClient constructor.
     */
    private function __construct()
    {
        $this->client = new Client(['defaults' => [
            'verify' => false
        ]]);
    }

    /**
     * @return HttpClient
     */
    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param string $uri
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function get(string $uri): ResponseInterface
    {
        try {
            return $this->client->get($uri);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                return $e->getResponse();
            }

            throw $e;
        }
    }

    /**
     * @param string $uri
     *
     * @return DOMDocument|null
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
        if (empty($content)) {
            return null;
        }

        $document = new DOMDocument('1.0', 'utf-8');
        if ($document->loadXML($content)) {
            return $document;
        }

        return null;
    }
}
