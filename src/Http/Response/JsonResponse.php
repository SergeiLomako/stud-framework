<?php

namespace Mindk\Framework\Http\Response;

/**
 * Class JsonResponse
 *
 * @package Mindk\Framework\Http\Response
 */
class JsonResponse extends Response
{
    /**
     * Response constructor.
     */
    public function __construct($body, int $code = 200) {

       parent::__construct($body, $code);

       $this->setHeader('Content-Type', 'application/json');
    }

    /**
     * Send body
     */
    public function sendBody() {

        echo json_encode($this->data);
    }
}