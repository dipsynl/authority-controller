<?php

use Mockery as m;

class AcParametersTest extends AcTestCase
{
    use AuthorityControllerHelpers;

    public function setUp()
    {
        parent::setUp();

        $this->app = $this->app = App::getFacadeRoot();
        $this->app['router'] = $this->router = $this->mockRouter();

        $this->controllerName = "ProjectsController";
        $this->router->resource('projects', $this->controllerName);

        $this->parameters = new \Efficiently\AuthorityController\Parameters;
        $this->app->instance('Params', $this->parameters);
    }

    public function testAddParameter()
    {
        Params::add('key', 'value');
        $this->assertEquals(Params::get('key'), 'value');
    }

    public function testAddParameterWithDotKeys()
    {
        Params::add('key.subkey', 'value');
        $this->assertEquals(Params::get('key.subkey'), 'value');
    }

    public function testOnlyParameters()
    {
        Params::add('key1', 'value1');
        Params::add('key2', 'value2');
        $this->assertEquals(Params::only('key1'), ['key1' => 'value1']);
    }

    public function testExceptParameters()
    {
        Params::add('key1', 'value1');
        Params::add('key2', 'value2');
        $this->assertEquals(Params::except('key2'), ['key1' => 'value1']);
    }

    public function testExtractResourceFromInput()
    {
        $input = ['project' => ['name' => 'foo']];
        $parameters = $this->parameters;
        $controller = $this->mockController();

        $this->call('POST', '/projects', $input);// store action

        $this->assertArrayHasKey('project', $this->getProperty($parameters, 'params'));
        $this->assertEquals($this->getProperty($parameters, 'params')['project'], $input['project']);

        $this->assertArrayHasKey('project', $this->getProperty($controller, 'params'));
        $this->assertEquals($this->getProperty($controller, 'params')['project'], $input['project']);
    }

    public function testResolveResourceFromInput()
    {
        $input = ['name' => 'foo'];
        $parameters = $this->parameters;
        $controller = $this->mockController();

        $this->call('POST', '/projects', $input);// store action

        $this->assertArrayHasKey('project', $this->getProperty($parameters, 'params'));
        $this->assertEquals($this->getProperty($parameters, 'params')['project'], $input);

        $this->assertArrayHasKey('project', $this->getProperty($controller, 'params'));
        $this->assertEquals($this->getProperty($controller, 'params')['project'], $input);
    }

    public function testExtractResourceFromInputWithSingularControllerAndRoute()
    {
        $input = ['project' => ['name' => 'foo']];
        $parameters = $this->parameters;

        $controllerName = "ProjectController";
        Route::resource('project', $controllerName);
        $controller = $this->mockController($controllerName);

        $this->call('POST', '/project', $input);// store action

        $this->assertArrayHasKey('project', $this->getProperty($parameters, 'params'));
        $this->assertEquals($this->getProperty($parameters, 'params')['project'], $input['project']);

        $this->assertArrayHasKey('project', $this->getProperty($controller, 'params'));
        $this->assertEquals($this->getProperty($controller, 'params')['project'], $input['project']);
    }

    public function testResolveResourceFromInputWithSingularControllerAndRoute()
    {
        $input = ['name' => 'foo'];
        $parameters = $this->parameters;

        $controllerName = "ProjectController";
        Route::resource('project', $controllerName);
        $controller = $this->mockController($controllerName);

        $this->call('POST', '/project', $input);// store action

        $this->assertArrayHasKey('project', $this->getProperty($parameters, 'params'));
        $this->assertEquals($this->getProperty($parameters, 'params')['project'], $input);

        $this->assertArrayHasKey('project', $this->getProperty($controller, 'params'));
        $this->assertEquals($this->getProperty($controller, 'params')['project'], $input);
    }

    public function testResolveActionAndControllerNamesFromRequest()
    {
        $input = ['project' => ['name' => 'foo']];
        $parameters = $this->parameters;
        $controller = $this->mockController();

        $this->call('POST', '/projects', $input);// store action

        $this->assertArrayHasKey('action', $this->getProperty($parameters, 'params'));
        $this->assertEquals($this->getProperty($parameters, 'params')['action'], 'store');

        $this->assertArrayHasKey('controller', $this->getProperty($parameters, 'params'));
        $this->assertEquals($this->getProperty($parameters, 'params')['controller'], 'projects');


        $this->assertArrayHasKey('action', $this->getProperty($controller, 'params'));
        $this->assertEquals($this->getProperty($controller, 'params')['action'], 'store');

        $this->assertArrayHasKey('controller', $this->getProperty($controller, 'params'));
        $this->assertEquals($this->getProperty($controller, 'params')['controller'], 'projects');
    }

    public function testResolveResourceIdFromRequest()
    {
        $parameters = $this->parameters;
        $controller = $this->mockController();

        $this->call('GET', '/projects/5');// show action

        $this->assertArrayHasKey('id', $this->getProperty($parameters, 'params'));
        $this->assertEquals($this->getProperty($parameters, 'params')['id'], '5');

        $this->assertArrayHasKey('id', $this->getProperty($controller, 'params'));
        $this->assertEquals($this->getProperty($controller, 'params')['id'], '5');
    }

