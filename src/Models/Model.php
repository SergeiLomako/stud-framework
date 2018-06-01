<?php

namespace Mindk\Framework\Models;

use Mindk\Framework\DB\DBOConnectorInterface;
use Mindk\Framework\Exceptions\ModelException;
use Mindk\Framework\Exceptions\NotFoundException;

/**
 * Basic Model Class
 *
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
     * @var string  DB Table except columns
     */
    protected $except = ['id', 'created_at', 'updated_at'];

    /**
     * @var null
     */
    protected $dbo = null;

    /**
     * Model constructor
     *
     * @param DBOConnectorInterface $db
     */
    public function __construct(DBOConnectorInterface $db) {

        $this->dbo = $db;
    }

    /**
     * Create new record
     *
     * @param $data
     * @return mixed
     * @throws ModelException
     */
    public function create( $data )
    {
        $table_columns = $this->getColumnsNames();
        foreach($data as $key => $value){
            if(!in_array($key, $table_columns)){
                throw new ModelException('Invalid column names. Expected: ' . implode(',', $table_columns));
            }
        }

        $array_values = [];
        foreach(array_values($data) as $value){
            if(is_string($value)){
                $value = '\'' . $value . '\'';
            }
            array_push($array_values, $value);
        }

        $sql = sprintf("INSERT INTO `%s` (%s) VALUES (%s)", $this->tableName,
            implode(',', array_keys($data)), implode(',',$array_values ));

        return $this->dbo->setQuery($sql);

    }

    /**
     * Read record
     *
     * @param int $id
     * @return mixed
     */
    public function load( int $id ) {
        $sql = sprintf("SELECT * FROM `%s` WHERE `%s`='%u'", $this->tableName, $this->primaryKey, $id);

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
            throw new NotFoundException('Model with id ' . $id . ' not found');
        }

        return $model;
    }
    /**
     * Save record state to db
     *
     * @return bool
     */
    public function save() : bool {
        $columns = $this->getColumnsNames();
        $objectVars = get_object_vars($this);

        foreach($objectVars as $key => $value) {
            if(in_array($key, $columns)) {
                $result[] = "`$key`='$value'";
            }
        }

        $sql = sprintf("UPDATE `%s` SET %s WHERE `%s`='%u'",
            $this->tableName, implode(',', $result), $this->primaryKey, (int)$this->id);

        return ($this->dbo->setQuery($sql) !== false) ? true : false;
    }

    /**
     * Delete record from DB
     *
     * @param int $id
     */
    public function delete( int $id ) {

        $sql = sprintf("DELETE FROM `%s` WHERE `%s`='%u'",
            $this->tableName, $this->primaryKey, (int) $id);

        $this->dbo->setQuery($sql);
    }

    /**
     * Get list of records
     *
     * @param string $columnName
     * @return mixed
     */
    public function getList( string $columnName = '*' ) {

        $sql = sprintf("SELECT `%s` FROM `%s`",
            $columnName, $this->tableName);

        return $this->dbo->setQuery($sql)->getList(get_class($this));
    }

    /**
     * Gets columns names of a table
     *
     * @return array
     */
    public function getColumnsNames()
    {
        $sql = 'DESCRIBE `' . $this->tableName . '`';
        $this->dbo->setQuery($sql);
        $statement = $this->dbo->get('statement');
        $columns = $statement->fetchAll(\PDO::FETCH_COLUMN);

        return array_diff($columns, $this->except);

    }
}