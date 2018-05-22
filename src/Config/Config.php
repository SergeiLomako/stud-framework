<?php

namespace Mindk\Framework\Config;

/**
 * Class Config
 * System config wrapper
 *
 * @package Mindk\Framework\Config
 */
class Config
{
    /**
     * @var array   Config storage
     */
    protected static $config = [];

    /**
     * @var object  Instance of Config class
     */
    protected static $instance;

    /**
     * Closed Config constructor and clone.
     */
    protected function __construct() { }
    protected function __clone() { }

    /**
     * Config getInstance Singleton
     * @param array $data
     * @return Config
     */
    public static function getInstance($data = [])
    {
        if(empty(self::$config) && !empty($data))
        {
            self::set($data);
        }

        return is_object(self::$instance) ? self::$instance : self::$instance = new self();
    }

    /**
     * Load config
     *
     * @param $file
     */
    public function loadFromFile($file){
        self::$config = include($file);
    }

    /**
     * Set config
     *
     * @param $data
     */
    public static function set($data){
        self::$config = $data;
    }

    /**
     * Get config param
     *
     * @param   string Param name
     *
     * @return mixed
     */
    public function __get($param_name){
        return isset(self::$config[$param_name]) ? self::$config[$param_name] : null;
    }

    /**
     * Recursive getter
     *
     * @param $key  Key may be complex like: db.host, db.driver, etc
     * @param $default
     *
     * @return mixed
     */
    public function get($key = null, $default = null){
        $chain = explode('.', $key);
        $node = self::$config;

        if(!empty($chain)){
            do{
                $cell = array_shift($chain);
                if(!isset($node[$cell])){
                    break;
                }
                $node = is_array($node) ? $node[$cell] : null;
            }while(!empty($chain) && !empty($node));
        }

        return $node ?? $default;
    }

    /**
     * Check if key exists
     *
     * @param $key  Key may be complex like: db.host, db.driver, etc
     *
     * @return bool
     */
    public function has($key): bool{
        $chain = explode('.', $key);
        $node = self::$config;

        do{
            $cell = array_shift($chain);
            if(!isset($node[$cell])){
                return false;
            }
            $node = $node[$cell];
        }while(!empty($chain) && !empty($node));

        return true;
    }
}