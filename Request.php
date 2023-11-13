<?php

namespace rokpeterlin\phpmvc;

class Request
{
  public function getPath()
  {
    $path = $_SERVER['REQUEST_URI'] ?? '/';

    // Check if path contains any query parameters
    $position = strpos($path, '?');

    // If there is no '?', return path
    if (!$position) {
      return $path;
    }
    $path = substr($path, 0, $position);
    return $path;
  }

  public function method()
  {
    return strtolower($_SERVER['REQUEST_METHOD']);
  }
  public function isGet()
  {
    return $this->method() === 'get';
  }
  public function isPost()
  {
    return $this->method() === 'post';
  }

  public function getBody()
  {

    $body = [];

    // Sanitize data
    if ($this->method() === 'get') {
      foreach ($_GET as $key => $value) {
        $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
      }
    }
    if ($this->method() === 'post') {
      foreach ($_POST as $key => $value) {
        $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
      }
    }


    return $body;
  }
}
