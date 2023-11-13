<?php


namespace app\core;

use \app\controllers\SiteController;
use app\core\exception\NotFoundException;

class Router
{

  protected array $routes = [];
  public Request $request;
  public Response $response;


  public function __construct(Request $request, Response $response)
  {
    $this->request = $request;
    $this->response = $response;
  }


  public function get($path, $callback)
  {
    $this->routes['get'][$path] = $callback;
  }

  public function post($path, $callback)
  {
    $this->routes['post'][$path] = $callback;
  }

  public function resolve()
  {
    $path = $this->request->getPath();
    $method = $this->request->method();

    // $callback can be either string (view), function or an array (controller)
    if (!isset($this->routes[$method][$path])) {
      throw new NotFoundException();
    }
    $callback = $this->routes[$method][$path];

    // If there is no callback on given route for the given method, return 'Not found'
    if (!$callback) {
      throw new NotFoundException();
    }
    // If callback is a string, we need to serve the corresponding view
    if (is_string($callback)) {
      return
        Application::$app->view->renderView($callback);
    }

    // If $callback is an array, we need to convert the first argument of $callback to an instance of it (SiteController::class -> new SiteController). We can save this instance as the current controller in the Application level.
    if (is_array($callback)) {
      $callback[0] = new $callback[0]();
      Application::$app->controller = $callback[0];
      Application::$app->controller->action = $callback[1];

      foreach (Application::$app->controller->getMiddlewares() as $middleware) {
        $middleware->execute();
      }
    }
    // Then we try to execute the function. If $callback is a function 'call_user_func' will execute it. If it's an array the 'call_user_func' will work in the same way. So 'call_user_func' will try to execute the 'contact' method of the 'SiteController' object that we created in 'if' above.
    return call_user_func($callback, $this->request, $this->response);
  }
}
