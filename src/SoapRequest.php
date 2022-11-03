<?php

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
    /**
     * @var string
     */
    private string $operation;
    /**
     * @var string
     */
    private string $action;
    /**
     * @var Body|null
     */
    private ?Body $body;
    /**
     * @var Header|null
     */
    private ?Header $header;

    /**
     * SoapRequest constructor.
     *
     * @param Wsdl   $wsdl
     * @param string $operation
     *
     * @throws \Throwable
     */
    public function __construct(Wsdl $wsdl, string $operation)
    {
        $this->operation = $operation;
        $this->action    = $wsdl->getSoapActionOfOperation($operation);
    }

    /**
     * @return string
     */
    public function getOperation(): string
    {
        return $this->operation;
    }

    /**
     * @return string
     */
    public function getSoapAction(): string
    {
        return $this->action;
    }

    /**
     * @return Body
     */
    public function getBody(): Body
    {
        if ($this->body === null) {
            $this->body = new Body();
        }

        return $this->body;
    }

    /**
     * @return Header
     */
    public function getHeader(): Header
    {
        if ($this->header === null) {
            $this->header = new Header();
        }

        return $this->header;
    }

    /**
     * @return Envelope
     */
    public function createEnvelope(): Envelope
    {
        $envelope = new Envelope();
        $envelope->appendElement($this->getHeader());
        $envelope->appendElement($this->getBody());

        return $envelope;
    }
}
