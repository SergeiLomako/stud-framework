<?php

namespace Mindk\Framework\Models;

/**
 * Class UserModel
 *
 * @package Mindk\Framework\Models
 */
class UserModel extends Model
{
    protected $tableName= 'users';

    /**
     * Find user by credentials
     *
     * @param $login
     * @param $password
     *
     * @return mixed
     */
    public function findByCredentials($login, $password) {

        $sql = sprintf("SELECT * FROM `%s` WHERE `login`='%s' AND `password`='%s'",
            $this->tableName, $login, md5($password));

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

        $sql = sprintf("SELECT * FROM `%s` WHERE `token`='%s'",
            $this->tableName, (string) $token );

        return $this->dbo->setQuery($sql)->getResult($this);
    }

    /**
     * Returns role name of a user
     *
     * @return mixed
     */
    public function getRole(){
        $sql = sprintf("SELECT `title` FROM `roles` WHERE `id` = %s", $this->role_id);
        $state =  $this->dbo->setQuery($sql)->getResult($this);
        return $state->title;
    }

    /**
     * Find user by email
     * 
     * @param $email
     * @return mixed
     */    
    public function findByEmail($email){
        $sql = sprintf("SELECT * FROM `%s` WHERE `login`='%s'", $this->tableName, $email);
        return $this->dbo->setQuery($sql)->getResult($this);
    }
}