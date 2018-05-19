<?php

namespace Mindk\Framework\Controllers;

use Mindk\Framework\Exceptions\AuthRequiredException;
use Mindk\Framework\Exceptions\IncorrectInputException;
use Mindk\Framework\Http\Request\Request;
use Mindk\Framework\Models\UserModel;

/**
 * Class UserController
 * @package Mindk\Framework\Controllers
 */
class UserController
{
    /**
     * Register through action
     *
     * @param Request $request
     * @param UserModel $model
     *
     * @throws IncorrectInputException
     */
    public function register(Request $request, UserModel $model) {

        $loginColumnName = 'email';

        $login = $request->get('login', '', 'string');
        $password = $request->get('password', '', 'string');
        $confirmPassword = $request->get('confirm_password', '', 'string');

        if(!empty($login) && filter_var($login, FILTER_VALIDATE_EMAIL)) {

            foreach ($model->getList( $loginColumnName ) as $value) {
                if ($value->{$loginColumnName} === $login) {
                    throw new IncorrectInputException("This e-mail address is already registered.");
                }
            }

            if($password === $confirmPassword) {
                if(!empty($password) && strlen($password) > 3 && strlen($password) < 17) {

                    $model->create( array($loginColumnName => $login,
                        'password' => md5($password), 'token' => md5(uniqid()) );

                } else {
                    throw new IncorrectInputException("Password length should be between 4 and 16 symbols.");
                }

            } else {
                throw new IncorrectInputException("Passwords do not match.");
            }

        } else {
            throw new IncorrectInputException("Please, provide a correct e-mail address.");
        }
    }

    /**
     * Login through action
     *
     * @param Request $request
     * @param UserModel $model
     *
     * @return mixed
     * @throws AuthRequiredException
     */
    public function login(Request $request, UserModel $model) {

        if($login = $request->get('login', '', 'string')) {

            $user = $model->findByCredentials($login, $request->get('password', ''));
        }

        if(empty($user)) {
            throw new AuthRequiredException('Bad access credentials provided');
        }

        // Generate new access token and save:
        $user->token = md5(uniqid());
        $user->save((int)$user->id, 'token', (string)$user->token);

        return $user->token;
    }

    /**
     * Logout
     *
     * @param Request $request
     */
    public function logout(Request $request) {
        $request->headers['X-Auth'] = null;
    }
}