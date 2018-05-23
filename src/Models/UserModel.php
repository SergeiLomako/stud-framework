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
    const TABLE_NAME = 'users';
    const LOGIN_NAME = 'login';
    const PASSWORD_NAME = 'password';
    const TOKEN_NAME = 'auth_token';
    const ROLE_NAME = 'role_id';
    const ROLE_TABLE = 'roles';
    const ROLE_TITLE = 'title';

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
            $this::TABLE_NAME, $this::LOGIN_NAME, (string)$login, $this::PASSWORD_NAME, (string)( md5($password) ));

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
            $this::TABLE_NAME, $this::TOKEN_NAME, (string)$token );

        return $this->dbo->setQuery($sql)->getResult($this);
    }

    /**
     * Returns role name of a user
     *
     * @return mixed
     */
    public function getRoleName() {

        $sql = sprintf("SELECT `%s` FROM `%s` WHERE `%s`='%s'",
            $this::ROLE_TITLE, $this::ROLE_TABLE, $this::PRIMARY_KEY, $this->{$this::ROLE_NAME} );

        $result = $this->dbo->setQuery($sql)->getList(get_class($this));
        $result = $result[0]->{$this::ROLE_TITLE};

        return $result;
    }
}