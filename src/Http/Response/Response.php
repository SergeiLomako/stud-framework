<?php

namespace Mindk\Framework\Http\Response;

/**
 * HTTP Response representation class
 *
 * @package Mindk\Framework\Http\Response
 */
class Response
{
    /**
     * Status messages
     */
    const STATUS_MSGS = [
        200 => 'Ok',
        301 => 'Moved Permanently',
        302 => 'Moved Temporary',
        400 => 'Bad Request',
        401 => 'Auth Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        500 => 'Server Error'
    ];

    /**
     * @var array HTTP response headers
     */
    protected $headers = [];

    /**
     * @var null Response payload data
     */
    protected $data = null;

    /**
     * @var int Status code
     */
    public $code = 200;

    /**
     * Response constructor.
     */
    public function __construct($body, int $code = 200) {

        $this->data = $body;
        $this->code = $code;
    }

    /**
     * Add HTTP response header
     *
     * @param string    Header name
     * @param string    Headers value
     */
    public function setHeader(string $key, string $value) {

        $this->headers[$key] = $value;
    }

    /**
     * Set payload data
     *
     * @param $body
     */
    public function setBody($body) {

        $this->data = $body;
    }

    /**
     * Send headers
     */
    public function sendHeaders() {

        header(sprintf("%s %s %s", $_SERVER['SERVER_PROTOCOL'], $this->code, (self::STATUS_MSGS[$this->code] ?? '')));

        if(!empty($this->headers)){
            foreach ($this->headers as $key => $value) {
                header($key . ': ' . $value);
            }
        }
    }

    /**
     * Send body
     */
    public function sendBody() {

        echo $this->data;
    }

    /**
     * Send response
     */
    public function send() {

        $this->sendHeaders();
        $this->sendBody();
    }
}