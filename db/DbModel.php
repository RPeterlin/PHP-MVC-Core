<?php


namespace app\core\db;

use app\core\Application;
use app\core\Model;

// It will map user's model (class) into database table
abstract class DbModel extends Model
{
  // Returns the name of the table which the corresponding model wants to communicate with
  abstract public function tableName(): string;

  // Return all the column names that we want to save in table with the name of 'tableName'
  abstract public function attributes(): array;

  // Returns the name of the column which serves as PK in the table called 'tableName'
  abstract public function primaryKey(): string;


  public function save()
  {
    // Takes model's attributes and saves in the table name

    $tableName = $this->tableName();

    // We need attributes because not all the fields that are defined in User class (or User class rules) shouldn't be saved in the database. 'pwdConf' is a great example of that. So attributes is a list of columns of the chosen database table.
    $attributes = $this->attributes();

    // Stick ':' in front of each element
    $params = array_map(fn ($x) => ":$x", $attributes);

    // $sql = "INSERT INTO $tableName (name, email...) VALUES (:firstname, :email...)";
    // Doing it like that to prevent SQL injections
    $sql = "INSERT INTO $tableName 
      (" . implode(',', $attributes) . ") VALUES 
      (" . implode(',', $params) . ")";

    $statement = self::prepare($sql);
    foreach ($attributes as $attribute) {
      $statement->bindValue(":$attribute", $this->{$attribute});
    }

    $statement->execute();
    return true;
  }

  public static function prepare($sql)
  {
    // Shortcut
    return Application::$app->db->pdo->prepare($sql);
  }

  public function findOne($where) // [email => 'a@a.com', name => 'yes']
  {
    // We can't say self::tableName because tableName is an abstract value. We can use static:: and that corresponds to the class on which 'findOne' will be called. If we say User::findOne(...), the tableName of User will be used.
    $tableName = static::tableName();
    $attributes = array_keys($where);
    // SELECT * FROM $tableName WHERE email = :email AND name = :name;
    $sql = "SELECT * FROM $tableName WHERE " . implode('AND', array_map(fn ($x) => "$x = :$x", $attributes));
    $statement = self::prepare($sql);

    foreach ($where as $key => $item) {
      $statement->bindValue(":$key", $item);
    }
    $statement->execute();

    // Fetch object returns an object by default, but it can also return an instance of the class on which 'findOne' is called.
    return $statement->fetchObject($class = \app\models\User::class);
  }
}
