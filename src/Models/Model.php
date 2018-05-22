<?php

namespace Mindk\Framework\Models;

use Mindk\Framework\DB\DBOConnectorInterface;
use Mindk\Framework\Exceptions\ModelException;

/**
 * Basic Model Class
 * @package Mindk\Framework\Models
 */
abstract class Model
{
    /**
     * @var string  DB Table standard keys
     */
    protected $tableName = '';
    protected $primaryKey = 'id';
    protected $createdAt = 'created_at';
    protected $updatedAt = 'updated_at';

    /**
     * @var null
     */
    protected $dbo = null;

    /**
     * Model constructor
     * @param DBOConnectorInterface $db
     */
    public function __construct(DBOConnectorInterface $db)
    {
        $this->dbo = $db;
    }

    /**
     * Create new record
     *
     * @param array $data
     * @throws ModelException
     */
    public function create( array $data ) {

        $dataColumns = array_keys($data);
        $tableColumns = array_values($this->getColumnsNames());

        if($dataColumns !== array_intersect($dataColumns, $tableColumns) ||
            $tableColumns !== array_intersect($tableColumns, $dataColumns)) {

            throw new ModelException('Invalid column names. Expected: ' .
                implode(', ', $tableColumns) . '. Received: ' . implode(', ', $dataColumns) . '.');
        }

        $keys = implode("`, `", array_keys($data));
        $values = implode("', '", $data);

        $sql = sprintf("INSERT INTO `%s` (`%s`) VALUES ('%s')",
            (string)$this->tableName, (string)$keys, (string)$values);

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

        $sql = sprintf("SELECT * FROM `%s` WHERE `%s`='%u'",
                      (string)$this->tableName, $this->primaryKey, $id);

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
            if(!array_key_exists($key, $classVars) &&
                $key !== $this->primaryKey && $key !== $this->createdAt && $key !== $this->updatedAt ) {

                $result[] = "`$key`='$value'";
            }
        }

        $result = implode(', ', $result);

        $sql = sprintf("UPDATE `%s` SET %s WHERE `%s`='%u'",
            (string)$this->tableName, (string)$result, $this->primaryKey, (int)$this->{$this->primaryKey});

        return ($this->dbo->setQuery($sql) !== false) ? true : false;
    }

    /**
     * Delete record from DB
     */
    public function delete( int $id ) {

        $sql = sprintf("DELETE FROM `%s` WHERE `%s`='%u'",
            (string)$this->tableName, $this->primaryKey, $id);

        $this->dbo->setQuery($sql);
    }

    /**
     * Get list of records
     *
     * @return array
     */
    public function getList( string $columnName = '*' ) {

        $sql = sprintf("SELECT `%s` FROM `%s`",
            $columnName, (string)$this->tableName);

        return $this->dbo->setQuery($sql)->getList(get_class($this));
    }

    /**
     * Gets columns names of a table
     *
     * @return mixed
     */
    public function getColumnsNames()
    {

        $sql = sprintf("DESCRIBE `%s`",
            (string)$this->tableName);

        $columnsInfo = $this->dbo->setQuery($sql)->getList(get_class($this));

        foreach ($columnsInfo as $value) {

            if ($value->Field !== $this->primaryKey && $value->Field !== $this->createdAt &&
                $value->Field !== $this->updatedAt) {

                $result[] = $value->Field;
            }
        }

        return !empty($result) ? $result : null;
    }
}