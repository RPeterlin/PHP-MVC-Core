<?php

namespace rokpeterlin\phpmvc;

use rokpeterlin\phpmvc\db\Database;
use rokpeterlin\phpmvc\db\DbModel;

class Application
{

  public Router $router;
  public Request $request;
  public Response $response;
  public string $layout = 'main';
  public ?Controller $controller = null;
  public Session $session;
  public Database $db;
  public ?DbModel $user; //? because it might be null
  public string $userClass; // We specify what the default userClass is in index.php
  public View $view;

  public static Application $app;
  // Save the rootPath as a static property of the Application
  public static string $ROOT_DIR;

  public function __construct($rootPath, array $config)
  {
    self::$ROOT_DIR = $rootPath;
    self::$app = $this;
    // $this->request = new \rokpeterlin\phpmvc\Request();
    $this->request = new Request();
    $this->response = new Response();
    $this->session = new Session();
    $this->router = new Router($this->request, $this->response);
    $this->view = new View();

    // $config may contain config for not only database but some other things as well. So we take a subarray for the 'db' key.
    $this->db = new Database($config['db']);
    $this->userClass = $config['userClass'];

    // 'primaryKey' is the name of the column in the table (id), while 'primaryValue' is the actual identifier (integer).
    $primaryValue = $this->session->get('user');
    if ($primaryValue) {
      $user = new $this->userClass();
      $primaryKey = $user->primaryKey();
      $this->user = $user->findOne([$primaryKey => $primaryValue]);
    } else {
      $this->user = null;
    }
  }

  public function run()
  {
    try {
      echo $this->router->resolve();
    } catch (\Exception $e) {
      $this->response->setStatusCode($e->getCode());
      echo $this->view->renderView('_error', ['exception' => $e]);
    }
  }

  public function getController()
  {
    return $this->controller;
  }

  public function setController(Controller $controller)
  {
    $this->controller = $controller;
  }


  public function login(DbModel $user)
  {
    $this->user = $user;
    $primaryKey = $user->primaryKey();
    $primaryValue = $user->{$primaryKey};
    $this->session->set('user', $primaryValue);
    return true;
  }

  public function logout()
  {
    $this->user = null;
    $this->session->remove('user');
    return true;
  }
}
