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

    public function create( array $data ) {
        $sql = sprintf("INSERT INTO `%s` (`" . implode("`, `", array_keys($data)) .
            "`) VALUES ('" . implode("', '", $data) . "')", (string)$this->tableName);

        $this->dbo->setQuery($sql);
    }

    /**
     * Read record
     *
     * @param   int Record ID
     *
     * @return  object
     */
  
    public function load( int $id ) {
        $sql = sprintf("SELECT * FROM `%s` WHERE `%s`= %s,
                      (string)$this->tableName, (string)$this->primaryKey, (int)$id);

        return $this->dbo->setQuery($sql)->getResult($this);
    }

    /**
     * Save record state to db
     *
     * @return bool
     */
    public function save() : bool {

        $classVars = get_class_vars(get_class($this));
        $objectVars = get_object_vars($this);

        foreach ($objectVars as $key => $value) {
            if(!array_key_exists($key, $classVars)) {
                $result[] = "`$key`='$value'";
            }
        }

        $result = implode(', ', $result);

        $sql = sprintf("UPDATE `%s` SET %s WHERE `%s`=" .
            (int)$this->{$this->primaryKey}, (string)$this->tableName, (string)$result, (string)$this->primaryKey);

        return $this->dbo->setQuery($sql) ? true : false;
    }

    /**
     * Delete record from DB
     */
    public function delete( int $id ) {
        $sql = sprintf("DELETE FROM `%s` WHERE `%s`=" .
            (int)$id, (string)$this->tableName, (string)$this->primaryKey);

        $this->dbo->setQuery($sql);
    }

    /**
     * Get list of records
     *
     * @return array
     */
    public function getList( string $columnName = '*' ) {
        $sql = sprintf("SELECT `%s` FROM `%s`",
            (string)$columnName, (string)$this->tableName);

        return $this->dbo->setQuery($sql)->getList(get_class($this));
    }

    public function getColumnsNames()
    {
        $sql = 'DESCRIBE `' . $this->tableName. '`';
        $this->setQuery($sql);
        $statement = $this->dbo->get('statement');
        $statement->setFetchMode( \PDO::FETCH_COLUMN);

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }


}