<?php

namespace Mindk\Framework;

require(dirname(__DIR__).'/vendor/autoload.php');

use Mindk\Framework\Exceptions\NotFoundException;
use Mindk\Framework\Middleware\RouteMiddlewareGateway;
use Mindk\Framework\Routing\Route;
use Mindk\Framework\Http\Response\JsonResponse;
use Mindk\Framework\Config\Config;
use Mindk\Framework\DI\Injector;

/**
 * Application class
 */
class App
{
    /**
     * @var array   Config cache
     */
    protected $config = null;

    /**
     * App constructor.
     * @param $config
     */
    public function __construct(array $config)
    {
        $this->config = new Config($config);
        Injector::setConfig($this->config);
    }

    /**
     * Run the app
     */
    public function run(){

        try{
            $router = Injector::make('router', ['mapping' => $this->config->get('routes', []) ] );
            $route = $router->findRoute();
            $middlewareGateway = new RouteMiddlewareGateway($this->config->get('middleware'));

            if($route instanceof Route){
                $response = $middlewareGateway->handle($route, function($object) {
                    return $this->processRoute($object);
                });
            } else {
                throw new NotFoundException('Route not found');
            }
        }
        catch(NotFoundException $e) {
            $response = $e->toResponse();
        }
        catch(\Exception $e) {
            $response = new JsonResponse(['error' => $e->getMessage()], 500);
        }

        // Send final response:
        $response->send();
    }

    /**
     * Process route
     *
     * @param Route $route
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    protected function processRoute(Route $route){

        $controllerReflection = new \ReflectionClass($route->controller);

        if($controllerReflection->hasMethod($route->action)){
            $controller = Injector::make($route->controller);
            $methodReflection = $controllerReflection->getMethod($route->action);

            // Get response from responsible controller:
            $paramset = Injector::resolveParams($methodReflection->getParameters(), $route->params);
            $response = $methodReflection->invokeArgs($controller, $paramset);

        } else {
            throw new \Exception('Bad controller action');
        }

        return $response;
    }
}