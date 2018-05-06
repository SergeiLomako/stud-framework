<?php

namespace Mindk\Framework\Middleware;

use Mindk\Framework\Auth\AuthService;
use Mindk\Framework\Http\Response\JsonResponse;
use Optimus\Onion\LayerInterface;

/**
 * Class ACL Route Middleware
 * @package Mindk\Framework\Middleware
 */
class ACL implements LayerInterface
{
    /**
     * Handler
     *
     * @param $object
     * @param Closure $next
     */
    public function peel($object, \Closure $next){
        // Assuming $object is route object:
        $acl = $object->acl;

        if(!empty($acl)) {
            if(!AuthService::checkRoles($object->acl)){
                return new JsonResponse('Access denied', 403);
            }
        }

        return $next($object);
    }
}