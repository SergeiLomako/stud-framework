<?php

namespace Mindk\Framework\Models;

/**
 * Class UserModel
 * @package Mindk\Framework\Models
 */
class UserModel extends Model
{
    /**
     * @var string  DB Table standard keys
     */
    protected $tableName = 'users';
    protected $loginName = 'login';
    protected $passwordName = 'password';
    protected $tokenName = 'auth_token';

    /**
     * Find user by credentials
     *
     * @param $login
     * @param $password
     *
     * @return mixed
     */
    public function findByCredentials($login, $password) {
        $sql = sprintf("SELECT * FROM `%s` WHERE `%s`='%s' AND `%s`='%s'",
            $this->tableName, $this->loginName, (string)$login, $this->passwordName, (string)( md5($password) ));

        return $this->dbo->setQuery($sql)->getResult($this);
    }

    /**
     * Find user by access token
     *
     * @param $token
     *
     * @return mixed
     */
    public function findByToken($token) {
        $token = filter_var($token, FILTER_SANITIZE_STRING);
        $sql = sprintf("SELECT * FROM `%s` WHERE `%s`='%s'",
            $this->tableName, $this->tokenName, (string)$token );

        return $this->dbo->setQuery($sql)->getResult($this);
    }
}