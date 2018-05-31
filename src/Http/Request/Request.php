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
        $raw_data = $_REQUEST + $_FILES;
        if($this->getMethod() === 'PUT') {
            parse_str(file_get_contents("php://input"),$post_vars);
            $raw_data += $post_vars;
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

        if($type != 'raw'){
            switch ($type) {
                case 'int':
                    $data = (int) $data;
                    break;
                case 'array':
                    $data = (array) $data;
                    break;
                case 'float':
                    $data = (float) $data;
                    break;
                case 'bool':
                    $data = (bool) $data;
                    break;
                case 'email':
                    $data = filter_var($data, FILTER_VALIDATE_EMAIL);
                    break;
                case 'string':
                    $data = (string) $data;
                    $data = trim(strip_tags(htmlentities($data)));
                    break;
            }
        }
        return $data;
        
    }

    /**
     * Checks the key for existence
     * 
     * @param $key
     * @return bool
     */
    public function has($key): bool {
        return !empty($this->get($key));
    }

    /**
     * Checks if the value of the key is a file
     * 
     * @param $key
     * @return bool
     */
    public function hasUploadFile($key): bool {
        $file = $this->get($key);
        return is_uploaded_file($file['tmp_name']);
    }

    /**
     * Will check the array for existence and emptiness
     * 
     * @param $key
     * @return bool
     */
    public function check($key){
        return  $this->has($key) && !empty($this->get($key));
    }
       
}