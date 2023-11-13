<?php


namespace app\core\middlewares;

use app\core\Application;
use app\core\exception\ForbiddenException;


class AuthMiddleware extends BaseMiddleware
{
  public array $actions;


  public function __construct(array $actions = [])
  {
    $this->actions = $actions;
  }


  public function execute()
  {
    // If user isn't logged in
    if (!Application::$app->user) {
      // If either the actions array is empty or the current action is in the actions, then the action is restricted
      if (empty($this->actions) || in_array(Application::$app->controller->action, $this->actions)) {
        throw new ForbiddenException();
      }
    }
  }
}
