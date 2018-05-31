<?php

namespace Mindk\Framework\Middleware;

use Mindk\Framework\DI\Injector;
use Mindk\Framework\Http\Response\Response;
use Mindk\Framework\Http\Response\JsonResponse;
use Optimus\Onion\Onion;
use Mindk\Framework\Routing\Route;

/**
 * Class MiddlewareGateway
 *
 * @package Mindk\Framework\Middleware
 */
class RouteMiddlewareGateway
{
    /**
     * @var array Layers
     */
    protected $layers = [];

    /**
     * RouteMiddlewareGateway constructor
     *
     * @param array $middlawares
     */
    public function __construct($middlawares = []) {

        $this->layers = $middlawares;
    }

    /**
     * Handle subject
     *
     * @param Route $subject
     * @param \Closure $core
     * @return Response
     * @throws \Exception
     */
    public function handle(Route $subject, \Closure $core): Response {

        $onion = new Onion();
        $mw_instances = [];

        if(!empty($this->layers)) {
            foreach($this->layers as $layer) {
                array_push($mw_instances, Injector::make($layer));
            }
        }

        $response = $onion
            ->layer($mw_instances)
            ->peel($subject, $core);

        return  $response;
    }
}