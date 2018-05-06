<?php

namespace Mindk\Framework\Middleware;

use Mindk\Framework\Auth\AuthService;
use Mindk\Framework\Http\Request\Request;
use Mindk\Framework\Models\UserModel;
use Optimus\Onion\LayerInterface;

/**
 * Class Auth Route Middleware
 * @package Mindk\Framework\Middleware
 */
class Auth implements LayerInterface
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var UserModel
     */
    protected $userModel;

    /**
     * Auth constructor.
     * @param Request $request
     */
    public function __construct(Request $request, UserModel $model)
    {
        $this->request = $request;
        $this->userModel = $model;
    }

    /**
     * Authorize request if access token provided
     *
     * @param $object
     * @param Closure $next
     *
     * @return mixed
     */
    public function peel($object, \Closure $next){
        // Assuming $object is route object:
        if($token = $this->request->getHeader('X-Auth')){
            if($user = $this->userModel->findByToken($token)){
                AuthService::setUser($user);
            }
        }

        return $next($object);
    }
}