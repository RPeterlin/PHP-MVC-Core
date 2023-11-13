<?php


namespace app\core\db;

use app\core\Application;

class Database
{
  public \PDO $pdo;


  public function __construct(array $config)
  {

    //dsn -> domain service name (defines host, port and db)
    $dsn = $config['dsn'] ?? '';
    $user = $config['user'] ?? '';
    $password = $config['password'] ?? '';

    $this->pdo = new \PDO($dsn, $user, $password);
    // If there is some problem regarding $this->pdo, just throw an exception. Without this pdo just suppresses the error and we don't see anything.
    $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
  }


  public function applyMigrations()
  {
    // This method has to read the files from the migrations folder and apply them to a database. It also has to keep track which migrations were already applied and not re-apply them.

    $this->createMigrationsTable();
    $appliedMigrations = $this->getAppliedMigrations();
    $newMigrations = [];

    // scandir returns a list of files in a given directory
    $files = scandir(Application::$ROOT_DIR . '/migrations');

    $leftToApplyMigrations = array_diff($files, $appliedMigrations);
    foreach ($leftToApplyMigrations as $migration) {
      if ($migration === '.' || $migration === '..') {
        continue;
      }

      // $migration is a filename
      require_once Application::$ROOT_DIR . '/migrations/' . $migration;
      // convert filename to classname (drop the '.php' extension)
      $className = pathinfo($migration, PATHINFO_FILENAME);
      // create an instance of the class
      $instance = new $className();
      // apply migration by calling 'up' method
      $this->log("Applying migration $migration");
      $instance->up();
      $this->log("Applied migration $migration");
      // push the applied migration to the array
      $newMigrations[] = $migration;
    }

    // copy the newly applied migrations to the db
    if (!empty($newMigrations)) {
      $this->saveMigrations($newMigrations);
    } else {
      $this->log("All migration are applied");
    }
  }


  public function createMigrationsTable()
  {
    $this->pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
      id INT AUTO_INCREMENT PRIMARY KEY,
      migration VARCHAR(255),
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");
  }


  public function getAppliedMigrations()
  {
    $statement = $this->pdo->prepare("SELECT migration from migrations");
    $statement->execute();

    return $statement->fetchAll(\PDO::FETCH_COLUMN);
  }


  public function saveMigrations(array $migrations)
  {
    // PHP map and arrow functions: transform each entry of $migrations array to a ('entry')
    $migrations = array_map(fn ($m) => "('$m')", $migrations);
    // Concatenate array entries into a comma separated string
    $migrations = implode(",", $migrations);

    $statement = $this->pdo->prepare("INSERT INTO migrations (migration) VALUES $migrations");
    $statement->execute();
  }


  protected function log($message)
  {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
  }
}
