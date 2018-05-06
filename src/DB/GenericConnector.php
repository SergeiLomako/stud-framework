<?php

namespace Mindk\Framework\DB;

use Mindk\Framework\Config\Config;

/**
 * Class GenericConnector
 * @package Mindk\Framework\DB
 */
class GenericConnector implements DBOConnectorInterface
{
    protected $connection = null;

    protected $statement = null;

    /**
     * GenericConnector constructor.
     */
    public function __construct(Config $config)
    {
        $this->connection = new \PDO(sprintf('mysql:host=%s;dbname=%s;', $config->get('db.host'), $config->get('db.db_name')),
            $config->get('db.user'),
            $config->get('db.password')
        );
    }

    /**
     * Set SQL query
     */
    public function setQuery($sql) {
        if($this->connection){
            $this->statement = $this->connection->query($sql);
        }

        return $this;
    }

    /**
     * Get result
     *
     * @param   Target object
     *
     * @return  Object
     */
    public function getResult(&$target) {

        if($this->statement){
            $this->statement->setFetchMode( \PDO::FETCH_INTO, $target );
            $result = $this->statement->fetch( \PDO::FETCH_INTO );
        } else {
            $result = null;
        }

        return $result;
    }

    /**
     * Get results
     *
     * @param string    Class name
     *
     * @return array
     */
    public function getList($targetClass = '\stdClass') {

        if($this->statement){
            $this->statement->setFetchMode( \PDO::FETCH_CLASS, $targetClass);
            $result = $this->statement->fetchAll(\PDO::FETCH_CLASS);
        } else {
            $result = [];
        }

        return $result;
    }
}