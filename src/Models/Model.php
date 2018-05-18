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

    public function getColumnsNames()
    {
        $sql = 'DESCRIBE `' . $this->tableName. '`';
        $this->dbo->setQuery($sql);
        $statement = $this->dbo->get('statement');

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Create new record
     */
    public function create( $data )
    {
        $table_columns = $this->getColumnsNames();
        foreach($data as $key => $value){
            if(!in_array($key, $table_columns)){
                throw new ModelException('Invalid column names. Expected: ' . implode(',', $table_columns));
            }
        }
        $columns = implode(',', array_keys($data));
        $array_values = [];
        foreach(array_values($data) as $value){
            if(is_string($value)){
                $value = '\'' . $value . '\'';
            }
            array_push($array_values, $value);
        }

        $sql = 'INSERT INTO `' . $this->tableName . '` ('. $columns .') VALUES (' .
            implode(',',$array_values ) . ')';
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

}