<?php

namespace Mindk\Framework\Controllers;

use Mindk\Framework\Exceptions\AuthRequiredException;
use Mindk\Framework\Http\Request\Request;
use Mindk\Framework\Models\UserModel;
use Mindk\Framework\DB\DBOConnectorInterface;
use Mindk\Framework\Http\Response\JsonResponse;

/**
 * Class UserController
 * @package Mindk\Framework\Controllers
 */
class UserController
{
    public function register(Request $request, UserModel $model) {
        print_r('test11');
        $errors = [];
        $email = null;
        $password = null;
        if($request->get('email', '', 'email')){
            if(!empty($model->findByEmail($request->get('email', '', 'email')))){
                $email = $request->get('email', '', 'email');
            }
            else {
                array_push($errors, ['email' => 'Email already exists']);
            }
        }
        if(strlen($request->get('password', '')) > 5 && $request->get('password', '') === $request->get('confirm_password', '')){
            $password = $request->get('password', '');
        }
        elseif(strlen($request->get('password', '')) <= 5){
            array_push($errors, ['password' => 'password must be at least 6 characters']);
        }
        else {
            array_push($errors, ['password' => 'passwords do not match']);
        }

        $status = null;
        if(empty($errors)){
            $model->create(['email' => $email, 'password' => password_hash($password, PASSWORD_DEFAULT)]);
            $status = 'success';
        }
        else {
            $status = $errors;
        }
        $response = new JsonResponse($status);
        $response->send();

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
    public function login(Request $request, UserModel $model, DBOConnectorInterface $dbo) {

        if($login = $request->get('login', '', 'string')) {

            $user = $model->findByCredentials($login, $request->get('password', ''));
        }

        if(empty($user)) {
            throw new AuthRequiredException('Bad access credentials provided');
        }

        // Generate new access token and save:
        $user->token = md5(uniqid());
        //$user->save();
        //@TODO: REMOVE THIS when UserModel::save() implemented
        $dbo->setQuery("UPDATE `users` SET `token`='".$user->token."' WHERE `id`=".(int)$user->id);

        return $user->token;
    }

    public function logout(Request $request) {
        //@TODO: Implement
    }
}