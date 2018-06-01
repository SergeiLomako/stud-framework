<?php

namespace Mindk\Framework\Auth;

/**
 * Class AuthService
 * @package Mindk\Framework\Auth
 */
class AuthService
{
    /**
     * @var null Current user instance
     */
    protected static $user = null;

    /**
     * Set current user
     */
    public static function setUser($user) {

        self::$user = $user;
    }

    /**
     * Get current user instance
     *
     * @return mixed
     */
    public static function getUser() {

        return self::$user;
    }

    /**
     * Get current user id
     * 
     * @return mixed
     */
    public static function getUserId(){
        
        return self::$user->id;
    } 

    /**
     * Check if current user has requested roles
     *
     * @param $roles
     * @return bool
     */
    public static function checkRoles($roles) {
        $roles = (array)$roles;
        $user = AuthService::getUser();
        $userRole = empty($user) ? 'guest' : $user->getRole();

        return in_array($userRole, $roles);
    }
}