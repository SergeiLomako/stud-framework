<?php

namespace Mindk\Framework\Controllers;

use Mindk\Framework\Exceptions\AuthRequiredException;
use Mindk\Framework\Http\Request\Request;
use Mindk\Framework\Models\UserModel;
use Mindk\Framework\Http\Response\JsonResponse;
use Mindk\Framework\Validation\Validation;
use Mindk\Framework\DB\DBOConnectorInterface;

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
     * @param Validation $validation
     * @param DBOConnectorInterface $db
     * @return JsonResponse
     * @throws \Mindk\Framework\Exceptions\ModelException
     * @throws \Mindk\Framework\Exceptions\ValidationException
     */
    public function register(Request $request, UserModel $model, Validation $validation, DBOConnectorInterface $db) {
        $rules = ['login' => 'required|email|unique:users:login',
                  'password' => 'required|min:6|confirmed',
                  'first_name' => 'required|min:2|max:30',
                  'last_name' => 'required|min:2|max:30',
                  'phone' => 'required'];
        $errors = $validation->validate($request, $rules, $db);
        $status = null;
        $code = 200;
        if (!is_array($errors)) {
            $token = md5(uniqid());
            $model->create(['login' => $request->get('login', null, 'string'),
                            'password' => md5($request->get('password', null, 'string')),
                            'first_name' => $request->get('first_name', null, 'string'),
                            'last_name' => $request->get('last_name', null, 'string'),
                            'phone' => $request->get('phone', null, 'int'),
                            'token' => $token]);
            $status = ['token' => $token];
        } else {
            $status = $errors;
            $code = 400;
        }

        return new JsonResponse($status, $code);
    }

    /**
     * Login through action
     *
     * @param Request $request
     * @param UserModel $model
     * @return string
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

        return $user->token;
    }

    /**
     * Logout
     *
     * @param Request $request
     * @param UserModel $model
     * @return JsonResponse
     */
    public function logout(Request $request, UserModel $model) {
        $user = $model->findByToken($request->get('token', '', 'string'));
        $body = 'You are not logged in';
        $code = 500;
        if($user){
            $user->token = md5(uniqid());;
            $user->save();
            $body = 'Logout';
            $code = 200;
        }
        return new JsonResponse($body, $code);
    }
}