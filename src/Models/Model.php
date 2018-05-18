<?php

namespace Mindk\Framework\Models;

use Mindk\Framework\DB\DBOConnectorInterface;
use Mindk\Framework\DB\GenericConnector;

/**
 * Basic Model Class
 * @package Mindk\Framework\Models
 */
abstract class Model
{
    /**
     * @var string  DB Table name
     */
    protected $tableName = '';

    /**
     * @var string  DB Table primary key
     */
    protected $primaryKey = 'id';

    /**
     * @var null
     */
    protected $dbo = null;

    /**
     * Model constructor.
     * @param GenericConnector $db
     */
    public function __construct(GenericConnector $db)
    {
        $this->dbo = $db;
    }

    /**
     * Create new record
     */
    public function create( $data )
    {
        $columns = implode(',', array_keys($data));
        $values = implode(',', array_values($data));
        $sql = 'INSERT INTO `' . $this->tableName . '` (`'. $columns .'`) VALUES (`' . $values .'`)';
        return $this->dbo->setQuery($sql);
    }

    /**
     * Read record
     *
     * @param   int Record ID
     *
     * @return  object
     */
    public function load( $id )
    {
        $sql = 'SELECT * FROM `' . $this->tableName .
            '` WHERE `'.$this->primaryKey.'`='.(int)$id; //!

        return $this->dbo->setQuery($sql)->getResult($this);
    }

    /**
     * Save record state to db
     *
     * @return bool
     */
    public function save() {
        //@TODO: Implement this
    }

    /**
     * Delete record from DB
     */
    public function delete() {
        //@TODO: Implement this
    }

    /**
     * Get list of records
     *
     * @return array
     */
    public function getList() {
        $sql = 'SELECT * FROM `' . $this->tableName . '`';

        return $this->dbo->setQuery($sql)->getList(get_class($this));
    }

    public function getColumnsNames()
    {
        $sql = 'DESCRIBE `' . $this->tableName. '`';
        $this->dbo->setQuery($sql);
        $statement = $this->dbo->get('statement');
        $statement->setFetchMode( \PDO::FETCH_COLUMN);

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }


}