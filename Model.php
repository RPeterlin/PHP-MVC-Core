<?php


namespace rokpeterlin\phpmvc;


abstract class Model
{

  public const RULE_REQUIRED = 'required';
  public const RULE_EMAIL = 'email';
  public const RULE_MIN = 'min';
  public const RULE_MAX = 'max';
  public const RULE_MATCH = 'match';
  public const RULE_UNIQUE = 'unique';

  abstract public function rules(): array;
  public array $errors = [];

  // Mapping from attribute names to labels on the site
  public function labels()
  {
    return [];
  }

  public function getLabel($attribute)
  {
    return $this->labels()[$attribute] ?? $attribute;
  }

  public function loadData($data)
  {
    foreach ($data as $key => $value) {
      if (property_exists($this, $key)) {
        $this->{$key} = $value;
      }
    }
  }


  public function validate()
  {
    // Each model that extends 'Model' has some rules. 'validate' method goes through the rules and checks them.

    foreach ($this->rules() as $attribute => $rules) {
      // $rules is an array of rules
      $value = $this->{$attribute};
      foreach ($rules as $rule) {
        $ruleName = $rule;
        if (is_array($ruleName)) {
          $ruleName = $rule[0];
        }
        if ($ruleName === self::RULE_REQUIRED && !$value) {
          $this->addErrorForRule($attribute, self::RULE_REQUIRED);
        }
        if ($ruleName === self::RULE_EMAIL && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
          $this->addErrorForRule($attribute, self::RULE_EMAIL);
        }
        if ($ruleName === self::RULE_MIN && strlen($value) < $rule['min']) {
          $this->addErrorForRule($attribute, self::RULE_MIN, $rule);
        }
        if ($ruleName === self::RULE_MAX && strlen($value) > $rule['max']) {
          $this->addErrorForRule($attribute, self::RULE_MAX, $rule);
        }
        if ($ruleName === self::RULE_MATCH && !empty($this->{$rule['match']}) && $value !== $this->{$rule['match']}) {
          $this->addErrorForRule($attribute, self::RULE_MATCH, ['match' => $this->getLabel($rule['match'])]);
        }
        if ($ruleName === self::RULE_UNIQUE) {
          $className = $rule['class'];

          // We could also specify: [self::RULE_UNIQUE, 'class' => self::class, 'attribute => 'name']
          $uniqueAttribute = $rule['attribute'] ?? $attribute;
          $tableName = $className::tableName();

          $sql = "SELECT * FROM $tableName WHERE $uniqueAttribute = :attr";
          $statement = Application::$app->db->pdo->prepare($sql);
          $statement->bindValue(":attr", $value);
          $statement->execute();
          $record = $statement->fetchObject();

          if ($record) {
            $this->addErrorForRule($attribute, self::RULE_UNIQUE, ['field' => $this->getLabel($attribute)]);
          }
        }
      }
    }
    return empty($this->errors);
  }

  public function addError($attribute, $message)
  {
    $this->errors[$attribute][] = $message;
  }

  private function addErrorForRule($attribute, $rule, $params = [])
  {
    $message = $this->errorMessages()[$rule] ?? '';
    foreach ($params as $key => $value) {
      $message = str_replace("{{$key}}", $value, $message);
    }
    $this->errors[$attribute][] = $message;
  }

  public function errorMessages()
  {
    return [
      self::RULE_REQUIRED => "This field is required",
      self::RULE_EMAIL => "This field must be a valid email address",
      self::RULE_MIN => "Minimum length of this field must be {min}",
      self::RULE_MAX => "Maximum length of this field must be {max}",
      self::RULE_MATCH => "This field must be the same as {match}",
      self::RULE_UNIQUE => "Record with this {field} already exists",
    ];
  }

  public function hasError($attribute)
  {
    return $this->errors[$attribute] ?? false;
  }

  public function getFirstError($attribute)
  {
    return $this->errors[$attribute][0] ?? false;
  }
}
