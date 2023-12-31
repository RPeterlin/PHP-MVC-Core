<?php


namespace rokpeterlin\phpmvc;

use rokpeterlin\phpmvc\Application;
use rokpeterlin\phpmvc\middlewares\BaseMiddleware;

class Controller
{
  public string $layout = 'main';
  public string $action = '';
  protected array $middlewares = [];


  public function render($view, $params = [])
  {
    return Application::$app->view->renderView($view, $params);
  }

  public function setLayout($layout)
  {
    $this->layout = $layout;
  }

  public function getMiddlewares()
  {
    return $this->middlewares;
  }

  public function registerMiddleware(BaseMiddleware $middleware)
  {
    // Syntax for array-push
    $this->middlewares[] = $middleware;
  }
}
