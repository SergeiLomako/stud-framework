<?php

namespace Mindk\Framework\Http\Request;

/**
 * Class Request
 *
 * @package Mindk\Http\Request
 */
class Request
{
    /**
     * @var array   Http headers
     */
    public $headers = null;

    /**
     * @var array   Raw Request data storage cache
     */
    private $raw_data = null;

    /**
     * Request constructor.
     */
    public function __construct()
    {
        $headers = [];

        // Parse and cache HTTP headers
        if(function_exists('getallheaders')){
            $headers = getallheaders();
        } elseif(function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
        } else {

            foreach($_SERVER as $key => $value){
                if ( preg_match('/^HTTP_/i', $key) ) {
                    $key = str_replace(" ", "-", ucwords(strtolower(str_replace("_"," ",substr($key,5)))));
                    $headers[$key] = $value;
                }
            }
        }

        // Grab all request data:
        $raw_data = $_REQUEST;
        if($raw_input = json_decode($this->getRawInput(), true)) {
            if(!is_array($raw_input)){
                $raw_input = ['_raw' => $raw_input];
            }

            $raw_data = array_merge($raw_data, $raw_input);
        }

        // Make headers act like object:
        $this->headers  = new \ArrayObject($headers, \ArrayObject::ARRAY_AS_PROPS);
        $this->raw_data = new \ArrayObject($raw_data, \ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Get request Uri
     *
     * @return string
     */
    public function getUri(): string {

        return explode('?', $_SERVER['REQUEST_URI'])[0];
    }

    /**
     * Get request method name
     *
     * @return string
     */
    public function getMethod(): string {

        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Get request variable
     *
     * @param   string    Name
     * @param   mixed     Default value
     * @param   string    Filter type
     *
     * @return null
     */
    public function get(string $name, $default = null, string $type = 'raw'){

        $value = $this->raw_data[$name] ?? $default;

        return $this->filterVar($value, $type);
    }

    /**
     * Bind some raw data to request
     *
     * @param $name
     * @param $value
     */
    public function set($name, $value) {

        $this->raw_data->offsetSet($name, $value);
    }

    /**
     * Get request header value
     *
     * @param $name
     * @param null $default
     *
     * @return mixed|null
     */
    public function getHeader(string $name, $default = null) {

        return $this->headers[$name] ?? $default;
    }

    /**
     * Get raw php input cache
     *
     * @return string
     */
    public function getRawInput(): string {

        return file_get_contents('php://input');
    }

    /**
     * Filter request data
     *
     * @param $data
     * @param string $type
     *
     * @return mixed
     */
    public function filterVar($data, string $type = 'raw') {

        //@TODO: Add some filtration for data here!

        return $data;
    }
}