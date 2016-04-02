<?php

namespace Test\Integration;

use Asd\{Asd, Controller};
use Asd\Http\{Request, Response, Uri};
use Asd\Router\{Router, Route};

class MyController extends Controller
{
  public function jsonAction()
  {
    $this->json('Hello World!');
  }

  public function textAction()
  {
    $this->response->getBody()->write('Hello World!');
  }
}

class AsdTest extends \PHPUnit_Framework_TestCase
{
  
  public function setUp()
  {
    $router = new Router();
    $uri = new Uri();
    $uri = $uri->withPath('path');
    $request = new Request('GET', $uri);
    $response = new Response();
    $this->app = new Asd($router, $request, $response);
    $this->app->addRoute(new Route('GET', 'path', 'Test\Integration\MyController@jsonAction'));
  }

  /**
   * @test
   * @covers Asd\Asd::run
   * @covers Asd\Asd::dispatch
   */
  public function dispatch()
  {
    $this->app->run();
    $this->expectOutputString(json_encode('Hello World!'));
  }
}