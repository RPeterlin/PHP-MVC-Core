<?php


namespace rokpeterlin\phpmvc;


class View
{

  public string $title = '';


  public function renderView($view, $params = [])
  {
    $viewContent = $this->viewContent($view, $params); //renderOnlyView
    $layoutContent = $this->layoutContent();
    return str_replace('{{content}}', $viewContent, $layoutContent);
  }

  protected function layoutContent()
  {
    $layout = Application::$app->layout;
    // Override the default layout (which is 'main'), if the controller was specified in index.php
    if (Application::$app->controller) {
      $layout = Application::$app->controller->layout;
    }
    // start output caching. On ob_start nothing is output to the browser
    ob_start();
    include_once Application::$ROOT_DIR . "/views/layouts/$layout.php";
    // return what is already buffered and clear the buffer
    return ob_get_clean();
  }

  protected function viewContent($view, $params)
  {
    foreach ($params as $key => $value) {
      // $key evaluates to $name for example. So then we have $($key) = $value which is $(name) = $value or $name = $value. We have just defined $name in this scope.
      $$key = $value;
    }
    // start output caching. On ob_start nothing is output to the browser
    ob_start();
    // The include sees the variables defined with foreach above.
    include_once Application::$ROOT_DIR . "/views/$view.php";
    // return what is already buffered and clear the buffer
    return ob_get_clean();
  }
}
