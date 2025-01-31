<?php

declare(strict_types=1);

namespace Dgame\Wsdl;

use Dgame\Soap\Components\Body;
use Dgame\Soap\Components\Envelope;
use Dgame\Soap\Components\Header;

/**
 * Class SoapRequest
 * @package Dgame\Wsdl
 */
class SoapRequest
{
    private readonly string $action;

    private ?Body $body = null;

    private ?Header $header = null;

    /**
     * SoapRequest constructor.
     *
     *
     * @throws \Throwable
     */
    public function __construct(Wsdl $wsdl, private readonly string $operation)
    {
        $this->action    = $wsdl->getSoapActionOfOperation($this->operation);
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function getSoapAction(): string
    {
        return $this->action;
    }

    public function getBody(): Body
    {
        if (!$this->body instanceof \Dgame\Soap\Components\Body) {
            $this->body = new Body();
        }

        return $this->body;
    }

    public function getHeader(): Header
    {
        if (!$this->header instanceof \Dgame\Soap\Components\Header) {
            $this->header = new Header();
        }

        return $this->header;
    }

    public function createEnvelope(): Envelope
    {
        $envelope = new Envelope();
        $envelope->appendElement($this->getHeader());
        $envelope->appendElement($this->getBody());

        return $envelope;
    }
}