    public function testResolveResourceAndParentResourceIdsFromRequest()
    {
        $parameters = $this->parameters;

        $controllerName = "TasksController";
        Route::resource('projects.tasks', $controllerName);
        $controller = $this->mockController($controllerName);

        $this->call('GET', '/projects/5/tasks/2');// show action of task resource

        $this->assertArrayHasKey('project_id', $this->getProperty($parameters, 'params'));
        $this->assertEquals($this->getProperty($parameters, 'params')['project_id'], '5');

        $this->assertArrayHasKey('project_id', $this->getProperty($controller, 'params'));
        $this->assertEquals($this->getProperty($controller, 'params')['project_id'], '5');


        $this->assertArrayHasKey('id', $this->getProperty($parameters, 'params'));
        $this->assertEquals($this->getProperty($parameters, 'params')['id'], '2');

        $this->assertArrayHasKey('id', $this->getProperty($controller, 'params'));
        $this->assertEquals($this->getProperty($controller, 'params')['id'], '2');
    }

    public function testResolveResourceIdFromRequestWithSingularController()
    {
        $parameters = $this->parameters;

        $controllerName = "ProjectController";
        Route::resource('projects', $controllerName);
        $controller = $this->mockController($controllerName);

        $this->call('GET', '/projects/6');// show action

        $this->assertArrayHasKey('id', $this->getProperty($parameters, 'params'));
        $this->assertEquals($this->getProperty($parameters, 'params')['id'], '6');

        $this->assertArrayHasKey('id', $this->getProperty($controller, 'params'));
        $this->assertEquals($this->getProperty($controller, 'params')['id'], '6');
    }

    public function testResolveResourceIdFromRequestWithSingularRoute()
    {
        $parameters = $this->parameters;

        Route::resource('project', $this->controllerName);
        $controller = $this->mockController();

        $this->call('GET', '/projects/7');// show action

        $this->assertArrayHasKey('id', $this->getProperty($parameters, 'params'));
        $this->assertEquals($this->getProperty($parameters, 'params')['id'], '7');

        $this->assertArrayHasKey('id', $this->getProperty($controller, 'params'));
        $this->assertEquals($this->getProperty($controller, 'params')['id'], '7');
    }

    public function testResolveResourceIdFromRequestWithSingularControllerAndRoute()
    {
        $parameters = $this->parameters;

        $controllerName = "ProjectController";
        Route::resource('project', $controllerName);
        $controller = $this->mockController($controllerName);

        $this->call('GET', '/project/8');// show action

        $this->assertArrayHasKey('id', $this->getProperty($parameters, 'params'));
        $this->assertEquals($this->getProperty($parameters, 'params')['id'], '8');

        $this->assertArrayHasKey('id', $this->getProperty($controller, 'params'));
        $this->assertEquals($this->getProperty($controller, 'params')['id'], '8');
    }

    public function testResolveResourceAndParentResourceIdsFromRequestWithSingularControllerAndRoute()
    {
        $parameters = $this->parameters;

        $controllerName = "TaskController";
        Route::resource('project.task', $controllerName);
        $controller = $this->mockController($controllerName);

        $this->call('GET', '/project/5/task/2');// show action of task resource

        $this->assertArrayHasKey('project_id', $this->getProperty($parameters, 'params'));
        $this->assertEquals($this->getProperty($parameters, 'params')['project_id'], '5');

        $this->assertArrayHasKey('project_id', $this->getProperty($controller, 'params'));
        $this->assertEquals($this->getProperty($controller, 'params')['project_id'], '5');


        $this->assertArrayHasKey('id', $this->getProperty($parameters, 'params'));
        $this->assertEquals($this->getProperty($parameters, 'params')['id'], '2');

        $this->assertArrayHasKey('id', $this->getProperty($controller, 'params'));
        $this->assertEquals($this->getProperty($controller, 'params')['id'], '2');
    }

    protected function mockController($controllerName = null)
    {
        $controllerName = $controllerName ?: $this->controllerName;

        $this->router = $this->app['router'];

        $events = $this->getProperty($this->router, 'events');
        $this->setProperty($this->router, 'events', $events);

        $this->app['router'] = $this->router;

        $this->mock($controllerName);
        $controllerInstance = $this->app->make($controllerName);
        $controllerInstance->shouldReceive('paramsBeforeFilter')->with(m::type('string'))->once();

        $dispatcher = $this->router->getControllerDispatcher();
        $dispatcher->shouldReceive('dispatch')
          ->with(m::type('\Illuminate\Routing\Route'), m::type('\Illuminate\Http\Request'), $controllerName, m::type('string'))
            ->once()->andReturnUsing(function ($route, $request, $controller, $method) use ($dispatcher, $controllerInstance) {

                $this->app->make('Params')->fillController($controllerInstance);

                $filterName = "router.filter: controller.parameters.".get_classname($controllerInstance);
                $this->assertTrue(Event::hasListeners($filterName));
                Event::fire($filterName);

                return new \Symfony\Component\HttpFoundation\Response;
        });

        $this->mock('\Efficiently\AuthorityController\ControllerResource');
        $this->controllerResource = $this->app->make('\Efficiently\AuthorityController\ControllerResource');
        $this->controllerResource->shouldReceive('getNameByController')->with('ProjectsController')->andReturn('project');

        return $controllerInstance;
    }

    protected function mockRouter($app = null)
    {
        $app = $app ?: $this->app;
        $routerFacade = new \Illuminate\Support\Facades\Route;
        $this->invokeMethod($routerFacade, 'createFreshMockInstance', ['router']);
        $router = $routerFacade::getFacadeRoot()->makePartial();

        $this->setProperty($router, 'events', $app['events']);
        $this->setProperty($router, 'routes', new \Illuminate\Routing\RouteCollection);
        $this->setProperty($router, 'container', $app);

        $this->mock("\Illuminate\Routing\ControllerDispatcher");
        $dispatcher = $app->make("\Illuminate\Routing\ControllerDispatcher");
        $this->setProperty($dispatcher, 'filterer', $router);
        $this->setProperty($dispatcher, 'container', $app);

        $router->setControllerDispatcher($dispatcher);

        return $router;
    }

}
