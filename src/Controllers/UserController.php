<?php

namespace Mindk\Framework\Controllers;

use Mindk\Framework\Exceptions\AuthRequiredException;
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
     * @var string  DB Table standard keys
     */
    protected $loginName = 'login';
    protected $passwordName = 'password';
    protected $tokenName = 'auth_token';

    /**
     * Register through action
     *
     * @param Request $request
     * @param UserModel $model
     */
    public function register(Request $request, UserModel $model) {

        $errors = [];

        $login = $request->get($this->loginName, '', 'string');
        $password = $request->get($this->passwordName, '', 'string');
        $confirmPassword = $request->get('confirm_' . $this->passwordName, '', 'string');


        if(!empty($login) && filter_var($login, FILTER_VALIDATE_EMAIL)) {

            foreach ($model->getList( $this->loginName ) as $value) {

                if ($value->{$this->loginName} === $login) {
                    $errors["$this->loginName"] = 'This e-mail address is already registered.';
                    break;
                }
            }

            if($password === $confirmPassword) {

                if(!empty($password) && strlen($password) > 5 && strlen($password) < 17) {

                    $token = md5(uniqid());

                    $model->create( array($this->loginName => $login,
                        $this->passwordName => md5($password), $this->tokenName => $token) );

                } else {
                    $errors["$this->passwordName"] = 'Password length should be between 6 and 16 symbols.';
                }

            } else {
                $errors["$this->passwordName"] = 'Passwords do not match.';
            }

        } else {
            $errors["$this->loginName"] = 'Please, provide a correct e-mail address.';
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
        $user->{$this->tokenName} = md5(uniqid());
        $user->save();

        $response = new JsonResponse(null);
        $response->setHeader('X-Auth', $user->{$this->tokenName});
        $response->send();

        //return $user->{$this->tokenName};
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