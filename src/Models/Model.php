<?php

namespace Mindk\Framework\Models;

use Mindk\Framework\DB\DBOConnectorInterface;

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
        $sql = sprintf("SELECT * FROM `%s` WHERE `%s`=" .
            (int)$id, (string)$this->tableName, (string)$this->primaryKey);

        return $this->dbo->setQuery($sql)->getResult($this);
    }

    /**
     * Save record state to db
     *
     * @return bool
     */
    public function save( int $id, string $key, string $value ) : bool {
        $sql = sprintf("UPDATE `%s` SET `%s`='%s' WHERE `%s`=" .
            (int)$id, (string)$this->tableName, (string)$key, (string)$value, (string)$this->primaryKey);

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
}