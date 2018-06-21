<?php
namespace NahroTo\DatabaseManager;

use NahroTo\DatabaseManager\Database;

class DatabaseManager {

    /** @var int Default PDO fetch style */
    const DEFAULT_FETCH_STYLE = \PDO::ATTR_DEFAULT_FETCH_MODE;

    /** @var Database The database this class uses. */
    private $database;

    /** @var \PDO */
    private $pdo;

    /** @var array */
    private $pdoAttributeSets = [];

    /**
     * @var int The PDO fetch style used on {@see \PDOStatement::fetchAll()}
     * which is called in {@see DatabaseManager::query()}.
     * */
    private $fetchStyle = self::DEFAULT_FETCH_STYLE;

    public function __construct(Database $database) {
        $this->database = $database;
    }

    /**
     * Starts a new database connection.
     * Must be called before {@see DatabaseManager::query()}
     */
    public function start(): void {
        $database = $this->getDatabase();

        $pdo = new \PDO(
            "mysql:host=".$database->getHost().";dbname=".$database->getName(),
            $database->getUsername(),
            $database->getPassword()
        );

        foreach ($this->pdoAttributeSets as $pdoAttributeSet) {
            $pdo->setAttribute($pdoAttributeSet[0], $pdoAttributeSet[1]);
        }

        $this->pdo = $pdo;
    }

    /**
     * Queries the database. {@see DatabaseManager::start()} must be called first.
     * @param string $sqlQuery The query in SQL.
     * @param array $bindedParameters (optional) Array of binded parameters that are marked with '?' in the query.
     * @return array The query results.
     */
    public function query(string $sqlQuery, array $bindedParameters = null): array  {
        $pdo = $this->pdo;
        if (is_null($pdo)) {
            throw new Exception("query(..) called without calling start() before.");
        }
        $pdoStatement = $pdo->prepare($sqlQuery);
        if (!is_null($bindedParameters)) {
            foreach ($bindedParameters as $index => $bindedParameter) {
                $pdoStatement->bindParam($index + 1, $bindedParameter);
            }
        }
        $pdoStatement->execute();
        return $pdoStatement->fetchAll($this->getFetchStyle());
    }

    /**
     * Starts a new database connection, queries the database then stops the database connection.
     * This should be only called if you need to query the database once in a session,
     * otherwise use {@see DatabaseManager::query()}.
     * @param string $sqlQuery The query in SQL.
     * @param array $bindedParameters (optional) Array of binded parameters that are marked with '?' in the query.
     * @return array The query results.
     */
    public function queryOnce(string $sqlQuery, array $bindedParameters = null): array  {
        $this->start();
        $results = $this->query($sqlQuery, $bindedParameters);
        $this->stop();
        return $results;
    }

    /**
     * Stops the database connection.
     * @see DatabaseManager::start()
     */
    public function stop(): void {
        $this->pdo = null;
    }

    public function setDatabase(Database $database): void {
        $this->database = $database;
    }

    /**
     * @return Database The database.
     */
    public function getDatabase(): Database {
        return $this->database;
    }

    /**
     * Sets the pdo attributes. {@see DatabaseManager::start()} needs to be called
     * afterwards in order to apply the changes.
     */
    public function setPdoAttribute(int $attribute, $value): void {
        array_push($this->pdoAttributeSets, [$attribute, $value]);
    }

    /**
     * @return array The configured PDO attributes.
     */
    public function getPdoAttributes(): array {
        return $this->pdoAttributeSets;
    }

    /**
     * The fetch style used to query the results with {@see \PDOStatement::fetchAll()}.
     */
    public function setFetchStyle(int $fetchStyle): void {
        $this->fetchStyle = $fetchStyle;
    }

    /**
     * The fetch style used to query the results with {@see \PDOStatement::fetchAll()}.
     */
    public function getFetchStyle(): int {
        return $this->fetchStyle;
    }
}