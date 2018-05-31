<?php

namespace Mindk\Framework\Models;

use Mindk\Framework\DB\DBOConnectorInterface;
use Mindk\Framework\Exceptions\ModelException;
use Mindk\Framework\Exceptions\NotFoundException;

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
     * @param DBOConnectorInterface $db
     */
    public function __construct(DBOConnectorInterface $db)
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
  
    public function load( int $id ) {
        $sql = sprintf("SELECT * FROM `%s` WHERE `%s`= %s",
                      (string)$this->tableName, (string)$this->primaryKey, (int)$id);

        return $this->dbo->setQuery($sql)->getResult($this);
    }

    /**
     * Get model by id if exist.
     * 
     * @param $id
     * @return object
     * @throws NotFoundException
     */
    public function findOrFail($id){
        $model = $this->load($id);
        if(!$model){
            throw new NotFoundException('Model not found');
        }
        
        return $model;
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
        $sql = sprintf("DELETE FROM `%s` WHERE `%s`= %s",
               (string)$this->tableName, (string)$this->primaryKey, (int)$id);

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

}
