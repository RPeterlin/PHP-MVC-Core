<?php

namespace app\core\form;

use app\core\Model;

class Form
{
  public static function begin($action, $method)
  {
    echo sprintf('<form action="%s" method="%s">', $action, $method);
    return new Form();
  }
  public static function end()
  {
    echo '</form>';
  }
  public function inputField(Model $model, $attribute)
  {
    return new inputField($model, $attribute);
  }

  public function textAreaField(Model $model, $attribute)
  {
    return new TextareaField($model, $attribute);
  }
}
