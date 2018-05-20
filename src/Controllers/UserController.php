<?php

namespace Mindk\Framework\Controllers;

use Mindk\Framework\Exceptions\AuthRequiredException;
// use Mindk\Framework\Exceptions\IncorrectInputException;
use Mindk\Framework\Http\Request\Request;
use Mindk\Framework\Models\UserModel;
use Mindk\Framework\Http\Response\JsonResponse;

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
     */
    public function register(Request $request, UserModel $model) {

        $loginColumnName = 'email';
        $errors = [];

        $login = $request->get('login', '', 'string');
        $password = $request->get('password', '', 'string');
        $confirmPassword = $request->get('confirm_password', '', 'string');



        if(!empty($login) && filter_var($login, FILTER_VALIDATE_EMAIL)) {

            foreach ($model->getList( $loginColumnName ) as $value) {
                if ($value->{$loginColumnName} === $login) {
                    $errors['email'] = 'This e-mail address is already registered.';
                    break;
                }
            }

            if($password === $confirmPassword) {
                if(!empty($password) && strlen($password) > 3 && strlen($password) < 17) {

                    $token = md5(uniqid());

                    $model->create( array($loginColumnName => $login,
                        'password' => md5($password), 'token' => $token) );

                } else {
                    $errors['password'] = 'Password length should be between 4 and 16 symbols.';
                }

            } else {
                $errors['password'] = 'Passwords do not match.';
            }

        } else {
            $errors['email'] = 'Please, provide a correct e-mail address.';
        }



        $response = new JsonResponse($errors);

        if (!empty($token)) {
            $response->setHeader('X-Auth', $token);
        }

        $response->send();
    }

    /**
     * Login through action
     *
     * @param Request $request
     * @param UserModel $model
     *
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
        $user->save();

        $response = new JsonResponse(null);
        $response->setHeader('X-Auth', $user->token);
        $response->send();

        //return $user->token;
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