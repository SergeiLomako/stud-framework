<?php

namespace Mindk\Framework\Routing;

/**
 * Class Route
 * @package Mindk\Framework\Routing
 */
class Route
{
    /**
     * @var array   Raw data
     */
    protected $data = [];

    /**
     * Route constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        if(!empty($data['handler'])){
            // Parse handler string:
            $buffer = explode('@', $data['handler']);
            $this->data['controller'] = $buffer[0];
            $this->data['action'] = $buffer[1];
        }
    }

    /**
     * Magic getter
     *
     * @param $property
     *
     * @return mixed
     */
    public function __get($property) {
        return array_key_exists($property, $this->data) ? $this->data[$property] : null;
    }
}