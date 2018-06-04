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
     * @return JsonResponse
     * @throws \Mindk\Framework\Exceptions\ModelException
     */
    public function register(Request $request, UserModel $model, Validation $validation, DBOConnectorInterface $db) {
        $rules = ['login' => 'required|email|unique:users:email',
                  'password' => 'required|min:6|confirmed'];
        $errors = $validation->validate($request, $rules, $db);
        $status = null;
        $code = 200;
        if (!is_array($errors)) {
            $token = md5(uniqid());
            $model->create(['login' => $request->get('login', null, 'string'),
                            'password' => md5($request->get('password', null, 'string')), 'token' => $token]);
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
        $user = $model->findByToken($request->getHeader('X-Auth'));
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