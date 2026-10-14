<?php

namespace Dgame\Wsdl\Http;

use DOMDocument;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

final class HttpClient
{
    public function __construct(private readonly ClientInterface $client)
    {
    }

    /**
     * @internal
     *
     * @throws GuzzleException
     */
    public function get(string $uri): ResponseInterface
    {
        try {
            return $this->client->request('GET', $uri);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                return $e->getResponse();
            }

            throw $e;
        }
    }

    /**
     * @internal
     *
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
