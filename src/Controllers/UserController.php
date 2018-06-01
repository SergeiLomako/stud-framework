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
     * Register through action
     *
     * @param Request $request
     * @param UserModel $model
     * @return array|string
     * @throws \Mindk\Framework\Exceptions\ModelException
     */
    public function register(Request $request, UserModel $model) {
        $errors = [];
        if ($request->get('login', '', 'email')) {
            if (empty($model->findByEmail($request->get('login', '', 'email')))) {
                $login = $request->get('login', '', 'email');
            } else {
                array_push($errors, [$model::LOGIN_NAME => 'Email already exists']);
            }
        } else {
            $errors[$model::LOGIN_NAME] = 'Incorrect email';
        }
        if (strlen($request->get('password', '')) > 5 && $request->get('password', '') === $request->get('confirm_password', '')) {
            $password = $request->get('password', '');
        } elseif (strlen($request->get('password', '')) <= 5) {
            array_push($errors, [$model::PASSWORD_NAME => 'Password must be at least 6 characters']);
        } else {
            array_push($errors, [$model::PASSWORD_NAME => 'Passwords do not match']);
        }

        $status = null;
        $code = 200;
        if (empty($errors)) {
            $token = md5(uniqid());
            $model->create([$model::LOGIN_NAME => $login, $model::PASSWORD_NAME => md5($password), $model::TOKEN_NAME => $token]);
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
        $user->{$model::TOKEN_NAME} = md5(uniqid());
        $user->save();

        return $user->{$model::TOKEN_NAME};
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
        $body = 'Something went wrong';
        $code = 500;
        if($user){
            $user->{$model::TOKEN_NAME} = 0;
            $user->save();
            $body = 'Logout';
            $code = 200; 
        }
        return new JsonResponse($body, $code);
    }
}