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
     * @var string  DB Table standard keys
     */
    const TABLE_NAME = '';
    const PRIMARY_KEY = 'id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

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
     * @param array $inputData
     * @throws ModelException
     */
    public function create( array $inputData ) {
        $tableData = $this->getColumnsNames();
        $tableKeysDiff = array_diff_key($tableData, $inputData);
        $inputKeysDiff = array_diff_key($inputData, $tableData);

        if($tableKeysDiff && !$inputKeysDiff) {

            foreach( $tableKeysDiff as $key => $value) {

                if(!empty($tableData[$key])) {
                    $inputData[$key] = $tableData[$key];
                } else {

                    throw new ModelException('Invalid column names. Expected: ' .
                        implode(', ', array_keys($tableData)) . '. Received: ' .
                        implode(', ', array_keys($inputData)) . '.');
                }
            }

        } else {

            throw new ModelException('Invalid column names. Expected: ' .
                implode(', ', array_keys($tableData)) . '. Received: ' .
                implode(', ', array_keys($inputData)) . '.');
        }

        $keys = implode("`, `", array_keys($inputData));
        $values = implode("', '", $inputData);

        $sql = sprintf("INSERT INTO `%s` (`%s`) VALUES ('%s')",
            (string)$this::TABLE_NAME, (string)$keys, (string)$values);

        $this->dbo->setQuery($sql);
    }

    /**
     * Read record
     *
     * @param int $id
     * @return mixed
     */
    public function load( int $id ) {
        $sql = sprintf("SELECT * FROM `%s` WHERE `%s`='%u'", (string)$this::TABLE_NAME, $this::PRIMARY_KEY, $id);

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

        foreach($objectVars as $key => $value) {
            if(!array_key_exists($key, $classVars) &&
                $key !== $this::PRIMARY_KEY &&
                $key !== $this::CREATED_AT &&
                $key !== $this::UPDATED_AT ) {

                $result[] = "`$key`='$value'";
            }
        }


        $sql = sprintf("UPDATE `%s` SET %s WHERE `%s`='%u'",
               (string)$this::TABLE_NAME, implode(',', $result), $this::PRIMARY_KEY, (int)$this->{$this::PRIMARY_KEY});

        return ($this->dbo->setQuery($sql) !== false) ? true : false;
    }

    /**
     * Delete record from DB
     *
     * @param int $id
     */
    public function delete( int $id ) {

        $sql = sprintf("DELETE FROM `%s` WHERE `%s`='%u'",
            (string)$this::TABLE_NAME, $this::PRIMARY_KEY, $id);

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
            $columnName, (string)$this::TABLE_NAME);

        return $this->dbo->setQuery($sql)->getList(get_class($this));
    }

    /**
     * Gets columns names of a table
     *
     * @return mixed
     */
    public function getColumnsNames() {

        $sql = sprintf("DESCRIBE `%s`",
            (string)$this::TABLE_NAME);

        $columnsInfo = $this->dbo->setQuery($sql)->getList(get_class($this));

        foreach($columnsInfo as $value) {

            if( $value->Field !== $this::PRIMARY_KEY &&
                $value->Field !== $this::CREATED_AT &&
                $value->Field !== $this::UPDATED_AT) {

                $result[$value->Field] = $value->Default;
            }
        }

        return !empty($result) ? $result : null;
    }
}