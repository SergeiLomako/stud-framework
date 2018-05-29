<?php

namespace Mindk\Framework\Middleware;

use Mindk\Framework\Http\Request\Request;
use Mindk\Framework\Http\Response\Response;
use Optimus\Onion\LayerInterface;

/**
 * Class ACL Route Middleware
 * @package Mindk\Framework\Middleware
 */
class CORS implements LayerInterface
{
    protected $request;

    /**
     * CheckOptions constructor.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handler
     *
     * @param $object
     * @param \Closure $next
     * @return Response|mixed
     */
    public function peel($object, \Closure $next){

        if($this->request->getMethod() == 'OPTIONS'){
            $response = new Response('', 204);
        } else {
            $response = $next($object);
        }

        $response->setHeader('Access-Control-Allow-Origin', 'http://front.loc:82');
        $response->setHeader('Access-Control-Allow-Credentials', 'true');
        $response->setHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Origin, Authorization, Accept, Client-Security-Token, Accept-Encoding, APIToken, APIKey');
        $response->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, DELETE, PUT, PATCH');

        return $response;
    }
}