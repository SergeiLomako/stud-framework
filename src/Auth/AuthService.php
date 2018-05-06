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
     * Check if current user has requested roles
     *
     * @return bool
     */
    public static function checkRoles($roles) {
        $roles = (array)$roles;
        $user = AuthService::getUser();
        $userRole = empty($user) ? 'guest' : self::$user->role;

        return in_array($userRole, $roles);
    }
}