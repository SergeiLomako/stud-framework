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
    //protected $defaultRoleId = 2;

    /**
     * Register through action
     *
     * @param Request $request
     * @param UserModel $model
     *
     * @return JsonResponse
     * @throws \Mindk\Framework\Exceptions\ModelException
     */
    public function register(Request $request, UserModel $model) {

        $errors = [];

        $login = $request->get($model::LOGIN_NAME, '', 'string');
        $password = $request->get($model::PASSWORD_NAME, '', 'string');
        $confirmPassword = $request->get('confirm_' . $model::PASSWORD_NAME, '', 'string');


        if(!empty($login) && filter_var($login, FILTER_VALIDATE_EMAIL)) {

            foreach ($model->getList( $model::LOGIN_NAME ) as $value) {

                if ($value->{$model::LOGIN_NAME} === $login) {
                    $errors[$model::LOGIN_NAME] = 'This e-mail address is already registered.';
                    break;
                }
            }

            if($password === $confirmPassword) {

                if(!empty($password) && strlen($password) > 5 && strlen($password) < 17) {

                    $token = md5(uniqid());

                    $model->create( array($model::LOGIN_NAME => $login,
                        $model::PASSWORD_NAME => md5($password), $model::TOKEN_NAME => $token) );

                } else {
                    $errors[$model::PASSWORD_NAME] = 'Password length should be between 6 and 16 symbols.';
                }

            } else {
                $errors[$model::PASSWORD_NAME] = 'Passwords do not match.';
            }

        } else {
            $errors[$model::LOGIN_NAME] = 'Please, provide a correct e-mail address.';
        }


        $response = new JsonResponse($errors);

        if (!empty($token)) {
            $response->setHeader('X-Auth', $token);
        }

        return $response;
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
        $user->{$model::TOKEN_NAME} = md5(uniqid());
        $user->save();

        $response = new JsonResponse(null);
        $response->setHeader('X-Auth', $user->{$model::TOKEN_NAME});

        return $response;
    }

    /**
     * Logout
     *
     * @param Request $request
     * @param UserModel $model
     */
    public function logout(Request $request, UserModel $model) {

        if ( $user = $model->findByToken($request->headers['X-Auth']) ) {
            $user->{$model::TOKEN_NAME} = '';
            $model->clearValue( $user->{$model::PRIMARY_KEY}, $model::TOKEN_NAME );
        }

        $request->headers['X-Auth'] = null;
    }
}