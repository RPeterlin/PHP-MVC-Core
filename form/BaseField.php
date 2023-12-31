<?php

namespace rokpeterlin\phpmvc\form;

use rokpeterlin\phpmvc\Model;

abstract class BaseField
{

  public Model $model;
  public string $attribute;

  abstract public function renderInput(): string;


  public function __construct(Model $model, string $attribute)
  {
    $this->model = $model;
    $this->attribute = $attribute;
  }


  public function __toString()
  {
    // Whenever we try to print (echo) the object, the result of this method will be printed.

    return sprintf(
      '
      <div class="form-group">
        <label for="%s">%s</label>
        %s
        <div class="invalid-feedback"> %s </div>
      </div>
    ',
      $this->attribute,
      $this->model->getLabel($this->attribute),
      $this->renderInput(),
      $this->model->getFirstError($this->attribute)
    );
  }
}
